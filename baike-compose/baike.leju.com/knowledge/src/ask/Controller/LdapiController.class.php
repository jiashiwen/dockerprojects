<?php
/**
 * 乐道问答相关接口
 */
namespace ask\Controller;
use Think\Controller;
class LdapiController extends BaseController {

	protected $_method_type = '+';
	protected $lUserCenter = null;

	public function __construct() {
		parent::__construct();
		$lUserCenter = D('UserCenter', 'Logic', 'Common');
		$lUserCenter->setSource($lUserCenter::UC_SOURCE_LD);
		$this->lUserCenter = $lUserCenter;
	}

	/**
	 * 会员中心获取相关数据
	 *
	 */


	/**
	 * 批量获取用户编号对应的用户基本信息
	 */
	public function getUsersInfo() {
		$users = I('request.userids', '', 'trim,clear_all,clean_xss');
		$users = explode(',', $users);
		foreach ( $users as $i => &$user ) {
			$user = intval($user);
			if ( $user==0 ) {
				unset($users[$i]);
			}
		}
		if ( empty($users) ) {
			$this->ajax_error('请指定待查询的用户编号');
		}
		$ret = $this->lUserCenter->SearchUserinfo($users);
		if ( $ret ) {
			$ret = $this->lUserCenter->getData();
			$result = ['status'=>true, 'list'=>$ret['data']];
			$this->ajax_return($result);
		} else {
			$ret = $this->lUserCenter->getError();
			$msg = isset($ret['msg']) ? $ret['msg'] : $ret['error'];
			$this->ajax_error($msg);
		}
	}

	/**
	 * 查询页面关注数据接口
	 */
	public function status() {
		$userid = $this->checkUserid();
		$opts = I('get.');
		$ret = $this->lUserCenter->SearchStatus($userid, $opts);
		$result = $this->lUserCenter->getData();
		$result = [
			'status' => true,
			'list' => $result['data'],
		];
		$this->ajax_return($result);
	}

