<?php
/**
 * 问答控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class QuestionController extends BaseController {

	protected $auto_update_question_status = true; // 是否自动更新回答相关的问题状态

	// 默认列表页，每页问题数量为 20 条
	protected $pagesize = 20;
	// 当前登录用户的权限信息
	protected $roleinfo = null;

	protected $redis = null;

	protected $binds = [];

	public function __construct() {
		parent::__construct();

		$pageinfo = array('title'=>'问答管理');
		$this->assign('pageinfo', $pageinfo);
	}

	/**
	 * 用户所在的角色的操作权限验证
	 */
	protected function CheckRole ( $rolekey ) {
		// 基本操作权限判断
		$this->checkAuthorization($rolekey);
		// 指定属性验证
		$lR = D('Role', 'Logic', 'admin');
		$role = $lR->getRoleList();
		$roleCate = $this->roleCate($role, $rolekey);

		if ($roleCate === false) {
			$this->error('栏目权限不够');
		}	
		return array('roleCate'=>$roleCate);
	}
	/**
	 * 查看指定权限操作 $rolekey 是否在 $role 中被允许操作
	 */
	protected function roleCate($role,$rolekey) {
		$authorities = json_decode($role[$this->_user['role_id']]['authorities'], true);
		$roleCate = false;
		if ( $authorities ) {
			if ( $authorities[$rolekey] ) {
				$roleCate = $authorities[$rolekey];
			}
		}
		return !empty($roleCate) ? $roleCate : false;
	}

	/**
	 * 问答管理首页
	 * @@action - page
	 */
	public function index(){
		$rolekey = 'question/list';
		$this->checkAuthorization($rolekey);

		$lQuestion = D('Question', 'Logic', 'Common');
		$counts = $lQuestion->getCounters();
		foreach ( $counts as $i => &$count ) {
			if ( $count > 10000 ) {
				$count = number_format( $count / 10000, 2 ) . '万';
			}
		}
		$this->assign('counts', $counts);
		$this->display('index');
	}

	// ---- 乐道问答相关入口 逻辑 ----
		protected function ldcommon() {
			// 基础字典
			$this->binds['dict'] = C('DICT.LD');
			// 城市和企业字典加载
			$_cities = C('CITIES.ALL');
			$cities = [];
			$all = [];
			foreach ( $_cities as $city_en => $city ) {
				$city_cn = trim($city['cn']);
				if ( !array_key_exists($city_cn, $all) ) {
					$all[$city_cn] = $city_en;
				}
			}
			$fields = ['id, city, title, stname'];
			$where = ['cateid'=>1, 'status'=>9];
			$lds = D('Wiki', 'Model', 'Common')->field($fields)->where($where)->select();
			if ( $lds ) {
				foreach ( $lds as $i => $ld ) {
					$city_cn = trim($ld['city']);
					$ldname = trim($ld['stname']);
					if ( $ldname=='' ) {
						$ldname = '['.trim($ld['title']).']';
					}
					if ( array_key_exists($city_cn, $all) ) {
						if ( !array_key_exists($city_cn, $cities) ) {
							$cities[$city_cn] = [
								'id'=>$city_cn,
								'name'=>$city_cn,
								'en'=>$cities[$city_cn],
								'cn'=>$city_cn,
								'ld'=>[['id'=>$ld['id'], 'name'=>$ldname]]
							];
						} else {
							array_push($cities[$city_cn]['ld'], ['id'=>$ld['id'], 'name'=>$ldname]);
						}
					}
				}
			}
			$this->binds['cities'] = &$cities;
			return true;
		}
		// 城市、公司二级联动选择
		public function ldselectchain() {
			$city = I('get.city', '', 'trim');
			if ( $city=='' ) {
				$this->ajax_return(['status'=>true, 'list'=>[]]);
			}
			$this->ldcommon();
			$list = &$this->binds;
			if ( !array_key_exists($city, $list['cities']) ) {
				$this->ajax_error('请指定有效的城市名称');
			}
			if ( isset($list['cities'][$city]['ld']) && $list['cities'][$city]['ld'] ) {
				$ret = $list['cities'][$city]['ld'];
			} else {
				$ret = [];
			}
			$this->ajax_return(['status'=>true, 'list'=>$ret]);
		}
		// 公司列表入口
		public function company() {
			$page = I('get.page', 1, 'intval');
			$pagesize = 20;
			$city = I('get.city', '', 'trim');
			$name = I('get.name', '', 'trim');
			$status = I('get.status', '', 'trim');

			$mCompanies = D('Companies', 'Model', 'Common');
			$binds = $mCompanies->getCompanies($page, $pagesize, $city, $name, $status);


			// 处理分页条
			$linkopts = [];
			if ( $city!=='' ) { $linkopts['city']=$city; }
			if ( $name!=='' ) { $linkopts['name']=$name; }
			if ( $status!=='' ) { $linkopts['status']=$status; }
			$linkstring = http_build_query($linkopts);
			$linkstring = '/Question/company' . ( !empty($linkopts) ? '?page=#&'.$linkstring : '?page=#' );
			$opts = array(
					'first' => true, //首页
					'last' => true,	//尾页
					'prev' => true, //上一页
					'next' => true, //下一页
					'number' => 5, //显示页码数
					'linkstring' => $linkstring
			);
			$binds['pager'] = pager($page, $binds['total'], $pagesize, $opts);
			$binds['form'] = $linkopts;

			$this->ldcommon();
			$binds = array_merge($binds, $this->binds);
			// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
			foreach ( $binds as $_name => $_value ) {
				$this->assign($_name, $_value);
			}
			$this->display('company');
		}
		// 公司开启或关闭
		public function ldonoff() {
			$id = I('get.id', 0, 'intval');
			if ( $id<=0 ) {
				$this->ajax_error('请指定操作公司编号');
			}
			$type = I('get.type', '', 'trim');	// 0关闭 1开启
			$types = ['0'=>'关闭', '1'=>'开启'];
			if ( !array_key_exists($type, $types) ) {
				$this->ajax_error('请指定操作状态');
			}

			$mWiki = D('Wiki', 'Model', 'Common');
			$where = ['id'=>$id, 'cateid'=>1, ];
			$wiki = $mWiki->where($where)->find();
			if ( !$wiki ) {
				$this->ajax_error('待操作的公司词条不存在');
			}
			if ( intval($wiki['status'])!=9 ) {
				$this->ajax_error('待操作的公司词条不存在或未发布.');
			}
			// if ( trim($wiki['city'])=='' ) {
			// 	$this->ajax_error('待操作的公司词条未设置所属城市');
			// }
			$mCompanies = D('Companies', 'Model', 'Common');
			$info = $mCompanies->where(['wiki_id'=>$id])->find();
			if ( $type == 0 ) {
				if ( !$info ) {
					$this->ajax_error('没有指定的公司信息');
				}
				$ret = $mCompanies->where(['wiki_id'=>$id])->delete();
			} else {
				if ( $info ) {
					$this->ajax_error('指定的公司已经开启乐道问答');
				}
				$mWiki = D('Wiki', 'Model', 'Common');
				$wiki = $mWiki->where(['id'=>$id, 'cateid'=>1, 'status'=>9])->find();
				if ( !$wiki ) {
					$this->ajax_error('指定的公司状态错误，不能开启');
				}

				$data = ['wiki_id'=>$id, 'ctime'=>NOW_TIME];
				$ret = $mCompanies->data($data)->add();
				$mCompanies->updateRelQuestions($id, 2);
			}
			if ( $ret ) {
				$this->ajax_return(['status'=>true, 'info'=>'企业'.$types[$type].'操作成功', 'data'=>$ret]);
			} else {
				$this->ajax_error('企业'.$types[$type].'操作异常');
			}
		}
		// 公司问题列表入口
		public function ldq() {
			$page = I('get.page', 1, 'intval');
			$pagesize = 20;
			$city = I('get.city', '', 'trim,clear_all,clean_xss');
			$company_id = I('get.name', '', 'trim');	// 对应 company_id 字段
			$status = I('get.status', '', 'trim');
			$essence = I('get.essence', '', 'trim');
			$ontop = I('get.ontop', '', 'trim');
			$keyword = I('get.keyword', '', 'trim,clear_all,clean_xss');

			$opts = [];
			if ( $company_id!=='' && is_numeric($company_id) ) { $opts['company_id'] = $company_id; }
			if ( $status!=='' ) { $opts['status'] = $status; }
			if ( $city!=='' ) { $opts['city'] = $city; }
			if ( $essence!=='' ) { $opts['essence'] = $essence; }
			if ( $ontop!=='' ) { $opts['ontop'] = $ontop; }
			if ( $keyword!=='' ) { $opts['keyword'] = $keyword; }

			$mCompanyQuestions = D('CompanyQuestions', 'Model', 'Common');
			$binds = $mCompanyQuestions->getAdminList($page, $pagesize, $opts);
			// 处理分页条
			$linkopts = [];
			if ( $city!=='' ) { $linkopts['city']=$city; }
			if ( $company_id!=='' ) { $linkopts['name']=$company_id; }
			if ( $status!=='' ) { $linkopts['status']=$status; }
			if ( $essence!=='' ) { $linkopts['essence']=$essence; }
			if ( $ontop!=='' ) { $linkopts['ontop']=$ontop; }
			if ( $keyword!=='' ) { $linkopts['keyword']=$keyword; }
			$linkstring = http_build_query($linkopts);
			$linkstring = '/Question/ldq' . ( !empty($linkopts) ? '?page=#&'.$linkstring : '?page=#' );
			$opts = array(
					'first' => true, //首页
					'last' => true,	//尾页
					'prev' => true, //上一页
					'next' => true, //下一页
					'number' => 5, //显示页码数
					'linkstring' => $linkstring
			);
			$binds['pager'] = pager($page, $binds['total'], $pagesize, $opts);
			$binds['form'] = $linkopts;

			$this->ldcommon();
			$binds = array_merge($binds, $this->binds);
			// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
			foreach ( $binds as $_name => $_value ) {
				$this->assign($_name, $_value);
			}
			$this->display('ldq');
		}
		// 公司回答列表入口
		public function lda() {
			$page = I('get.page', 1, 'intval');
			$pagesize = 20;
			$city = I('get.city', '', 'trim');
			$company_id = I('get.name', '', 'trim');
			$status = I('get.status', '', 'trim');
			$keyword = I('get.keyword', '', 'trim,clear_all,clean_xss');

			// 处理分页条
			$mCompanyAnswers = D('CompanyAnswers', 'Model', 'Common');
			$binds = [];

			$opts = [];
			if ( $company_id!=='' && is_numeric($company_id) ) { $opts['company_id'] = $company_id; }
			if ( $status!=='' ) { $opts['status'] = $status; }
			if ( $city!=='' ) { $opts['scope'] = $city; }
			if ( $keyword!=='' ) { $opts['keyword'] = $keyword; }
			$binds = $mCompanyAnswers->getAdminList($page, $pagesize, $opts);
			$list = &$binds['list'];
			$comments = [];
			if ( !empty($list) ) {
				$ids = [];
				foreach ( $list as $i => $item ) {
					$id = intval($item['id']);
					if ( $id > 0 ) {
						array_push($ids, $id);
					}
				}
				$lComments = D('Comments', 'Logic', 'Common');
				$comments = $lComments->getCommentCount($ids);
			}
			foreach ( $list as $i => &$item ) {
				$id = intval($item['id']);
				$item['i_comments'] = array_key_exists($id, $comments) ? intval($comments[$id]) : 0;
			}
			

			$mCompanyQuestions = D('CompanyQuestions', 'Model', 'Common');

			$linkopts = [];
			if ( $city!=='' ) { $linkopts['city']=$city; }
			if ( $company_id!=='' ) { $linkopts['name']=$company_id; }
			if ( $status!=='' ) { $linkopts['status']=$status; }
			if ( $keyword!=='' ) { $linkopts['keyword']=$keyword; }
			$linkstring = http_build_query($linkopts);
			$linkstring = '/Question/lda' . ( !empty($linkopts) ? '?page=#&'.$linkstring : '?page=#' );
			$opts = array(
					'first' => true, //首页
					'last' => true,	//尾页
					'prev' => true, //上一页
					'next' => true, //下一页
					'number' => 5, //显示页码数
					'linkstring' => $linkstring
			);
			$binds['pager'] = pager($page, $binds['total'], $pagesize, $opts);
			$binds['form'] = $linkopts;

			$this->ldcommon();
			$binds = array_merge($binds, $this->binds);
			foreach ( $binds as $_name => $_value ) {
				$this->assign($_name, $_value);
			}
			$this->display('lda');
		}

		// 问题操作接口
		public function ldqact() {
			$act = I('get.act', '', 'trim,strtolower');
			$acts = ['del', 'undel', 'ontop', 'untop', 'oness', 'uness'];
			if ( !in_array($act, $acts) ) $this->ajax_error('操作失败，请指定正确的操作类型');
			$qid = I('get.id', 0, 'intval');
			if ( $qid<=0 ) $this->ajax_error('请提交要操作的问题编号');
			$model = D('CompanyQuestions', 'Model', 'Common');
			$info = $model->find($qid);
			if ( !$info ) $this->ajax_error('您要操作的问题不存在');
			$extra = json_decode($info['extra'], true);
			if ( $act == 'del' ) {
				$extra['last_status'] = intval($info['status']);
				$extra = json_encode($extra);
				$data = ['status'=>0,'extra'=>$extra,'utime'=>NOW_TIME,];
			}
			if ( $act == 'undel' ) {
				if ( intval($info['status'])!=0 ) $this->ajax_error('问题不是已删除状态，不能做恢复操作');
				if ( isset($extra['last_status']) && intval($extra['last_status'])==1 ) {
					$status = 1;
				} else {
					$status = 2;
				}
				$data = ['status'=>$status,'utime'=>NOW_TIME,];
			}
			if ( $act == 'ontop' ) {
				$data = ['ontop'=>NOW_TIME,'utime'=>NOW_TIME,];
			}
			if ( $act == 'untop' ) {
				$data = ['ontop'=>0,'utime'=>NOW_TIME,];
			}
			if ( $act == 'oness' ) {
				$data = ['essence'=>NOW_TIME,'utime'=>NOW_TIME,];
			}
			if ( $act == 'uness' ) {
				$data = ['essence'=>0,'utime'=>NOW_TIME,];
			}
			$ret = $model->where(['id'=>$qid])->data($data)->save();
			// var_dump($ret, $model->getLastSql());exit;
			if ( $ret ) {
				$company_id = intval($info['company_id']);
				if ( $company_id > 0 ) {
					D('Companies', 'Model', 'Common')->updateRelQuestions($company_id);
					$lInfos = D('Infos','Logic','Common');
					if ( $info['status']==2 ) {
						// 重新向新闻池推送数据
						$info = array_merge($info, $data);
						$lInfos->pushNewsPool($info, $lInfos::TYPE_LDQ);
					} else {
						// 从新闻池删除问题
						$lInfos->pushNewsPool($info, $lInfos::TYPE_LDQ, true);
					}
				}
				$this->ajax_return(['status'=>true, 'reason'=>'操作成功']);
			} else {
				$msg = '操作失败';
				$this->ajax_error($msg);
			}
		}

		// 回答操作接口
		public function ldaact() {
			$act = I('get.act', '', 'trim,strtolower');
			$acts = ['del', 'undel'];
			if ( !in_array($act, $acts) ) $this->ajax_error('操作失败，请指定正确的操作类型');
			$aid = I('get.id', 0, 'intval');
			if ( $aid<=0 ) $this->ajax_error('请提交要操作的回答编号');
			$model = D('CompanyAnswers', 'Model', 'Common');
			$info = $model->find($aid);
			if ( !$info ) $this->ajax_error('您要操作的回答不存在');
			$mQuestion = D('CompanyQuestions', 'Model', 'Common');
			$question = $mQuestion->find($info['question_id']);
			if ( !$question ) $this->ajax_error('您要操作的问题不存在');
			$extra = json_decode($info['extra'], true);
			if ( $act == 'del' ) {
				$extra['last_status'] = intval($info['status']);
				$extra = json_encode($extra);
				$data = ['status'=>0,'extra'=>$extra,'utime'=>NOW_TIME,];
				if ( intval($info['status'])==2 ) {
					$mQuestion->where(['id'=>$info['question_id']])->setDec('i_replies', 1);
				}
			}
			if ( $act == 'undel' ) {
				if ( intval($info['status'])!=0 ) $this->ajax_error('问题不是已删除状态，不能做恢复操作');
				if ( isset($extra['last_status']) && intval($extra['last_status'])==1 ) {
					$status = 1;
				} else {
					$status = 2;
					$mQuestion->where(['id'=>$info['question_id']])->setInc('i_replies', 1);
				}
				$data = ['status'=>$status,'utime'=>NOW_TIME,];
			}
			$ret = $model->where(['id'=>$aid])->data($data)->save();
			if ( $ret ) {
				$company_id = intval($info['company_id']);
				if ( $company_id > 0 ) {
					D('Companies', 'Model', 'Common')->updateRelQuestions($company_id);
					$lInfos = D('Infos','Logic','Common');
					if ( $info['status']==2 ) {
						// 重新向新闻池推送数据
						$info = array_merge($info, $data);
						$lInfos->pushNewsPool($info, $lInfos::TYPE_LDA);
					} else {
						// 从新闻池删除回答
						$lInfos->pushNewsPool($info, $lInfos::TYPE_LDA, true);
					}
				}
				$this->ajax_return(['status'=>true, 'reason'=>'操作成功']);
			} else {
				$msg = '操作失败';
				$this->ajax_error($msg);
			}
		}

	// ---- 人物问答相关入口 逻辑 ----
		protected function pncommon() {
			// 基础字典
			$this->binds['dict'] = C('DICT.PN');

			$fields = ['id, title'];
			$where = ['cateid'=>2, 'status'=>9];
			$pns = D('Wiki', 'Model', 'Common')->field($fields)->where($where)->select();
			$persons = [];
			if ( $pns ) {
				foreach ( $pns as $i => $pn ) {
					$pnname = trim($pn['title']);
					array_push($persons, [
						'id' => intval($pn['id']), 
						'name' => $pnname,
					]);
				}
			}
			$this->binds['persons'] = $persons;
			return true;
		}
		// 公司列表入口
		public function person() {
			$page = I('get.page', 1, 'intval');
			$pagesize = 20;
			$name = I('get.name', '', 'trim');
			$status = I('get.status', '', 'trim');

			$mPersons = D('Persons', 'Model', 'Common');
			$binds = $mPersons->getPersons($page, $pagesize, $name, $status);

			// 处理分页条
			$linkopts = [];
			if ( $name!=='' ) { $linkopts['name']=$name; }
			if ( $status!=='' ) { $linkopts['status']=$status; }
			$linkstring = http_build_query($linkopts);
			$linkstring = '/Question/person' . ( !empty($linkopts) ? '?page=#&'.$linkstring : '?page=#' );
			$opts = array(
					'first' => true, //首页
					'last' => true,	//尾页
					'prev' => true, //上一页
					'next' => true, //下一页
					'number' => 5, //显示页码数
					'linkstring' => $linkstring
			);
			$binds['pager'] = pager($page, $binds['total'], $pagesize, $opts);
			$binds['form'] = $linkopts;

			$this->pncommon();
			$binds = array_merge($binds, $this->binds);
			if ( defined('APP_DEPLOY') && APP_DEPLOY=='dev' ) {
				echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
			}
			foreach ( $binds as $_name => $_value ) {
				$this->assign($_name, $_value);
			}
			$this->display('person');
		}
		// 公司开启或关闭
		public function pnonoff() {
			$id = I('get.id', 0, 'intval');
			if ( $id<=0 ) {
				$this->ajax_error('请指定操作人物编号');
			}
			$type = I('get.type', '', 'trim');	// 0关闭 1开启
			$types = ['0'=>'关闭', '1'=>'开启'];
			if ( !array_key_exists($type, $types) ) {
				$this->ajax_error('请指定操作状态');
			}

			$mWiki = D('Wiki', 'Model', 'Common');
			$where = ['id'=>$id, 'cateid'=>2, ];
			$wiki = $mWiki->where($where)->find();
			if ( !$wiki ) {
				$this->ajax_error('待操作的人物词条不存在');
			}
			if ( intval($wiki['status'])!=9 ) {
				$this->ajax_error('待操作的人物词条不存在或未发布.');
			}
			$mPersons = D('Persons', 'Model', 'Common');
			$info = $mPersons->where(['wiki_id'=>$id])->find();
			if ( $type == 0 ) {
				if ( !$info ) {
					$this->ajax_error('没有指定的人物信息');
				}
				$ret = $mPersons->where(['wiki_id'=>$id])->delete();
			} else {
				if ( $info ) {
					$this->ajax_error('指定的人物已经开启乐道问答');
				}
				$mWiki = D('Wiki', 'Model', 'Common');
				$wiki = $mWiki->where(['id'=>$id, 'cateid'=>2, 'status'=>9])->find();
				if ( !$wiki ) {
					$this->ajax_error('指定的人物状态错误，不能开启');
				}

				$data = ['wiki_id'=>$id, 'ctime'=>NOW_TIME];
				$ret = $mPersons->data($data)->add();
				$mPersons->updateRelQuestions($id, 2);
			}
			if ( $ret ) {
				$this->ajax_return(['status'=>true, 'info'=>'人物'.$types[$type].'操作成功', 'data'=>$ret]);
			} else {
				$this->ajax_error('人物'.$types[$type].'操作异常');
			}
		}
		// 人物问题列表入口
		public function pnq() {
			$page = I('get.page', 1, 'intval');
			$pagesize = 20;
			$person_id = I('get.name', '', 'trim');	// 对应 person_id 字段
			$status = I('get.status', '', 'trim');
			$essence = I('get.essence', '', 'trim');
			$ontop = I('get.ontop', '', 'trim');
			$keyword = I('get.keyword', '', 'trim,clear_all,clean_xss');

			$opts = [];
			if ( $person_id!=='' && is_numeric($person_id) ) { $opts['person_id'] = $person_id; }
			if ( $status!=='' ) { $opts['status'] = $status; }
			if ( $essence!=='' ) { $opts['essence'] = $essence; }
			if ( $ontop!=='' ) { $opts['ontop'] = $ontop; }
			if ( $keyword!=='' ) { $opts['keyword'] = $keyword; }

			$mPersonQuestions = D('PersonQuestions', 'Model', 'Common');
			$binds = $mPersonQuestions->getAdminList($page, $pagesize, $opts);
			// 处理分页条
			$linkopts = [];
			if ( $person_id!=='' ) { $linkopts['name']=$person_id; }
			if ( $status!=='' ) { $linkopts['status']=$status; }
			if ( $essence!=='' ) { $linkopts['essence']=$essence; }
			if ( $ontop!=='' ) { $linkopts['ontop']=$ontop; }
			if ( $keyword!=='' ) { $linkopts['keyword']=$keyword; }
			$linkstring = http_build_query($linkopts);
			$linkstring = '/Question/pnq' . ( !empty($linkopts) ? '?page=#&'.$linkstring : '?page=#' );
			$opts = array(
					'first' => true, //首页
					'last' => true,	//尾页
					'prev' => true, //上一页
					'next' => true, //下一页
					'number' => 5, //显示页码数
					'linkstring' => $linkstring
			);
			$binds['pager'] = pager($page, $binds['total'], $pagesize, $opts);
			$binds['form'] = $linkopts;

			$this->pncommon();
			$binds = array_merge($binds, $this->binds);
			// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
			foreach ( $binds as $_name => $_value ) {
				$this->assign($_name, $_value);
			}
			$this->display('pnq');
		}
		// 人物回答列表入口
		public function pna() {
			$page = I('get.page', 1, 'intval');
			$pagesize = 20;
			$person_id = I('get.name', '', 'trim');
			$status = I('get.status', '', 'trim');
			$keyword = I('get.keyword', '', 'trim,clear_all,clean_xss');

			// 处理分页条
			$mPersonAnswers = D('PersonAnswers', 'Model', 'Common');
			$binds = [];

			$opts = [];
			if ( $person_id!=='' && is_numeric($person_id) ) { $opts['person_id'] = $person_id; }
			if ( $status!=='' ) { $opts['status'] = $status; }
			if ( $city!=='' ) { $opts['scope'] = $city; }
			if ( $keyword!=='' ) { $opts['keyword'] = $keyword; }
			$binds = $mPersonAnswers->getAdminList($page, $pagesize, $opts);
			$binds['_debug'] = $mPersonAnswers->getLastSql();
			$list = &$binds['list'];
			$comments = [];
			if ( !empty($list) ) {
				$ids = [];
				foreach ( $list as $i => $item ) {
					$id = intval($item['id']);
					if ( $id > 0 ) {
						array_push($ids, $id);
					}
				}
				$lComments = D('Comments', 'Logic', 'Common');
				$comments = $lComments->getCommentCount($ids);
			}
			foreach ( $list as $i => &$item ) {
				$id = intval($item['id']);
				$item['i_comments'] = array_key_exists($id, $comments) ? intval($comments[$id]) : 0;
			}
			
			// $mPersonQuestions = D('PersonQuestions', 'Model', 'Common');

			$linkopts = [];
			if ( $person_id!=='' ) { $linkopts['name']=$person_id; }
			if ( $status!=='' ) { $linkopts['status']=$status; }
			if ( $keyword!=='' ) { $linkopts['keyword']=$keyword; }
			$linkstring = http_build_query($linkopts);
			$linkstring = '/Question/pna' . ( !empty($linkopts) ? '?page=#&'.$linkstring : '?page=#' );
			$opts = array(
					'first' => true, //首页
					'last' => true,	//尾页
					'prev' => true, //上一页
					'next' => true, //下一页
					'number' => 5, //显示页码数
					'linkstring' => $linkstring
			);
			$binds['pager'] = pager($page, $binds['total'], $pagesize, $opts);
			$binds['form'] = $linkopts;

			$this->pncommon();
			$binds = array_merge($binds, $this->binds);
			foreach ( $binds as $_name => $_value ) {
				$this->assign($_name, $_value);
			}
			// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
			$this->display('pna');
		}

		// 问题操作接口
		public function pnqact() {
			$act = I('get.act', '', 'trim,strtolower');
			$acts = ['del', 'undel', 'ontop', 'untop', 'oness', 'uness'];
			if ( !in_array($act, $acts) ) $this->ajax_error('操作失败，请指定正确的操作类型');
			$qid = I('get.id', 0, 'intval');
			if ( $qid<=0 ) $this->ajax_error('请提交要操作的问题编号');
			$model = D('PersonQuestions', 'Model', 'Common');
			$info = $model->find($qid);
			if ( !$info ) $this->ajax_error('您要操作的问题不存在');
			$extra = json_decode($info['extra'], true);
			if ( $act == 'del' ) {
				$extra['last_status'] = intval($info['status']);
				$extra = json_encode($extra);
				$data = ['status'=>0,'extra'=>$extra,'utime'=>NOW_TIME,];
			}
			if ( $act == 'undel' ) {
				if ( intval($info['status'])!=0 ) $this->ajax_error('问题不是已删除状态，不能做恢复操作');
				if ( isset($extra['last_status']) && intval($extra['last_status'])==1 ) {
					$status = 1;
				} else {
					$status = 2;
				}
				$data = ['status'=>$status,'utime'=>NOW_TIME,];
			}
			if ( $act == 'ontop' ) {
				$data = ['ontop'=>NOW_TIME,'utime'=>NOW_TIME,];
			}
			if ( $act == 'untop' ) {
				$data = ['ontop'=>0,'utime'=>NOW_TIME,];
			}
			if ( $act == 'oness' ) {
				$data = ['essence'=>NOW_TIME,'utime'=>NOW_TIME,];
			}
			if ( $act == 'uness' ) {
				$data = ['essence'=>0,'utime'=>NOW_TIME,];
			}
			$ret = $model->where(['id'=>$qid])->data($data)->save();
			// var_dump($ret, $model->getLastSql());exit;
			if ( $ret ) {
				$company_id = intval($info['company_id']);
				if ( $company_id > 0 ) {
					D('Companies', 'Model', 'Common')->updateRelQuestions($company_id);
					$lInfos = D('Infos','Logic','Common');
					if ( $info['status']==2 ) {
						// 重新向新闻池推送数据
						$info = array_merge($info, $data);
						$lInfos->pushNewsPool($info, $lInfos::TYPE_PNQ);
					} else {
						// 从新闻池删除问题
						$lInfos->pushNewsPool($info, $lInfos::TYPE_PNQ, true);
					}
				}
				$this->ajax_return(['status'=>true, 'reason'=>'操作成功']);
			} else {
				$msg = '操作失败';
				$this->ajax_error($msg);
			}
		}

		// 回答操作接口
		public function pnaact() {
			$act = I('get.act', '', 'trim,strtolower');
			$acts = ['del', 'undel'];
			if ( !in_array($act, $acts) ) $this->ajax_error('操作失败，请指定正确的操作类型');
			$aid = I('get.id', 0, 'intval');
			if ( $aid<=0 ) $this->ajax_error('请提交要操作的回答编号');
			$model = D('PersonAnswers', 'Model', 'Common');
			$info = $model->find($aid);
			if ( !$info ) $this->ajax_error('您要操作的回答不存在');
			$mQuestion = D('PersonQuestions', 'Model', 'Common');
			$question = $mQuestion->find($info['question_id']);
			if ( !$question ) $this->ajax_error('您要操作的问题不存在');
			$extra = json_decode($info['extra'], true);
			if ( $act == 'del' ) {
				$extra['last_status'] = intval($info['status']);
				$extra = json_encode($extra);
				$data = ['status'=>0,'extra'=>$extra,'utime'=>NOW_TIME,];
				if ( intval($info['status'])==2 ) {
					$mQuestion->where(['id'=>$info['question_id']])->setDec('i_replies', 1);
				}
			}
			if ( $act == 'undel' ) {
				if ( intval($info['status'])!=0 ) $this->ajax_error('问题不是已删除状态，不能做恢复操作');
				if ( isset($extra['last_status']) && intval($extra['last_status'])==1 ) {
					$status = 1;
				} else {
					$status = 2;
					$mQuestion->where(['id'=>$info['question_id']])->setInc('i_replies', 1);
				}
				$data = ['status'=>$status,'utime'=>NOW_TIME,];
			}
			$ret = $model->where(['id'=>$aid])->data($data)->save();
			if ( $ret ) {
				$person_id = intval($info['person_id']);
				if ( $person_id > 0 ) {
					D('Persons', 'Model', 'Common')->updateRelQuestions($person_id);
					$lInfos = D('Infos','Logic','Common');
					if ( $info['status']==2 ) {
						// 重新向新闻池推送数据
						$info = array_merge($info, $data);
						$lInfos->pushNewsPool($info, $lInfos::TYPE_PNA);
					} else {
						// 从新闻池删除问题
						$lInfos->pushNewsPool($info, $lInfos::TYPE_PNA, true);
					}
				}
				$this->ajax_return(['status'=>true, 'reason'=>'操作成功']);
			} else {
				$msg = '操作失败';
				$this->ajax_error($msg);
			}
		}

	// ---- 以下是问题相关功能 ----
		/**
		 * 查看所有问题列表
		 * @@action - page
		 */
		public function all() {
			$rolekey = 'question/list';
			$this->roleinfo = $this->CheckRole($rolekey);

			$dict = $this->_getdicts();
			$filtered_title = '全部';
			$params = $this->_getparams();
			$list = $this->_getlist( $params, array() );
			$this->assign('dict', $dict);
			$this->assign('title', $filtered_title);
			$this->assign('list', $list);
			$this->display('list');
		}
		/**
		 * 查看所有待解决问题列表
		 * @@action - page
		 */
		public function unsolved() {
			$rolekey = 'question/list';
			$this->roleinfo = $this->CheckRole($rolekey);

			$dict = $this->_getdicts();
			$filtered_status = '21'; // 状态为21时的问题被定义为待解决的问题
			$filtered_title = $dict['status'][$filtered_status]['label'];
			$params = $this->_getparams(array('status'=>$filtered_status));
			$list = $this->_getlist( $params, array() );
			$this->assign('dict', $dict);
			$this->assign('title', $filtered_title);
			$this->assign('list', $list);
			$this->display('list');
		}
		/**
		 * 查看所有有回答的问题列表
		 * @@action - page
		 */
		public function answered() {
			$rolekey = 'question/list';
			$this->roleinfo = $this->CheckRole($rolekey);

			$dict = $this->_getdicts();
			$filtered_status = '22'; // 状态为22时的问题被定义为已回答的问题
			$filtered_title = $dict['status'][$filtered_status]['label'];
			$params = $this->_getparams(array('status'=>$filtered_status));
			$list = $this->_getlist( $params, array() );
			$this->assign('dict', $dict);
			$this->assign('title', $filtered_title);
			$this->assign('list', $list);
			$this->display('list');
		}
		/**
		 * 查看所有已采纳的问题列表
		 * @@action - page
		 */
		public function best() {
			$rolekey = 'question/list';
			$this->roleinfo = $this->CheckRole($rolekey);

			$dict = $this->_getdicts();
			$filtered_status = '23'; // 状态为23时的问题被定义为已采纳的问题
			$filtered_title = $dict['status'][$filtered_status]['label'];
			$params = $this->_getparams(array('status'=>$filtered_status));
			$list = $this->_getlist( $params, array() );
			$this->assign('dict', $dict);
			$this->assign('title', $filtered_title);
			$this->assign('list', $list);
			$this->display('list');
		}
		/**
		 * 查看所有待审核的问题列表
		 * @@action - page
		 */
		public function unverified() {
			$rolekey = 'question/list';
			$this->roleinfo = $this->CheckRole($rolekey);

			$dict = $this->_getdicts();
			$filtered_status = '12'; // 状态为12时的问题被定义为待审核的问题
			$filtered_title = $dict['status'][$filtered_status]['label'];
			$params = $this->_getparams(array('status'=>$filtered_status));
			$list = $this->_getlist( $params, array() );
			$this->assign('dict', $dict);
			$this->assign('title', $filtered_title);
			$this->assign('list', $list);
			$this->display('list');
		}
		/**
		 * 临时 : 需要导入到系统的
		 * @@action - page
		 */
		public function tocheck() {
			$rolekey = 'question/list';
			$this->roleinfo = $this->CheckRole($rolekey);

			$dict = $this->_getdicts();
			$filtered_status = '11'; // 状态为11时的问题被定义为待确认或待更新的问题
			$filtered_title = $dict['status'][$filtered_status]['label'];
			$params = $this->_getparams(array('status'=>$filtered_status));
			$list = $this->_getlist( $params, array() );
			$this->assign('dict', $dict);
			$this->assign('title', $filtered_title);
			$this->assign('list', $list);
			$this->display('list');
		}
		/**
		 * 查看近七天新增的问题列表
		 * @@action - page
		 */
		public function lastweek() {
			$rolekey = 'question/list';
			$role = $this->CheckRole($rolekey);

			$dict = $this->_getdicts();
			$filtered_title = '近七天新增';
			$today_zero = strtotime(date('Y-m-d 00:00:00', NOW_TIME));
			$range = array(
				'start' => strtotime('-7 days', $today_zero),
				'end' => $today_zero,
			);
			$params = $this->_getparams();
			$list = $this->_getlist( $params, $range );
			$this->assign('dict', $dict);
			$this->assign('title', $filtered_title);
			$this->assign('list', $list);
			$this->display('list');
		}

		protected function _getlist( $params=array(), $range=array(), $order='id desc' ) {
			$result = array();

			// 一次性获取所有栏目
			$lCate = D('Cate','Logic','Common');
			$catetree = $lCate->getIndexTopCategories('qa');
			foreach ( $catetree as $cateid => $cate ) {
				if ( !in_array($cateid, $this->roleinfo['roleCate']) ) {
					unset($catetree[$cateid]);
				}
			}
			$this->assign('catetree', json_encode($catetree));

			// 参数字典数据
			$dicts = C('DICT.ASK');
			$this->assign('dicts', $dicts);

			// 获取问题数据列表
			$page = intval($params['page']);
			$page = $page <= 0 ? 1 : $page;
			$pagesize = intval($params['pagesize']);
			$pagesize = $pagesize == 0 ? $this->pagesize : $pagesize;
			unset($params['page']);
			unset($params['pagesize']);

			// var_dump($params);
			// var_dump($this->roleinfo['roleCate']);
			if ( $params['cate1']!='' ) {
				if ( !in_array($params['cate1'], $this->roleinfo['roleCate']) ) {
					$this->error('栏目权限不够');
				} else {
					if ( !array_key_exists($params['cate2'], $catetree[$params['cate1']]['son']) ) {

					}
				}
			} else {
				$params['cate2'] = '';
			}
			// var_dump($params);
			$opts = $this->_getopts($params);
			$order = 'id desc';
			if ( $range ) {
				$range = array( 'ctime' => array('between', array($range['start'], $range['end'])) );
				$opts = $range + $opts;
				$order = 'ctime desc';
			}
			$mQuestion = D('Question', 'Model', 'Common');
			$total = $mQuestion->where($opts)->count();
			$result = $mQuestion->where($opts)->order($order)->page($page, $pagesize)->select();
			// var_dump($opts);
			// echo '<!--', PHP_EOL, $mQuestion->getLastSql(), PHP_EOL, '-->', PHP_EOL;
			// 附加数据拼装
			// var_dump($result);

			// 会员用户
			// - 暂时先不处理。直接使用昵称字段显示
			// 点赞总数统计
			$qids = array();
			$goods = array();
			$counter = array();
			foreach ( $result as $i => $item ) {
				array_push($qids, $item['id']);
			}
			if ( !empty($qids) ) {
				$mAnswers = D('Answers', 'Model', 'Common');
				$where = array(
					'qid'=>array('in', $qids),
					'status'=>array('in', array(21,22,23)),
				);
				$list = $mAnswers->where($where)->field('qid, sum(i_good) as i_good')->group('qid')->select();
				foreach ( $list as $i => $item ) {
					$goods[$item['qid']] = intval($item['i_good']);
				}
				unset($list);
			}

			$lTags = D('Tags', 'Logic', 'Common');
			foreach ( $result as $i => $item ) {
				$result[$i]['i_goods'] = isset($goods[$item['id']]) ? $goods[$item['id']] : 0;
				if ( substr(trim($result[$i]['data']), 0, 1)=='{' ) {
					$result[$i]['data'] = json_decode($item['data'], true);
					if ( !isset($result[$i]['data']['origin']) || !is_array($result[$i]['data']['origin']) ) {
						$result[$i]['data']['origin'] = array('name'=>'未知', 'url'=>'#');
					}
				} else {
					$result[$i]['data'] = array(
						'origin' => array('name'=>'未知', 'url'=>'#'),
						'scope' => array('city_cn'=>'', 'city_en'=>'', 'ip'=>''),
					);
				}
				$result[$i]['tagsinfo'] = $lTags->getTagnamesByTagids($item['tagids'],'pc');
				// 修复一些脏数据
				if ( isset($result[$i]['data']['images']) ) {
					$images = $result[$i]['data']['images'];
					if ( !is_array($images) ) {
						$images = explode(',', $images);
					}
					foreach ( $images as $i_i => $image ) {
						if ( trim($image) == '' ) {
							unset($images[$i_i]);
						}
					}
					$image_count = count($images);
					$result[$i]['i_images'] = $image_count;
					$result[$i]['data']['images'] = $images;
				}
				if ( $item['scope']=='' ) {
					$scope = '';
					if ( trim($result[$i]['data']['ip'])!='' ) {
						$scope = $result[$i]['data']['ip'];
					}
					if ( trim($result[$i]['data']['scope']['city_cn'])!='' ) {
						$scope = $result[$i]['data']['scope']['city_cn'];
					} else {
						if ( trim($result[$i]['data']['scope']['ip'])!='' ) {
							$scope = $result[$i]['data']['scope']['ip'];
						}
					}
					$result[$i]['scope'] = $scope;
				}
				
			}

			// 获取已推荐的数据
			$lRecommend = D('Recommend', 'Logic', 'Common');
			$rcmd_list = $lRecommend->getRecommends('qa');
			$rcmds = array('focus'=>array());
			foreach ( $rcmd_list as $i => $item ) {
				if ( intval($item['flag'])==1 ) {
					array_push($rcmds['focus'], $item['relid']);
				}
			}
			$this->assign('recommend', $rcmds);

			// 组织分页数据
			$baselink = '/Question/'.ACTION_NAME;
			$linkopts = $params;
			unset($linkopts['page']);
			unset($linkopts['pagesize']);
			foreach ( $linkopts as $name => $value ) {
				$linkopts[$name] = $name . '=' . $value;
			}
			$linkstring = $baselink . ( !empty($linkopts) ? '?page=#&'.implode('&',$linkopts) : '?page=#' );
			$linkopts = $params;
			unset($linkopts['page']);
			unset($linkopts['pagesize']);
			$pager_opts = array(
				'first' => true, //首页
				'last' => true,	//尾页
				'prev' => true, //上一页
				'next' => true, //下一页
				'number' => 5, //显示页码数
				'var' => 'page', // 列表的页码变量参数
				'jump' => true, // 是否启动跳页
				'linkstring' => $linkstring,
				'linkopts' => $linkopts,
			);
			$pager = pager($page, $total, $pagesize, $pager_opts);
			$this->assign('pager', $pager);
			// var_dump($dicts);
			return $result;
		}
		protected function _getopts( $params=array() ) {
			$opts = array(
				'id' => array('gt', 0),
			);
			if ( intval($params['id'])>0 ) {
				$opts['id'] = intval($params['id']);
			}
			if ( $params['status']!='' ) {
				$opts['status'] = $params['status'];
			}
			if ( in_array($params['answered'], array('has', 'no')) ) {
				if ( $params['answered']=='has' ) {
					$opts['a_replies'] = array('neq', 0);
				}
				if ( $params['answered']=='no' ) {
					$opts['a_replies'] = 0;
				}
			}
			if ( trim($params['keyword'])!='' ) {
				$opts['title'] = array('like', "%{$params['keyword']}%");
				// $opts['desc'] = array('like', "%{$params['keyword']}%");
			}
			if ( trim($params['tag'])!='' ) {
				$opts['tags'] = array('like', "%{$params['tag']}%");
			}
			$cate1 = $params['cate1'];
			$cate2 = $params['cate2'];
			if ( $cate1=='' && $cate2=='' ) {
				$catepath = array();
				foreach ( $this->roleinfo['roleCate'] as $i => $cateid ) {
					array_push($catepath, '0-'.trim($cateid).'-%');
				}
				$catepath = array('like', $catepath, 'OR');
			} else {
				$catepath = '0-'.trim($cate1).'-'.($cate2==''?'%':$cate2);
				$catepath = array('like', $catepath);
				$opts['catepath'] = $catepath;
			}
			// $opts['catepath'] = array($catepath, array('eq', '0-'), 'OR');
			return $opts;
		}
		protected function _getparams( $fix = array() ) {
			$params = I('get.');
			// var_dump($params);
			$filters = array(
				'cate1'=>'', 'cate2'=>'', 'tag'=>'', 'status'=>'', 'keyword'=>'',
				'page'=>1, 'pagesize'=>$this->pagesize, 'answered'=>'', 'id'=>'',
			);
			$fix = array_intersect_key($fix, $filters);
			// var_dump($fix);
			$params = array_intersect_key($params, $filters);
			// var_dump($params);
			foreach ( $fix as $param_name => $param_value ) {
				$params[$param_name] = $param_value;
			}
			// var_dump($params);
			$this->assign('opts', $params);
			return $params;
		}
		protected function _getdicts() {
			return array(
				'status' => array(
					'0'  => array('label'=>'已删除', 'class'=>'l_gray'),
					'11' => array('label'=>'待确认', 'class'=>''),
					'12' => array('label'=>'待审核', 'class'=>'l_org wd-hover-5'),
					'21' => array('label'=>'待解决', 'class'=>'l_red1'),
					'22' => array('label'=>'已回答', 'class'=>'l_grn2'),
					'23' => array('label'=>'已采纳', 'class'=>'l_grn1'),
				),
				'answered' => array(
					'all' => array('label'=>'所有'),
					'has' => array('label'=>'有回答'),
					'no' => array('label'=>'没有回答'),
				),
			);
		}
		/**
		 * 查看所有问题统计数据
		 * @@action - ajax
		 */
		public function statistic() {
			$key = 'QA:ADMIN:STATS:CHART';
			$this->redis = S(C('REDIS'));
			$list = $this->redis->get($key);
			$result = array('status'=>true, 'list'=>$list);
			if ( !$list ) {
				// 补跑
			}
			$this->ajax_return($result);
		}

	// ---- 以下是问题列表中相关功能 ----
		/**
		 * 获取问题详细信息
		 * @@action - ajax
		 */
		public function info() {
			$rolekey = 'question/list';
			$this->roleinfo = $this->CheckRole($rolekey);

			$id = I('get.qid', 0, 'intval');
			if ( $id<=0 ) {
				$this->ajax_error('请指定待查看的问题编号');
			}

			$mQuestion = D('Question', 'Model', 'Common');
			$where = array('id'=>$id);
			$ret = $mQuestion->where($where)->find();
			if ( !$ret ) {
				$this->ajax_error('没有指定的问题');
			} else {
				$this->ajax_return(array('status'=>true, 'list'=>$ret, 'reason'=>'查询成功'));
			}
		}

		/**
		 * 更新问题数据
		 * @@action - ajax
		 */
		public function save() {
			$rolekey = 'question/edit';
			$this->roleinfo = $this->CheckRole($rolekey);

			$id = I('request.qid', 0, 'intval');
			$form = I('request.');
			if ( $id<=0 ) {
				$this->ajax_error('请指定待更新的问题编号');
			}

			# 只接收 catepath 和 tags 数据变更
			$fields = array('catepath'=>'', 'tags'=>'', 'tagids'=>'');
			$form = array_intersect_key($form, $fields);
			if ( empty($form) ) {
				$this->ajax_error('请指定待更新的问题数据');
			}

			$data = array();
			# 验证 catepath，如果 catepath 符合规则，补全 cateid
			$cate1 = '';
			$data['catepath'] = $form['catepath'];
			$catepath = explode('-', $form['catepath']);
			$cate1 = intval($catepath[1]);
			if ( $cate1!=0 ) {
				if ( !in_array(strval($cate1), $this->roleinfo['roleCate']) ) {
					$this->ajax_error('您对此栏目无操作权限');
				}
				if ( count($catepath)==3 ) {
					$data['cateid'] = intval($catepath[2]);
				}
			}

			# 处理并验证 tags 参数
			$tagids = array();
			$tags = array();
			if ( isset($form['tagids']) && trim($form['tagids'])!='' ) {
				$_tagids = explode(',', trim($form['tagids']));
				foreach ( $_tagids as $i => $tag ) {
					$tag = intval($tag);
					if ( $tag == 0 ) continue;
					if ( !in_array($tag, $tagids) ) {
						array_push($tagids, $tag);
					}
				}
				$_tags = explode(' ', str_replace(',', ' ', trim($form['tags'])));
				foreach ( $_tags as $i => $tag ) {
					$tag = trim($tag);
					if ( $tag == '' ) continue;
					if ( !in_array($tag, $tags) ) {
						array_push($tags, $tag);
					}
				}
			}

			# 数据更新
			$mQuestion = D('Question', 'Model', 'Common');
			$where = array('id'=>$id);
			$info = $mQuestion->where($where)->find();

			// 添加容错处理
			if ( !$info ) {
				$this->ajax_error('请指定有效的问题');
			}

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($id),			// 被修改的问题id
				'title' => trim($info['title']), // 被修改的问题标题
			);
			$log_actid = QA_MOD_ACT;

			if ( !empty($tagids) ) {
				$data['tags'] = implode(' ', $tags);
				$data['tagids'] = ','.implode(',', $tagids).',';
				$sign_o = md5($data['catepath'].$data['tagids']);
				$sign_n = md5($info['catepath'].$info['tagids']);
			} else {
				$sign_o = md5($data['catepath']);
				$sign_n = md5($info['catepath']);
			}
			if ( $sign_o == $sign_n ) {
				$this->ajax_return(array('status'=>true,'reason'=>'数据相同不需要更新'));
			}
			$ret = $mQuestion->where($where)->data($data)->save();
			if ( !$ret ) {
				$error_msg = '更新失败';
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));

				$this->ajax_error($error_msg);
			} else {
				# 原始数据状态如果已经在索引中，更新索引
				if ( in_array(intval($info['status']), array(21,22,23)) ) {
					$this->_index_update($id);
				}
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_return(array('status'=>true,'reason'=>'更新成功'));
			}
		}

		/**
		 * [x] 获取问题审核的详细信息
		 * @@action - ajax
		 */
		public function notice() {
			$rolekey = 'question/list';
			$this->roleinfo = $this->CheckRole($rolekey);
			$id = I('get.qid', 0, 'intval');
			if ( $id<=0 ) {
				$this->ajax_error('请指定待查看审核信息的问题编号');
			}

			$mQuestion = D('Question', 'Model', 'Common');
			$where = array('id'=>$id);
			$ret = $mQuestion->where($where)->find();
			if ( !$ret ) {
				$this->ajax_error('没有指定的问题');
			} else {
				$content = strip_tags($ret['title'] . PHP_EOL . $ret['desc']);
				$content = $content . PHP_EOL . '强女干，色情,av，女优';
				// 使用 垃圾过滤机制 获取 审核结果
				$lSensitive = D('Sensitive', 'Logic', 'Common');
				$result = $lSensitive->detect($content, 1);
				if ( is_array($result['sensitivewords']) ) {
					$reason = array();
					foreach ( $result['sensitivewords'] as $i => $word ) {
						if ( array_key_exists($word['reason'], $reason) ) {
							array_push($reason[$word['reason']], $word['word']);
						} else {
							$reason[$word['reason']] = array($word['word']);
						}
					}
				} else {
					$reason = array();
				}
				$result = array(
					'content' => $content,
					'sensitive' => $result['sensitivewords'],
					'highlight' => $result['hlquery'],
					'score' => $result['evaluateValue'],
					'reason' => $reason,
				);
				$this->ajax_return(array('status'=>true, 'list'=>$result, 'reason'=>'查询成功'));
			}
		}

		/**
		 * 删除一个问题 (伪删除)
		 * @@action - ajax
		 */
		public function remove() {
			$rolekey = 'question/del';
			$this->roleinfo = $this->CheckRole($rolekey);

			$id = I('get.qid', 0, 'intval');
			if ( $id<=0 ) {
				$this->ajax_error('请指定待删除的问题编号');
			}

			$mQuestion = D('Question', 'Model', 'Common');
			$where = array('id'=>$id);
			$data = array('status'=>0);
			$info = $mQuestion->where($where)->find();
			if ( !$info ) {
				$this->ajax_error('没有指定的问题');
			}

			$catepath = explode('-', $info['catepath']);
			$cate1 = $catepath[1];
			if ( $cate1!='' && !in_array($cate1, $this->roleinfo['roleCate']) ) {
				$this->ajax_error('您没有此栏目问题的删除权限');
			}

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($id),			// 被删除的问题id
				'title' => trim($info['title']), // 被删除的问题标题
			);
			$log_actid = QA_REMOVE_ACT;

			$ret = $mQuestion->where($where)->data($data)->save();
			if ( !$ret ) {
				$error_msg = '删除失败';
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_error($error_msg);
			} else {
				$this->_index_update($id);

				// 如果是专家问答，清理专家问答的状态
				$mOplogs = D('Oplogs', 'Model', 'Common');
				$where = array(
					'relid' => $id,
					'act' => 41,
				);
				$isProQ = $mOplogs->where($where)->find();
				if ( $isProQ ) {
					$mOplogs->where($where)->delete();
					D('Members', 'Model', 'Common')
						->where(array('uid'=>$isProQ['uid']))
						->setDec('i_needanswer', 1);
				}
				// 注意：问答如果是提给指定专家的，那么后台删除后，再恢复，将不再指定专家了

				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));

				$this->ajax_return(array('status'=>true, 'reason'=>'删除成功'));
			}
		}

		/**
		 * 恢复一个问题 (伪删除 => ?)
		 * @@action - ajax
		 */
		public function restore() {
			$rolekey = 'question/del';
			$this->roleinfo = $this->CheckRole($rolekey);

			$id = I('get.qid', 0, 'intval');
			if ( $id<=0 ) {
				$this->ajax_error('请指定待恢复的问题编号');
			}

			$mQuestion = D('Question', 'Model', 'Common');
			$mAnswers = D('Answers', 'Model', 'Common');
			$list = $mAnswers->where(array('qid'=>$id))->field('id,status,is_best')->select();
			$answer_count = 0;
			$answer_best = 0;
			foreach ( $list as $i => $item ) {
				$status = ceil( $item['status'] / 10 );
				if ( $status == 2 ) { $answer_count ++; }
				if ( intval($item['status'])==22 ) { $answer_best ++; }
			}
			$status = 21;	// 待解决
			if ( $answer_best > 0 ) { $status = 23; } // 已采纳
			if ( $answer_count > 0 ) { $status = 22; } // 已回答

			$where = array('id'=>$id);
			$data = array('status'=>$status);
			$info = $mQuestion->where($where)->find();
			if ( !$info ) {
				$this->ajax_error('没有指定的问题');
			}
			$catepath = explode('-', $info['catepath']);
			$cate1 = $catepath[1];
			if ( $cate1!='' && !in_array($cate1, $this->roleinfo['roleCate']) ) {
				$this->ajax_error('您没有此栏目问题的恢复删除权限');
			}

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($id),			// 待恢复的问题id
				'title' => trim($info['title']), // 待恢复的问题标题
			);
			$log_actid = QA_RESTORE_ACT;

			$ret = $mQuestion->where($where)->data($data)->save();
			if ( !$ret ) {
				$error_msg = '恢复失败';
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_error($error_msg);
			} else {
				$this->_index_update($id);
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_return(array('status'=>true, 'reason'=>'恢复成功'));
			}
		}

		/**
		 * 审核一个问题
		 * @@action - ajax
		 */
		public function verify() {
			$rolekey = 'question/edit';
			$this->roleinfo = $this->CheckRole($rolekey);

			$id = I('get.qid', 0, 'intval');
			if ( $id<=0 ) {
				$this->ajax_error('请指定待审核的问题编号');
			}

			$mQuestion = D('Question', 'Model', 'Common');
			$where = array('id'=>$id, 'status'=>'12');
			$info = $mQuestion->where($where)->find();
			if ( !$info ) {
				$this->ajax_error('指定的待审核问题不存在');
			}

			$mAnswers = D('Answers', 'Model', 'Common');
			$list = $mAnswers->where(array('qid'=>$id))->field('id,status,is_best')->select();
			$answer_count = 0;
			$answer_best = 0;
			foreach ( $list as $i => $item ) {
				$status = ceil( $item['status'] / 10 );
				if ( $status == 2 ) { $answer_count ++; }
				if ( intval($item['status'])==22 ) { $answer_best ++; }
			}
			$status = 21;	// 待解决
			if ( $answer_best > 0 ) { $status = 23; } // 已采纳
			if ( $answer_count > 0 ) { $status = 22; } // 已回答

			$catepath = explode('-', $info['catepath']);
			$cate1 = $catepath[1];
			if ( $cate1!='' && !in_array($cate1, $this->roleinfo['roleCate']) ) {
				$this->ajax_error('您没有此栏目问题的审核权限');
			}

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($id),			// 待审核的问题id
				'title' => trim($info['title']), // 待审核的问题标题
			);
			$log_actid = QA_VERIFY_ACT;


			$where = array('id'=>$id, 'status'=>'12');
			$data = array('status'=>$status, 'utime'=>NOW_TIME);
			$ret = $mQuestion->where($where)->data($data)->save();
			if ( !$ret ) {
				$error_msg = '审核失败';
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_error($error_msg);
			} else {
				$this->_index_update($id);
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_return(array('status'=>true, 'reason'=>'审核成功'));
			}
		}

		/**
		 * 确认导入一个问题
		 * @@action - ajax
		 */
		public function confirm() {
			$rolekey = 'question/edit';
			$this->roleinfo = $this->CheckRole($rolekey);

			$id = I('get.qid', 0, 'intval');
			if ( $id<=0 ) {
				$this->ajax_error('请指定待导入的问题编号');
			}

			$lQuestion = D('Question', 'Logic', 'Common');
			$question = $lQuestion->cleanAnswersIfQuestionNotExists($id);
			if ( true===$question ) {
				$this->ajax_error('待导入的问题不存在');
			}

			$question['status'] = intval($question['status']);
			if ( $question['status']!=11 ) {
				$this->ajax_error('此问题不能被确认导入(id['.$id.']: '.$question['status'].')');
			}

			// 更新问题数据
			$mQuestion = D('Question', 'Model', 'Common');
			$where = array('id'=>$id);
			$data = array('status'=>21);
			$ret = $mQuestion->where($where)->data($data)->save();

			// 自动修正问题数据
			$lQuestion->fixQuestionData($id, $this->auto_update_question_status);
			// 清理问题详情缓存
			$lQuestion->flushCache($id);

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($id),			// 待审核的问题id
				'title' => trim($question['title']), // 待审核的问题标题
			);
			$log_actid = QA_CONFIRM_ACT;

			if ( !$ret ) {
				$error_msg = '确认导入失败';
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_error($error_msg);
			} else {
				$this->_index_update($id);
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_return(array('status'=>true, 'reason'=>'确认导入成功'));
			}
		}

		/**
		 * 推荐一个问题
		 * @@action - ajax
		 */
		public function recommend() {
			$id = I('request.qid', 0, 'intval');
			$act = I('request.act', '', 'strtolower,trim');
			$configs = C('RECOMMEND.QA');
			if ( !array_key_exists($act, $configs['acts']) ) {
				$this->ajax_error('操作异常，没有此操作方法');
			}
			$config = $configs['acts'][$act];
			$method = $config['method'];

			if ( $id<=0 ) {
				$this->ajax_error('请指定待操作的问题编号');
			}

			$lQuestion = D('Question', 'Logic', 'Common');
			$question = $lQuestion->cleanAnswersIfQuestionNotExists($id);
			if ( true===$question ) {
				$this->ajax_error('操作失败，请指定要操作的问题编号');
			}

			// 处理推荐时的图片
			$img = I('request.imgSrc', '', 'strtolower,trim');

			// 通过 推荐业务逻辑处理推荐内容
			$lRecommend = D('Recommend', 'Logic', 'Common');
			$flag = isset($config['flag']) ? intval($config['flag']) : 0;

			$extra = array();
			if ( $act=='focus' ) {
				$extra = array(
					'imgSrc' => $img,
					'title' => $info['title'],
				);
			}
			$ret = call_user_func_array(array($lRecommend, $method), array('type'=>'qa', 'id'=>$id, 'flag'=>$flag, 'extra'=>$extra));
			$result = array('status'=>true, 'reason'=>'推荐操作成功');
			$this->ajax_return($result);
		}

		/**
		 * 在问题变更时，触发是否进行更新索引的处理
		 * 
		 */
		protected function _index_update( $id=0 ) {
			if ( $id==0 ) {
				return false;
			}
			$lQuestionPublish = D('QuestionPublish', 'Logic', 'Common');
			$mQuestion = D('Question', 'Model', 'Common');
			$info = $mQuestion->find($id);
			$status = intval($info['status']);
			if ( in_array($status, array(21,22,23)) ) {
				$lQuestionPublish->Publish(array($id));
				// 用户提问成功后，自动主动推送到百度
				$url = url('show', array($id), 'pc', 'ask');
				$lSearch = D('Search', 'Logic', 'Common');
				$lSearch->pushToBaidu(array($url), 'ask');
			} else {
				$lQuestionPublish->Delete(array($id));
			}
			// var_dump($id, $info, $status, $docs, $ret);
			return $ret;
		}

	// ---- 以下是回答相关功能 ----

		/**
		 * 获取指定问题的所有回复
		 * @@action - ajax
		 */
		public function answers() {
			$qid = I('get.qid', 0, 'intval');
			if ( $qid <= 0 ) {
				$this->ajax_error('请指定要查看的问题编号!');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			$dict = $mAnswers->getStatusDict();
			$dicts = C('DICT.ASK');
			$dict_source = $dicts['SRC'];
			$list = $mAnswers->where(array('qid'=>$qid))->order('id desc')->select();
			foreach ( $list as $i => &$item ) {
				// 转换回答时间为人可读的数据格式
				$item['ctime'] = intval($item['ctime']) > 0 ? date('Y-m-d H:i:s', $item['ctime']) : '';
				// 转换状态参数
				$status = intval($item['status']);
				$item['status_code'] = $status;
				$item['status_class'] = $dict[$status]['class'];
				$item['status'] = $dict[$status]['label'];
				// 转换扩展数据
				if ( !empty(trim($item['data'])) ) {
					$item['data'] = json_decode($item['data'], true);
				} else {
					// 默认数据
					$item['data'] = array(
						// 原始数据来源
						'origin'=>array('url'=>'#', 'name'=>'未知'),
						// 用户所在城市
						'scope'=>array('city_cn'=>'', 'city_en'=>'', 'ip'=>''),
					);
				}
				$item['source_name'] = $dict_source[$item['source']]['name'];
				if ( $item['source']==0 && array_key_exists('origin', $item['data']) ) {
					// $item['source_name'] = $item['source_name'].'('.$item['data']['origin']['name'].')';
					$item['source'] = $item['data']['origin']['url'];
				} else {
					$item['source'] = 'javascript:;';
				}
				// 高亮敏感词
				if ( isset($item['data']['sensitive']) ) {
					$words = $item['data']['sensitive']['words'];
					$highlight = '<span class="l_red2">'.implode('</span><|hr|><span class="l_red2">', $words).'</span>';
					$highlight = explode('<|hr|>', $highlight);
					$item['reply'] = str_replace($words, $highlight, $item['reply']);
				}
				// 补充城市显示数据
				$city_name = '';
				if ( $item['data']['scope'] ) {
					if ( trim($item['data']['scope']['city_cn'])!='' ) {
						$city_name = $item['data']['scope']['city_cn'];
					} else {
						if ( trim($item['data']['scope']['ip'])!='' ) {
							$city_name = $item['data']['scope']['ip'];
						}
					}
				}
				$item['city_name'] = $city_name;
				// 显示当前状态下可以处理的操作
				$item['commands'] = array();
				if ( $status == 21 ) {	// 正常
					$item['commands'][] = '置顶';
					$item['commands'][] = '采纳';
					$item['commands'][] = '删除';
				}
				if ( $status == 0 ) {	// 已删除
					$item['commands'][] = '恢复';
				}
				if ( $status == 11 ) {	// 待确认
					$item['commands'][] = '确认';
					$item['commands'][] = '删除';
				}
				if ( $status == 12 ) {	// 待审核
					$item['commands'][] = '审核通过';
					$item['commands'][] = '删除';
				}
				if ( $status == 22 ) {	// 已置顶
					$item['commands'][] = '取消置顶';
					$item['commands'][] = '删除';
				}
				if ( $status == 23 ) {	// 已采纳
					$item['commands'][] = '删除';
				}
			}
			$result = array(
				'status' => true,
				'list' => $list,
				'dict' => $dict,
			);
			$this->ajax_return($result);
		}

		/**
		 * 删除指定问题的一个回复
		 * @@action - ajax
		 */
		public function answer_delete () {
			$aid = I('get.aid', 0, 'intval');
			if ( $aid <= 0 ) {
				$this->ajax_error('请指定要删除的问题答案编号!');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			$where = array('id'=>$aid);
			$info = $mAnswers->where($where)->find();

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($aid),			// 待审核的问题id
			);
			$log_actid = QAA_REMOVE_ACT;

			if ( $info && in_array($info['status'], array(11,12,21,22,23)) ) {

				$lQuestion = D('Question', 'Logic', 'Common');
				// 自动清理没有问题的回答
				$question = $lQuestion->cleanAnswersIfQuestionNotExists($answer['qid']);
				if ( true===$question ) {
					$this->ajax_error('此回答的问题不存在，相关回答已清理');
				}

				// 更新回答数据
				$where = array('id'=>$aid);
				$data = array('status'=>0);
				$ret = $mAnswers->where($where)->data($data)->save();

				// 自动修正问题数据
				$lQuestion->fixQuestionData($info['qid'], $this->auto_update_question_status);
				// 清理问题详情缓存
				$lQuestion->flushCache($answer['qid']);

				// 操作成功
				$log_note['qid'] = intval($info['qid']);
				$log_note['title'] = trim($info['title']);
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));

				// 更新完成后返回成功信息
				$result = array(
					'status' => true,
					'reason' => '编号为 '.$aid.' 的回复删除成功!',
				);
				$this->ajax_return($result);
			} else {
				$error_msg = '编号为 '.$aid.' 的回复删除失败!';
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_error($error_msg);
			}
		}

		/**
		 * 指定回复编号，恢复一个指定问题回复
		 * @@action - ajax
		 */
		public function answer_restore () {
			$aid = I('get.aid', 0, 'intval');
			if ( $aid <= 0 ) {
				$this->ajax_error('请指定要恢复的问题答案编号!');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			$where = array('id'=>$aid);
			$answer = $mAnswers->where($where)->find();
			if ( !$answer || $answer['status']!=0 ) {
				$this->ajax_error('待恢复的回答不存在，或不是已删除状态，恢复操作失败');
			}


			$lQuestion = D('Question', 'Logic', 'Common');
			// 自动清理没有问题的回答
			$question = $lQuestion->cleanAnswersIfQuestionNotExists($answer['qid']);
			if ( true===$question ) {
				$this->ajax_error('此回答的问题不存在，相关回答已清理');
			}

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($aid),			// 待审核的问题id
			);
			$log_actid = QAA_RESTORE_ACT;


			// 更新回答数据
			// @TODO: 要不要自动做敏感词过滤
			$where = array('id'=>$aid);
			$data = array(
				'status'=> $answer['is_best']>0 ? 23 : 21,
			);
			$ret = $mAnswers->where($where)->data($data)->save();

			// 自动修正问题数据
			$lQuestion->fixQuestionData($answer['qid'], $this->auto_update_question_status);
			// 清理问题详情缓存
			$lQuestion->flushCache($answer['qid']);

			// 操作成功
			$log_note['qid'] = intval($answer['qid']);
			$log_note['title'] = trim($question['title']);
			$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));

			// 更新完成后返回成功信息
			$result = array(
				'status' => true,
				'reason' => '编号为 '.$aid.' 的回复恢复成功!',
			);
			$this->ajax_return($result);
		}

		/**
		 * 指定回复编号，彻底删除一个指定问题回复
		 * // 暂时不提供服务
		 * @@action - ajax
		 */
		public function answer_destory () {
			$this->ajax_error('暂不提供此服务!');
			$aid = I('get.aid', 0, 'intval');
			if ( $aid <= 0 ) {
				$this->ajax_error('请指定要销毁的问题答案编号!');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			$where = array('id'=>$aid);
			$answer = $mAnswers->where($where)->find();
			if ( !$answer || $answer['status']!=0 ) {
				$this->ajax_error('编号为 '.$aid.' 的回复不存在，或不是已删除状态，销毁操作失败!');
			}


			$lQuestion = D('Question', 'Logic', 'Common');
			// 自动清理没有问题的回答
			$question = $lQuestion->cleanAnswersIfQuestionNotExists($answer['qid']);
			if ( true===$question ) {
				$this->ajax_error('此回答的问题不存在，相关回答已清理');
			}

			// 物理删除回答数据
			$where = array('id'=>$aid);
			$ret_destory = $mAnswers->where($where)->delete();

			// 自动修正问题数据
			$lQuestion->fixQuestionData($answer['qid'], $this->auto_update_question_status);

			if ( $ret_destory==0 ) {
				$this->ajax_error('编号为 '.$aid.' 的回复销毁失败!');
			}
			$result = array(
				'status' => true,
				'reason' => '编号为 '.$aid.' 的回复销毁成功!',
			);
			$this->ajax_return($result);
		}

		/**
		 * 指定回答编号，置顶一个指定问题回答
		 * @@action - ajax
		 * desc : 后置顶的回答会在最上面
		 */
		public function answer_ontop () {
			$aid = I('get.aid', 0, 'intval');
			if ( $aid <= 0 ) {
				$this->ajax_error('请指定要置顶的问题回答编号!');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			// 验证是否是正常状态下的回答信息
			$where = array('id'=>$aid);
			$answer = $mAnswers->where($where)->find();
			if ( !$answer || $answer['status']!=21 ) {
				$this->ajax_error('正常状态的回答才可以进行置顶操作!');
			}

			// 查询是否存在同一问题的其它置顶项
			$qid = intval($answer['qid']);
			$where = array('qid'=>$qid);
			// $mQuestion = D('Question', 'Model', 'Common');
			$field = 'id, status, is_top';
			$list = $mAnswers->field($field)->where($where)->select();
			foreach ( $list as $i => $item ) {
				if ( $item['status']==22 || $item['is_top']>0 ) {
					$this->ajax_error('一个问题只有设置一个置顶回答');
				}
			}

			// 同一问题，没有其它置顶回答，可以正常设置置顶
			$where = array('id'=>$aid);
			$data = array('status'=>22, 'is_top'=>NOW_TIME);
			$ret = $mAnswers->where($where)->data($data)->save();

			$lQuestion = D('Question', 'Logic', 'Common');
			// 自动修正问题数据
			$lQuestion->fixQuestionData($answer['qid'], $this->auto_update_question_status);
			// 清理问题详情缓存
			$lQuestion->flushCache($answer['qid']);

			$result = array(
				'status' => true,
				'reason' => '编号为 '.$aid.' 的回答置顶成功!',
			);
			$this->ajax_return($result);
		}

		/**
		 * 指定回答编号，取消置顶一个指定问题回答
		 * @@action - ajax
		 */
		public function answer_untop () {
			$aid = I('get.aid', 0, 'intval');
			if ( $aid <= 0 ) {
				$this->ajax_error('请指定要置顶的问题回答编号!');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			$where = array('id'=>$aid);
			$answer = $mAnswers->where($where)->find();
			if ( !$answer || $answer['status']!=22 ) {
				$this->ajax_error('请指定正确的回答!');
			}
			$data = array('status'=>21, 'is_top'=>0);
			$ret = $mAnswers->where($where)->data($data)->save();

			$lQuestion = D('Question', 'Logic', 'Common');
			// 自动修正问题数据
			$lQuestion->fixQuestionData($answer['qid'], $this->auto_update_question_status);
			// 清理问题详情缓存
			$lQuestion->flushCache($answer['qid']);

			$result = array(
				'status' => true,
				'reason' => '编号为 '.$aid.' 的回答取消置顶成功!',
			);
			$this->ajax_return($result);
		}

		/**
		 * 指定回复编号，采纳一个指定问题回复
		 * @@action - ajax
		 * ? 产品未设计 取消采纳操作
		 */
		public function answer_best () {
			$aid = I('get.aid', 0, 'intval');
			if ( $aid <= 0 ) {
				$this->ajax_error('请指定要采纳的问题回答编号');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			$where = array('id'=>$aid);
			$answer = $mAnswers->where($where)->find();
			if ( !$answer ) {
				$this->ajax_error('您指定的回答不存在');
			}

			// 清理缓存
			$lQuestion = D('Question', 'Logic', 'Common');
			$question = $lQuestion->cleanAnswersIfQuestionNotExists($answer['qid']);
			if ( true===$question ) {
				$this->ajax_error('此回答的问题不存在，相关回答已清理');
			}
			if ( $question['status']==23 || $question['last_best']!=0 ) {
				$this->ajax_error('此回答的问题已经有被采纳的回答了');
			}

			$answer['status'] = intval($answer['status']);
			if ( in_array($answer['status'], array(21, 22)) ) { // 只有正常的答案才可以进行采纳
				// 更新回答数据
				$where = array('id'=>$aid);
				$data = array('status'=>23, 'is_best'=>NOW_TIME);
				$ret = $mAnswers->where($where)->data($data)->save();

				// 自动修正问题数据
				$lQuestion->fixQuestionData($answer['qid'], $this->auto_update_question_status);
				// 清理问题详情缓存
				$lQuestion->flushCache($answer['qid']);

				// 更新完成后返回成功信息
				$result = array(
					'status' => true,
					'reason' => '编号为 '.$aid.' 的回复采纳成功!',
				);
				$this->ajax_return($result);
			} else {
				$this->ajax_error('此回答状态异常，被采纳的回答需要是正常状态或是置顶状态的回答');
			}

		}

		/**
		 * 指定回复编号，审核通过一个指定问题回复
		 * @@action - ajax
		 */
		public function answer_verify () {
			$aid = I('get.aid', 0, 'intval');
			if ( $aid <= 0 ) {
				$this->ajax_error('请指定要审核的问题回复编号!');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			$where = array('id'=>$aid);
			$answer = $mAnswers->where($where)->find();
			if ( !$answer || $answer['status']!=12 ) {
				// $this->ajax_error('编号为 '.$aid.' 的回复审核失败!');
				$this->ajax_error('待审核状态的回答才可以进行审核操作!');
			}

			// 更新回答数据
			$where = array('id'=>$aid, 'status'=>12);
			$data = array( 'status' => 21, );
			$ret = $mAnswers->where($where)->data($data)->save();

			$lQuestion = D('Question', 'Logic', 'Common');
			// 自动清理没有问题的回答
			$question = $lQuestion->cleanAnswersIfQuestionNotExists($answer['qid']);
			if ( true===$question ) {
				$this->ajax_error('此回答的问题不存在，相关回答已清理');
			}

			// 自动修正问题数据
			$lQuestion->fixQuestionData($answer['qid'], $this->auto_update_question_status);
			// 清理问题详情缓存
			$lQuestion->flushCache($answer['qid']);

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($aid),			// 待审核的问题回复的id
				'qid' => intval($answer['qid']),	// 回复所属的问题编号
				'title' => trim($question['title']), // 问题的标题
			);
			$log_actid = QAA_VERIFY_ACT;
			$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));

			// 更新完成后返回成功信息
			$result = array(
				'status' => true,
				'reason' => '编号为 '.$aid.' 的回复审核成功!',
			);
			$this->ajax_return($result);
		}

		/**
		 * 指定回复编号，确认一个待导入的问题回复 (回复数据为互联网爬取的数据)
		 * @@action - ajax
		 */
		public function answer_confirm () {
			$aid = I('get.aid', 0, 'intval');
			if ( $aid <= 0 ) {
				$this->ajax_error('请指定要确认(导入)的问题回答编号!');
			}

			$mAnswers = D('Answer', 'Model', 'Common');
			// 验证是否是正常状态下的回答信息
			$where = array('id'=>$aid);
			$answer = $mAnswers->where($where)->find();
			if ( !$answer || $answer['status']!=11 ) {
				$this->ajax_error('待确认状态的回答才可以进行确认(导入)操作!');
			}

			// 更新回答数据
			$data = array('status'=>21); // 导入回答后，回答的信息状态默认为正常状态
			$ret = $mAnswers->where($where)->data($data)->save();

			$qid = intval($answer['qid']);
			$lQuestion = D('Question', 'Logic', 'Common');
			// 自动修正问题数据
			$lQuestion->fixQuestionData($qid, $this->auto_update_question_status);
			// 清理问题详情缓存
			$lQuestion->flushCache($qid);

			// 更新完成后返回成功信息
			$result = array(
				'status' => true,
				'reason' => '编号为 '.$aid.' 的回复确认(导入)成功!',
			);
			$this->ajax_return($result);
		}
	// ---- 回答相关功能 定义结束 ----
}