	/**
	 * 关注公司
	 */
	public function uCompany() {
		$userid = $this->checkUserid();
		$company_id = $this->checkId('id');
		$direct = I('get.direct', 1, 'intval');	// 操作，1为关注 0为取消

		// 验证公司是否开启
		$mCompanies = D('Companies', 'Model', 'Common');
		$company = $mCompanies->CheckCompanyStatus($company_id);
		if ( !$company ) {
			$this->ajax_error('指定的公司暂时未提供乐道服务');
		}
		$where = ['wiki_id'=>$company_id];
		// 接口调用
		if ( $direct !== 0 ) {
			// 非 0 进行关注操作
			$ret = $this->lUserCenter->AttentionWiki($userid, $company_id, $company['title']);
			if ( $this->lUserCenter->getResult() ) {
				$mCompanies->where($where)->setInc('favs', 1);
				$msg = '公司关注成功';
			} else {
				$error = $this->lUserCenter->getError();
				$msg = $error['msg'];
				$this->ajax_error('公司关注失败 '.$msg);
			}
		} else {
			// 0 时进行取消关注操作
			$ret = $this->lUserCenter->UnAttentionWiki($userid, $company_id, $company['title']);
			if ( $this->lUserCenter->getResult() ) {
				$where['i_attention'] = ['gt', 0];
				$mCompanies->where($where)->setDec('favs', 1);
				$msg = '取消公司关注成功';
			} else {
				$error = $this->lUserCenter->getError();
				$msg = $error['msg'];
				$this->ajax_error('取消公司关注失败 '.$msg);
			}
		}

		// $ret = $mCompanies->where($where)->field('favs')->find();
		// $this->ajax_return(['status'=>true, 'msg'=>$msg, 'favs'=>intval($ret['favs'])]);
		$result = ['status'=>true, 'msg'=>$msg];
		if ( APP_DEBUG ) {
			$result['dbg'] = $this->lUserCenter->getData();
		}
		$this->ajax_return($result);
	}
	/**
	 * 关注问题
	 */
	public function uQuestion() {
		$userid = $this->checkUserid();
		$direct = I('get.direct', 1, 'intval');	// 操作，1为关注 0为取消
		$question_id = $this->checkId('id');

		// 验证问题状态
		$mCompanyQuestions = D('CompanyQuestions', 'Model', 'Common');
		$question = $mCompanyQuestions->find($question_id);
		if ( !$question ) {
			$this->ajax_error('指定的问题异常，请指定有效问题编号');
		}
		$status = intval($question['status']);
		if ( $status!==2 ) {
			$this->ajax_error('指定的问题异常，请指定有效问题编号.');
		}
		// 验证公司是否开启
		$company_id = intval($question['company_id']);
		$mCompanies = D('Companies', 'Model', 'Common');
		$company = $mCompanies->CheckCompanyStatus($company_id);
		if ( !$company ) {
			$this->ajax_error('指定的公司暂时未提供乐道服务');
		}

		$where = ['id'=>$question_id];
		// 接口调用
		if ( $direct !== 0 ) {
			// 非 0 进行关注操作
			$ret = $this->lUserCenter->AttentionQuestion($userid, $question_id);
			if ( $this->lUserCenter->getResult() ) {
				$mCompanyQuestions->where($where)->setInc('i_attention', 1);
				$msg = '问题关注成功';
			} else {
				$error = $this->lUserCenter->getError();
				$msg = $error['msg'];
				$this->ajax_error('问题关注失败 '.$msg);
			}
		} else {
			// 0 时进行取消关注操作
			$ret = $this->lUserCenter->UnAttentionQuestion($userid, $question_id);
			if ( $this->lUserCenter->getResult() ) {
				$where['i_attention'] = ['gt', 0];
				$mCompanyQuestions->where($where)->setDec('i_attention', 1);
				$msg = '取消问题关注成功';
			} else {
				$error = $this->lUserCenter->getError();
				$msg = $error['msg'];
				$this->ajax_error('取消问题关注失败 '.$msg);
			}
		}
		$this->lUserCenter->Reset();
		$opts = ['qid'=>$question_id];
		$this->lUserCenter->SearchStatus($userid, $opts);
		$fav = 0;
		if ( $this->lUserCenter->getResult() ) {
			$ret = $this->lUserCenter->getData();
			if ( intval($ret['data']['q'][$question_id]['total']) > 0 ) {
				$fav = intval($ret['data']['q'][$question_id]['total']);
			}
		} else {
			$ret = $mCompanyQuestions->where($where)->field('i_attention')->find();
			$fav = intval($ret['i_attention']);
		}
		$this->ajax_return(['status'=>true, 'msg'=>$msg, 'favs'=>$fav]);
	}
	/**
	 * 关注回答
	 */
	public function uAnswer() {
		$userid = $this->checkUserid();
		$direct = I('get.direct', 1, 'intval');	// 操作，1为关注 0为取消
		$answer_id = $this->checkId('id');

		// 验证回答状态
		$mCompanyAnswers = D('CompanyAnswers', 'Model', 'Common');
		$answer = $mCompanyAnswers->find($answer_id);
		if ( !$answer ) {
			$this->ajax_error('指定的回答异常，请指定有效回答编号');
		}
		$status = intval($answer['status']);
		if ( $status!==2 ) {
			$this->ajax_error('指定的回答异常，请指定有效回答编号.');
		}
		// 验证问题状态
		$question_id = intval($answer['question_id']);
		if ( $question_id<=0 ) {
			$this->ajax_error('指定的问题异常，请指定有效问题编号');
		}
		$mCompanyQuestions = D('CompanyQuestions', 'Model', 'Common');
		$question = $mCompanyQuestions->find($question_id);
		if ( !$question ) {
			$this->ajax_error('指定的问题异常，请指定有效问题编号');
		}
		$status = intval($question['status']);
		if ( $status!==2 ) {
			$this->ajax_error('指定的问题异常，请指定有效问题编号.');
		}
		// 验证公司是否开启
		$company_id = intval($question['company_id']);
		$mCompanies = D('Companies', 'Model', 'Common');
		$company = $mCompanies->CheckCompanyStatus($company_id);
		if ( !$company ) {
			$this->ajax_error('指定的公司暂时未提供乐道服务');
		}

		$where = ['id'=>$answer_id];
		// 接口调用
		if ( $direct !== 0 ) {
			// 非 0 进行关注操作
			$ret = $this->lUserCenter->AttentionAnswer($userid, $answer_id);
			if ( $this->lUserCenter->getResult() ) {
				$mCompanyAnswers->where($where)->setInc('i_attention', 1);
				$msg = '回答关注成功';
			} else {
				$error = $this->lUserCenter->getError();
				$msg = $error['msg'];
				$this->ajax_error('回答关注失败 '.$msg);
			}
		} else {
			// 0 时进行取消关注操作
			$ret = $this->lUserCenter->UnAttentionAnswer($userid, $answer_id);
			if ( $this->lUserCenter->getResult() ) {
				$where['i_attention'] = ['gt', 0];
				$mCompanyAnswers->where($where)->setDec('i_attention', 1);
				$msg = '取消回答关注成功';
			} else {
				$error = $this->lUserCenter->getError();
				$msg = $error['msg'];
				$this->ajax_error('取消回答关注失败 '.$msg);
			}
		}
		$this->lUserCenter->Reset();
		$opts = ['aid'=>$answer_id];
		$this->lUserCenter->SearchStatus($userid, $opts);
		$fav = 0;
		if ( $this->lUserCenter->getResult() ) {
			$ret = $this->lUserCenter->getData();
			if ( intval($ret['data']['a'][$answer_id]['total']) > 0 ) {
				$fav = intval($ret['data']['a'][$answer_id]['total']);
			}
		} else {
			$ret = $mCompanyAnswers->where($where)->field('i_attention')->find();
			$fav = intval($ret['i_attention']);
		}
		$this->ajax_return(['status'=>true, 'msg'=>$msg, 'favs'=>$fav]);
	}
	/**
	 * 赞回答
	 */
	public function uGoodAnswer() {
		$userid = $this->checkUserid();
		$direct = I('get.direct', 1, 'intval');	// 操作，1为关注 0为取消
		$answer_id = $this->checkId('id');

		// 验证回答状态
		$mCompanyAnswers = D('CompanyAnswers', 'Model', 'Common');
		$answer = $mCompanyAnswers->find($answer_id);
		if ( !$answer ) {
			$this->ajax_error('指定的回答异常，请指定有效回答编号');
		}
		$status = intval($answer['status']);
		if ( $status!==2 ) {
			$this->ajax_error('指定的回答异常，请指定有效回答编号.');
		}
		// 验证问题状态
		$question_id = intval($answer['question_id']);
		if ( $question_id<=0 ) {
			$this->ajax_error('指定的问题异常，请指定有效问题编号');
		}
		$mCompanyQuestions = D('CompanyQuestions', 'Model', 'Common');
		$question = $mCompanyQuestions->find($question_id);
		if ( !$question ) {
			$this->ajax_error('指定的问题异常，请指定有效问题编号');
		}
		$status = intval($question['status']);
		if ( $status!==2 ) {
			$this->ajax_error('指定的问题异常，请指定有效问题编号.');
		}
		// 验证公司是否开启
		$company_id = intval($question['company_id']);
		$mCompanies = D('Companies', 'Model', 'Common');
		$company = $mCompanies->CheckCompanyStatus($company_id);
		if ( !$company ) {
			$this->ajax_error('指定的公司暂时未提供乐道服务');
		}

		$where = ['id'=>$answer_id];
		// 接口调用
		if ( $direct !== 0 ) {
			// 非 0 进行关注操作
			$ret = $this->lUserCenter->GoodAnswer($userid, $answer_id);
			$data = $this->lUserCenter->getData();
			if ( $this->lUserCenter->getResult() ) {
				$mCompanyAnswers->where($where)->setInc('i_good', 1);
				$msg = '问题点赞成功';
			} else {
				$error = $this->lUserCenter->getError();
				$msg = $error['msg'];
				$this->ajax_error('问题点赞失败，'.$msg);
			}
		} else {
			// 0 时进行取消关注操作
			$ret = $this->lUserCenter->UnGoodAnswer($userid, $answer_id);
			if ( $this->lUserCenter->getResult() ) {
				$where['i_good'] = ['gt', 0];
				$mCompanyAnswers->where($where)->setDec('i_good', 1);
				$msg = '取消问题点赞成功';
			} else {
				$error = $this->lUserCenter->getError();
				$msg = $error['msg'];
				$this->ajax_error('取消问题点赞失败，'.$msg);
			}
		}
		$this->lUserCenter->Reset();
		$opts = ['gaid'=>$answer_id];
		$this->lUserCenter->SearchStatus($userid, $opts);
		$fav = 0;
		if ( $this->lUserCenter->getResult() ) {
			$ret = $this->lUserCenter->getData();
			if ( intval($ret['data']['ga'][$answer_id]['total']) > 0 ) {
				$fav = intval($ret['data']['ga'][$answer_id]['total']);
			}
		} else {
			$ret = $mCompanyAnswers->where($where)->field('i_good')->find();
			$fav = intval($ret['i_good']);
		}
		$this->ajax_return(['status'=>true, 'msg'=>$msg, 'good'=>$fav]);
	}

	/**
	 * 关注问题
	 */
	public function uUser() {
		$userid = $this->checkUserid();
		$user_id = $this->checkId('id');
		$direct = I('get.direct', 1, 'intval');	// 操作，1为关注 0为取消

		// 接口调用
		if ( $direct !== 0 ) {
			// 非 0 进行关注操作
			$ret = $this->lUserCenter->AttentionUser($user_id, $userid);
			if ( $this->lUserCenter->getResult() ) {
				$msg = '用户关注成功';
			} else {
				$this->ajax_error('用户关注失败');
			}
		} else {
			// 0 时进行取消关注操作
			$ret = $this->lUserCenter->UnAttentionUser($user_id, $userid);
			if ( $this->lUserCenter->getResult() ) {
				$msg = '取消用户关注成功';
			} else {
				$this->ajax_error('取消用户关注失败');
			}
		}
		$this->ajax_return(['status'=>true, 'msg'=>$msg]);
	}


	/*
	 * 验证用户是否登录，并获取合法用户会员编号
	 */
	protected function checkUserid() {
		$userid = intval($this->_userid);
		if ( $userid <= 0 ) {
			$this->ajax_error('请先登录后进行操作');
		}
		return $userid;
	}
	/*
	 * 验证指定参数是否正确编号
	 */
	protected function checkId($field='id') {
		$id = I('get.'.$field, 0, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('请指定编号');
		}
		return $id;
	}

	/**
	 * 提问接口
	 */
	public function ask() {
		$userid = $this->checkUserid();

		$result = array(
			'status'=>true, 'info'=>'success', 'reason'=>'提问成功',
			'debug'=>['cost'=>[]],
		);

		G('checking_start');
		$form = I('post.');
		$fields = array_flip(['title', 'desc', 'images', 'company_id']);
		$form = array_intersect_key($form, $fields);

		$company_id = intval($form['company_id']);
		if ( $company_id <= 0 ) {
			$this->ajax_error('请指定当前提问公司');
		}
		$mCompanies = D('Companies', 'Model', 'Common');
		$company = $mCompanies->getCompanyInfo($company_id);
		if ( !$company ) {
			$this->ajax_error('请指定当前提问公司.');
		}

		$form['title'] = clean_xss(clear_all($form['title']));
		if ( $form['title']=='' ) {
			$this->ajax_error('问题标题是必填项');
		}
		if ( abslength($form['title']) > 200 ) {
			$this->ajax_error('标题字数不可超过 200 字');
		}
		$form['desc'] = clean_xss(clear_all($form['desc']));
		if ( abslength($form['desc']) > 5000 ) {
			$this->ajax_error('描述信息不可超过超过 5000 字');
		}
		G('checking_end');
		$result['debug']['cost']['checking'] = array(
			'time' => G('checking_start', 'checking_end', 3),
			'mem' => G('checking_start', 'checking_end', 'm'),
		);

		G('handledata_start');
		if ( $this->_device=='pc' ) {
			$source = 0;
		} else {
			$source = 1;
		}
		if ( isset($form['images']) ) {
			$images = explode(',', $form['images']);
			foreach ( $images as $i => $image ) {
				if ( trim($image)=='' ) {
					unset($images[$i]);
				}
			}
			unset($form['images']);
		} else {
			$images = [];
		}
		// 扩展字段处理
		$form['extra'] = [];
		$form['extra']['usernick'] = $this->_userinfo['username'];
		$form['extra']['avatar'] = $this->_userinfo['headurl'];
		// 多张图片上传时，每张图片使用","进行分隔
		$form['extra']['images'] = $images;
		$_client = getCookieLocation($this->_device);
		$form['extra']['client'] = $_client; // 已包含 ip 城市中、英文名称

		$form['city'] = $company['city'];
		$form['company_id'] = $company['id'];
		$form['ctime'] = NOW_TIME;
		$form['utime'] = NOW_TIME;
		$form['userid'] = $userid;
		$form['source'] = $source;
		$form['status'] = 2; // 默认正常发布
		$form['i_images'] = count($images);

		# 敏感词过滤
		// 如果为待审核信息，存储敏感词审核信息 后台使用
		if ( C('NEED_VERIFY')==true ) {
			G('sensitive_start');
			$lSensitive = D('Sensitive', 'Logic', 'Common');
			$content = $form['title'] . PHP_EOL . $form['desc'];
			$ret = $lSensitive->detect($content, 0);
			if ( $ret && $ret['status'] ) {
				$form['extra']['data']['sensitive'] = $ret;
				if ( in_array($ret['type'], array('政治','色情','营销')) ) {
					$form['status'] = 1; // 需要审核
				}
			}
			G('sensitive_end');
			$result['debug']['cost']['sensitive'] = array(
				'time' => G('sensitive_start', 'sensitive_end', 3),
				'mem' => G('sensitive_start', 'sensitive_end', 'm'),
			);
		}

		$form['extra'] = json_encode($form['extra']);
		G('handledata_end');
		$result['debug']['cost']['handledata'] = array(
			'time' => G('handledata_start', 'handledata_end', 3),
			'mem' => G('handledata_start', 'handledata_end', 'm'),
		);

		// 添加至数据库
		$mCompanyQuestions = D('CompanyQuestions', 'Model', 'Common');
		$id = $mCompanyQuestions->data($form)->add();
		if ( $id ) {
			$form['id'] = $id;
			$mCompanies->updateRelQuestions($company_id, $form['status']);

			if ( $form['status'] == 2 ) {
				// 发布成功
				// 会员接口接口调用 -> 创建一个问题的关联
				$ret = $this->lUserCenter->DoQuestion($userid, $id);

				// 问题数据推送到新闻池
				$lInfos = D('Infos','Logic','Common');
				$info = $mCompanyQuestions->find($id);
				$lInfos->pushNewsPool($info, $lInfos::TYPE_LDQ);

				$result['question_id'] = intval($id);
				$this->ajax_return($result);
			}
			if ( $form['status']==1 ) {
				$msg = '您提的问题中有敏感信息，问题已经提交，请等待客服人员审核';
				$this->ajax_error($msg);
			}
		} else {
			$this->ajax_error('提问失败');
		}
	}

	/**
	 * 对问题进行回复
	 * @@action - ajax
	 */
	public function reply() {
		$userid = $this->checkUserid();

		$result = array(
			'status'=>true, 'info'=>'success', 'reason'=>'回答成功',
			'answer_id'=>false, 'debug'=>['cost'=>[]],
		);

		G('checking_start');
		$_device = $this->_device == 'pc' ? 'pc' : 'touch';

		$form = I('post.');
		$fields = array('question_id'=>'', 'reply'=>'', 'images'=>'');
		$form = array_intersect_key($form, $fields);
		$form['reply'] = clean_xss(clear_all($form['reply']));
		$question_id = intval($form['question_id']);
		if ( $question_id == 0 ) {
			$this->ajax_error('请指定要回答的问题编号 question_id');
		}
		$mCompanyQuestions = D('CompanyQuestions', 'Model', 'Common');
		$question = $mCompanyQuestions->find($question_id);
		if ( !$question ) {
			$this->ajax_error('您回复的问题不存在');
		}
		$question['status'] = intval($question['status']);
		if ( $question['status']!==2 ) {
			$this->ajax_error('您回复的问题不存在.');
		}

		G('checking_end');
		$result['debug']['cost']['checking'] = array(
			'time' => G('checking_start', 'checking_end', 3),
			'mem' => G('checking_start', 'checking_end', 'm'),
		);

		G('handledata_start');
		if ( isset($form['images']) ) {
			$images = explode(',', $form['images']);
			foreach ( $images as $i => $image ) {
				if ( trim($image)=='' ) {
					unset($images[$i]);
				}
			}
			unset($form['images']);
		} else {
			$images = [];
		}
		// 扩展字段处理
		$form['extra'] = [];
		$form['extra']['usernick'] = $this->_userinfo['username'];
		$form['extra']['avatar'] = $this->_userinfo['headurl'];
		$form['extra']['images'] = $images;
		$_client = getCookieLocation($this->_device);
		$form['extra']['client'] = $_client; // 已包含 ip 城市中、英文名称
		$form['city'] = $question['city'];
		$form['company_id'] = $question['company_id'];
		$form['question_id'] = $question['id'];
		$form['userid'] = $userid;
		$form['ctime'] = NOW_TIME;
		$form['utime'] = NOW_TIME;
		$form['reply'] = filterInput(clean_xss(clear_all($form['reply'])));
		if ( $this->_device=='pc' ) {
			$source = 0;
		} else {
			$source = 1;
		}
		$form['source'] = $source;
		$form['status'] = 2; // 默认直接发布

		if ( C('NEED_VERIFY')==true ) {
			// 敏感词过滤
			G('sensitive_start');
			// 如果为待审核信息，存储敏感词审核信息 后台使用
			$lSensitive = D('Sensitive', 'Logic', 'Common');
			$content = $form['reply'];
			$ret = $lSensitive->detect($content, 0);
			$result['debug']['lSensitive'] = $ret;
			if ( $ret && $ret['status'] ) {
				$form['extra']['confirm'] = $ret;
				if ( in_array($ret['type'], array('政治','色情','推销')) ) {
					$form['status'] = 1; // 需要审核
				} else {
					$form['status'] = 2; // 直接发布
				}
			}
			G('sensitive_end');
			$result['debug']['cost']['sensitive'] = array(
				'time' => G('sensitive_start', 'sensitive_end', 3),
				'mem' => G('sensitive_start', 'sensitive_end', 'm'),
			);
		}

		// 将扩展数据字段进行编码存储
		$form['extra'] = json_encode($form['extra']);
		G('handledata_end');
		$result['debug']['cost']['handledata'] = array(
			'time' => G('handledata_start', 'handledata_end', 3),
			'mem' => G('handledata_start', 'handledata_end', 'm'),
		);

		// 插入回答数据
		$mCompanyAnswers = D('CompanyAnswers', 'Model', 'Common');
		$id = $mCompanyAnswers->data($form)->add();
		if ( $id ) {
			G('business_start');
			$result['answer_id'] = intval($id);
			$result['question_id'] = $question_id;

			$mCompanies = D('Companies', 'Model', 'Common');
			$company_id = intval($question['company_id']);
			$mCompanies->updateRelQuestions($company_id, $form['status']);
			// 状态如果为2，表示发布回复成功 回复状态正常的情况下完成回复操作
			if ( $form['status']==2 ) {
				// 会员接口接口调用 -> 创建一个问题的关联
				$ret = $this->lUserCenter->DoAnswer($userid, $question_id, $id);

				// TODO: 回答成功后，回复数 +1
				// 更新问答存在的有效回复数量，所有回复数量，更新问题最后更新时间
				$ret = $mCompanyQuestions->updateQuestionNewAnswer($question['id']);
				$result['debug']['updateQuestionNewAnswer'] = $ret;

				// 问题数据推送到新闻池
				$lInfos = D('Infos','Logic','Common');
				$info = $mCompanyAnswers->find($id);
				$lInfos->pushNewsPool($info, $lInfos::TYPE_LDA);
			}
			// 回复状态异常的情况下，完成回复操作，需要管理员后台审核回复内容
			if ( $form['status']==1 ) {
				$result['status'] = false;
				$result['info'] = 'hidden';
				$result['reason'] = '您发布的回答含有 '.implode(', ', $form['extra']['sensitive']['words']).
					'等敏感词汇，疑似 '.$form['extra']['sensitive']['type'].' 类型的回复。已经发往后台管理员处进行审核。';
			}
			// 自动修正问题数据
			// $result['debug']['fixQuestionData'] = $lQuestion->fixQuestionData($qid);
			G('business_end');
			$result['debug']['cost']['business'] = array(
				'time' => G('business_start', 'business_end', 3),
				'mem' => G('business_start', 'business_end', 'm'),
			);
			$this->ajax_return($result);
		} else {
			$this->ajax_error('提交错误，请重试');
		}
	}


	/**
	 * 设置操作类型
	 */
	public function setMethod($direct='+') {
		$direct = $direct === '-' ? '-' : '+';
		$this->_method_type = $direct;
		return $this;
	}

}
