<?php
/**
 * 栏目管理逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class CateController extends BaseController {

	protected $type_mapping = array(
		'kb' => array(
			'auth' => array(
				'list' => 'cate/list',
				'add' => 'cate/add',
				'edit' => 'cate/edit',
				'del' => 'cate/del',
			),
			'type' => 'kb',
			'maxlevel' => 3,
			'title' => '知识栏目',
		),
		'qa' => array(
			'auth' => array(
				'list' => 'cate/qalist',
				'add' => 'cate/qaadd',
				'edit' => 'cate/qaedit',
				'del' => 'cate/qadel',
			),
			'type' => 'qa',
			'maxlevel' => 2,
			'title' => '问答栏目',
		),
	);

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 将操作请求转换为对应的业务
	 * @access protected
	 * @param $act 操作类型
	 * @param $type 业务类型 kb为知识 qa为问答
	 * @return mixed 异常时返回字符器 正常时返回配置数组
	 */
	protected function getActionOpts( $act, $type='kb' ) {
		$type = I('request.type', $type, 'strtolower,trim');
		if ( !array_key_exists($type, $this->type_mapping) ) {
			return '没有指定的业务类型!';
		}
		$opts = $this->type_mapping[$type];
		if ( !array_key_exists($act, $opts['auth']) ) {
			return '没有指定的操作定义!';
		}
		$opts['rolekey'] = $opts['auth'][$act];
		return $opts;
	}

	/**
	 * 知识栏目管理
	 * @@action
	 */
	public function index() {
		$opts = $this->getActionOpts('list');
		$this->categories($opts);
	}

	public function knowledge() { $this->categories($this->getActionOpts('list', 'kb')); }
	public function question() { $this->categories($this->getActionOpts('list', 'qa')); }
	/**
	 * 栏目数据获取
	 * 
	 */
	protected function categories($opts=array()) {
		$rolekey = $opts['rolekey'];
		$this->checkAuthorization($rolekey);

		if ( !IS_GET ) {
			$this->error('请错异常！');
		}

		$list = array();
		$mCategories = D('Categories', 'Model', 'Common');
		$allList = $mCategories->getAllCate($opts['type']);
		if ( $allList ) {
			$list = $this->formatTree($allList);
		}
		// var_dump($list);

		$this->assign('maxlevel', $opts['maxlevel']);
		$this->assign('title', $opts['title']);
		$this->assign('list',$list);
		$this->display('list.'.$opts['type']);
	}

	/**
	 * 格式化栏目树
	 */
	protected function formatTree($data, $pid = 0){
		$list = array();
		$tem = array();
		foreach ( $data as $item ) {
			if ( $item['parent'] == $pid ) {
				$tem = $this->formatTree($data, $item['id']);
				//判断是否存在子数组
				$tem && $item['son'] = $tem;
				$list[] = $item;
			}
		}
		return $list;
	}

	/**
	 * 2017-01-06 新添加接口
	 * 提供给栏目编辑前，读取指定栏目的信息使用
	 * @@action - ajax
	 */
	public function info() {
		$opts = $this->getActionOpts('list');

		$result = array(
			'status'=>false,
			'reason'=>'操作失败',
		);
		// 权限验证
		$rolekey = $opts['rolekey'];	// 栏目 读取/列表显示 权限
		$roleRet = $this->checkAuthorization($rolekey, 'ajax');

		// 指定要读取的栏目编号
		$id = I('get.id', 0, 'intval');
		if ( $id <= 0 ) {
			$result['reason'] = '栏目编号错误';
			$this->ajax_return($result);
		}

		$mCategories = D('Categories', 'Model', 'Common');
		$where = array('type'=>$opts['type'], 'id'=>$id);
		$info = $mCategories->where($where)->find();
		$result['dbg'] = $mCategories->getLastSql();
		if ( !$info ) {
			$result['reason'] = '编号为 '.$id.' 的栏目不存在';
			$this->ajax_return($result);
		}

		// 后期可能会加入栏目 seo 相关的设置
		$filters = array('name'=>'', 'code'=>'', 'type'=>'');
		// 'seo_title'=>'', 'seo_keywords'=>'', 'seo_description'=>''
		$info = array_intersect_key($info, $filters);

		$info['code'] = trim($info['code']);
		$result['status'] = true;
		$result['reason'] = '获取成功';
		$result['info'] = $info;
		$this->ajax_return($result);
	}

	/**
	 * 保存知识栏目设置
	 * @@action - ajax
	 */
	public function add() {
		if ( !IS_POST && !IS_AJAX ) {
			$this->ajax_error('数据提交方式错误，请重试！');
		}

		$opts = $this->getActionOpts('add');

		$rolekey = $opts['rolekey'];
		$this->checkAuthorization($rolekey, 'ajax');

		$num = $opts['maxlevel']; //最大级数
		$post = array();
		$post['id'] = I('request.id', 0, 'intval'); // 添加时，传入的 id 为父栏目 id
		// $post['type'] = I('request.type', '', 'trim,strtolower');
		$post['type'] = $opts['type'];
		$post['name'] = I('request.name', '', 'trim,filterInput,clean_xss');
		$post['code'] = I('request.code', '', 'trim,filterInput,clean_xss');

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'userid' => intval($uid),			// 被操作管理员id
		);
		$log_actid = $opts['type']=='kb' ? KBCATE_ADD_ACT : QACATE_ADD_ACT;

		// 验证分类代码
		$_opts = array('parent'=>$post['id']);
		$valid_msg = $this->validForm($post, $_opts, 'add');
		if ( $valid_msg!==true ) {
			// 创建分类操作异常
			$log_note['status'] = false;
			$log_note['reason'] = $valid_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, 0, json_encode($log_note));
			$this->ajax_error($valid_msg);
		}

		!$post['id'] && $post['id'] = 0;
		$mCategories = D('Categories', 'Model', 'Common');
		$iorder = $mCategories->getMaxIorder($post['id'], $opts['type']) + 1;
		$post['iorder'] = $iorder;
		$getOne = $mCategories->getCateInfo($post['id'], $opts['type']);

		if ($getOne === 0) {
			$post['path'] = '0';
			$post['parent'] = 0;
			$post['level'] = 1;
		} else {
			if ($getOne === NULL) {
				$error_msg = $opts['title'].'栏目类别ID错误';
				// 栏目类别编号错误
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, 0, json_encode($log_note));
				$this->ajax_error($error_msg);
			} else {
				if ($getOne['level'] == $num) {
					$error_msg = '超出最大级数'.$num.'级';
					// 新创建的栏目超过允许的最大级数
					$log_note['status'] = false;
					$log_note['reason'] = $error_msg;
					$this->_logger->addLog($this->_user['id'], $log_actid, 0, json_encode($log_note));
					$this->ajax_error($error_msg);
				}
			}
			$post['path'] = $getOne['path'];
			$post['parent'] = $getOne['id'];
			$post['level'] = $getOne['level'] + 1;
		}

		$post['status'] = 0;

		if ( $post['id'] ) {
			unset($post['id']);
		}
		// 日志记录创建的栏目名称
		$log_note['catename'] = $post['name'];
		if ( $mCategories->create($post) ) {
			if ( $mCategories->add($post) ) {
				$lastid = $mCategories->getLastInsID();
				$mCategories->id = $lastid;
				$allpath = $post['path'] . '-' .$lastid;
				$mCategories->path = $allpath;
				$mCategories->save();
				$result['lastid'] = $lastid;
				$result['name'] = $post['name'];
				$this->addTreeNode($post['parent'], $lastid, $post['name'], $allpath, $opts['type']);

				// 添加操作日志
				$log_note['level'] = $post['level'];
				$log_note['cateid'] = $lastid;
				$this->_logger->addLog($this->_user['id'], $log_actid, 0, json_encode($log_note));

				$result = array( 'status'=>true, 'info'=>array('lastid'=>$lastid), 'reason'=>'添加成功', );
				$this->ajax_return($result);
			} else {
				$error_msg = '系统异常';
				// 创建分类操作异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, 0, json_encode($log_note));
				$this->ajax_error($error_msg);
			}
		} else {
			$error_msg = $mCategories->getError();
			// 创建分类操作异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, 0, json_encode($log_note));

			$this->ajax_error($error_msg);
		}
	}
	protected function addTreeNode($pid, $id, $name, $path, $type='kb') {
		$lCate = D('Cate','Logic','Common');
		$lCate->addTreeNode($pid, $id, $name, $path, $type);
		$lCate->init(1, $type);
		$lCate->toTree(0, $type);
		return true;
	}

	/**
	 * 保存知识栏目设置
	 * @@action - ajax
	 */
	public function edit() {
		if ( !IS_POST || !IS_AJAX ) {
			$this->ajax_error('数据提交方式错误，请重试！');
		}

		$opts = $this->getActionOpts('edit');

		$rolekey = $opts['rolekey'];
		$this->checkAuthorization($rolekey, 'ajax');

		$num = $opts['maxlevel']; //最大级数
		$post = array();
		$post['type'] = $opts['type'];
		$post['id'] = I('request.id', 0, 'intval');
		$post['name'] = I('request.name', '', 'trim,filterInput,clean_xss');
		$post['code'] = I('request.code', '', 'trim,filterInput,clean_xss');

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'userid' => intval($uid),			// 被操作管理员id
		);
		$log_actid = $opts['type']=='kb' ? KBCATE_MOD_ACT : QACATE_MOD_ACT;

		// 验证分类代码
		$_opts = array('id'=>array('neq', $post['id']));
		$valid_msg = $this->validForm($post, $_opts, 'edit');
		if ( $valid_msg!==true ) {
			// 修改栏目操作异常
			$log_note['status'] = false;
			$log_note['reason'] = $valid_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $post['id'], json_encode($log_note));
			$this->ajax_error($valid_msg);
		}

		$log_note['catename'] = $post['name'];

		$mCategories = D('Categories', 'Model', 'Common');
		$ret = $mCategories->save($post);
		if ( $ret ) {
			$this->editTreeNode($post['id'], $post['name'], $opts['type']);
		} else {
			$error_msg = '保存失败';
			// 修改栏目操作异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $post['id'], json_encode($log_note));
			$this->ajax_error($error_msg);
		}


		// 添加操作日志
		$log_note['cateid'] = $post['id'];
		$this->_logger->addLog($this->_user['id'], $log_actid, $post['id'], json_encode($log_note));
		$result = array('status'=>true, 'reason'=>'保存成功!',);
		$this->ajax_return($result);
	}
	protected function editTreeNode($id, $name, $type='kb') {
		$lCate = D('Cate','Logic','Common');
		$lCate->editTreeNode($id, $name, $type);
		$lCate->init(1, $type);
		$lCate->toTree(0, $type);
		return true;
	}


	/**
	 * 验证栏目代码是否符合产品设定
	 * 只允许小写英文字符串
	 * 至少 ? 字符 最多 50 个字符
	 * 栏目代码不允许重复
	 */
	protected function validForm( $form, $opts=array(), $action='add' ) {
		if ( !array_key_exists($form['type'], $this->type_mapping) ) {
			return '栏目类型错误!';
		}
		if ( $action=='edit' && intval($form['id'])==0 ) {
			return '请指定要修改的栏目编号 id !';
		}

		// 栏目代码基本验证
		$code_len = strlen($post['code']);
		// if ( $code_len > 20 ) {
		// 	return '分类代码超过长度 20 个字符的限制!';
		// }
		if ( $code_len > 50 ) {
			return '栏目代码长度不得超过 50 个字符!';
		}
		$reg = '/[a-z]+/';
		$valid = preg_match($reg, $form['code'], $match);
		if ( $valid==0 || $match[0]!=$form['code'] ) {
			return '栏目代码必须为小写半角英文字符!';
		}

		// 栏目名称基本验证
		if ( empty($form['name']) ) {
			return '请按填写栏目名称!';
		}
		// 字符串长度验证
		$name_len = abslength(clear_all($form['name']));
		if ( $name_len > 10 ) {
			return '分类名称超出 10 个字!';
		}

		$mCategories = D('Categories', 'Model', 'Common');
		// 如果是修改操作，先验证
		if ( $action=='edit' ) {
			$info = $mCategories->find($id);
			if (!$info) {
				$this->ajax_error('待修改的栏目编号 id 错误!');
			}
			$opts['parent'] = $info['parent'];
		}

		// 验证栏目代码是否重复
		$where = $opts + array('type'=>$form['type'], 'code'=>$form['code']);
		$exist = $mCategories->where($where)->find();
		if ( $exist ) {
			return '栏目代码重复!';
		}

		$where = array(
			'type'=>$form['type'],
			'parent'=>$form['id'],
			'name'=>$form['name'],
		);
		if ( $action=='edit' ) {
			$where['parent'] = $info['parent'];
			$where['id'] = array('neq', $form['id']);
		}
		$exist = $mCategories->where($where)->find();
		if ( $exist ) {
			return '栏目名称已存在';
		}

		return true;
	}


	/**
	 * 栏目向上调整顺序
	 * @@action - ajax
	 */
	public function exchange() {
		if ( !IS_GET && !IS_AJAX ) {
			$this->ajax_error('数据提交方式错误，请重试！');
		}

		$opts = $this->getActionOpts('edit');

		$result = array(
			'status'=>false,
			'reason'=>'操作失败',
		);
		$oid = I('request.oid', 0, 'intval');
		$nid = I('request.nid', 0, 'intval');
		$rolekey = $opts['rolekey'];
		$this->checkAuthorization($rolekey, 'ajax');

		if ($oid && $nid) {
			$mCategories = D('Categories', 'Model', 'Common');

			$oinfo = $mCategories->getCateInfo($oid, $opts['type']);
			$ninfo = $mCategories->getCateInfo($nid, $opts['type']);
			if ($oinfo && $ninfo && ($oinfo['parent'] == $ninfo['parent'])) {
				$omap['id'] = $oinfo['id'];
				$omap['iorder'] = $ninfo['iorder'];
				$nmap['id'] = $ninfo['id'];
				$nmap['iorder'] = $oinfo['iorder'];

				if ($mCategories->save($omap) && $mCategories->save($nmap)) {
					$lCate = D('Cate','Logic','Common');
					$lCate->exchTreeNode($oinfo['parent'], $oid, $nid, $opts['type']);
					$result['status'] = true;
					$result['reason'] = '操作成功';
				}
			} else {
				$result['reason'] = '栏目ID错误';
			}
		} else {
			$result['reason'] = '请按正确的格式填写';
		}
		$this->ajax_return($result);
	}

	/**
	 * 栏目显示状态切换更新
	 * @@action - ajax
	 */
	public function onoff() {
		if ( !IS_GET && !IS_AJAX ) {
			$this->ajax_error('数据提交方式错误，请重试!');
		}

		$type = I('request.type', '', 'strtolower,trim');
		if ( $type=='' ) {
			$this->ajax_error('请指定操作栏目类别，是知识栏目还是问答栏目？');
		}
		// 针对问答栏目的隐藏操作逻辑
		if ( $opts['type']=='qa' ) {
			$this->ajax_error('针对问答栏目的隐藏/恢复隐藏功能已被禁用! 如有特别需求，请与 #凌雷 联系');
		}

		$opts = $this->getActionOpts('edit', $type);
		$rolekey = $opts['rolekey'];
		$roleRet = $this->checkAuthorization($rolekey, 'ajax');
		if ( $roleRet['status'] == 'fail' ) {
			$this->ajax_error('权限不够!');
		}

		$id = I('request.id', 0, 'intval');
		if ( $id == 0 ) {
			$this->ajax_error('请指定的栏目编号!');
		}

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'userid' => intval($uid),			// 被操作管理员id
		);
		$log_actid = $opts['type']=='kb' ? KBCATE_HIDE_ACT : QACATE_HIDE_ACT;

		$mCategories = D('Categories', 'Model', 'Common');
		$where = array('type'=>$opts['type'], 'id'=>$id);
		$cate = $mCategories->where($where)->find();
		if ( !$cate ) {
			$error_msg = '栏目编号不存在';
			// 修改栏目操作异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
			$this->ajax_error($error_msg);
		}

		// 审计操作日志数据
		$log_note['cateid'] = $id;
		$log_note['catename'] = $cate['name'];
		$parent = $cate['parent'];
		$lCate = D('Cate','Logic','Common');
		// 隐藏栏目
		if ( $cate['status'] == 0 ) {
			$data['id'] = $cate['id'];
			$data['status'] = 1;	// 隐藏状态，逻辑删除
			if ( $mCategories->save($data) ) {
				$lCate->delTreeNode($parent, $id, $opts['type']);
				$result = array(
					'status' => true,
					'reason' => '操作成功',
				);
			}
			// 审计操作日志数据
			$log_note['action'] = '禁用';
		}

		// 恢复栏目
		if ( $cate['status'] == 1 ) {
			$data['id'] = $cate['id'];
			$data['status'] = 0;
			if ( $mCategories->save($data) ) {
				$lCate->recoverTreeNode($parent, $id, $cate['name'], $cate['path'], $opts['type']);
				$result = array(
					'status' => true,
					'reason' => '操作成功',
				);
			}
			// 审计操作日志数据
			$log_note['action'] = '启用';
		}

		// 修改栏目操作异常
		$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
		$this->ajax_return($result);
	}


	/**
	 * 栏目删除操作
	 * - 连带栏目下的数据一并删除
	 * @@action - ajax
	 */
	public function destory() {
		if ( !IS_GET && !IS_AJAX ) {
			$this->ajax_error('数据提交方式错误，请重试!');
		}

		$opts = $this->getActionOpts('del');
		$rolekey = $opts['rolekey'];
		$roleRet = $this->checkAuthorization($rolekey, 'ajax');
		if ( $roleRet['status'] == 'fail' ) {
			$this->ajax_error('权限不够');
		}

		$id = I('request.id', 0, 'intval');
		if ( $id <= 0 ) {
			$this->ajax_error('请指定待删除的栏目编号!');
		}

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'userid' => intval($uid),			// 被操作管理员id
		);
		$log_actid = $opts['type']=='kb' ? KBCATE_DEL_ACT : QACATE_DEL_ACT;

		$mCategories = D('Categories', 'Model', 'Common');
		$where = array('type'=>$opts['type'], 'id'=>$id);
		$cate = $mCategories->where($where)->find();
		if ( !$cate ) {
			$error_msg = '数据不存在或已经删除，请刷新';
			// 修改栏目操作异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
			$this->ajax_error($error_msg);
		}

		// 审计操作日志数据
		$log_note['cateid'] = $id;
		$log_note['catename'] = $cate['name'];

		$result = array('status'=>false, 'reason'=>'');

		$can_destory = false;
		// 只针对知识栏目中的知识数据进行清理
		if ( $opts['type']=='kb' ) {
			$parent = $cate['parent'];
			$path = $cate['level']<$opts['maxlevel'] ? $cate['path'].'-' : $cate['path'];

			// 删除栏目数据
			// 1. 内容数据表
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$where = array(
				'type' => $opts['type'],
				'catepath' => array(
					array('like', $path.'%'),
					array('like', $path.'-%'),
					'OR',
				),
			);
			$data_exists = $mKnowledge->where($where)->count();
			$data_exists = intval($data_exists);
			// 当且仅当指定栏目下没有任何知识数据时，才允许删除操作
			if ( $data_exists == 0 ) {
				$cnt1 = $mKnowledge->where($where)->delete();
				// 2. 历史数据表
				$mKnowledgeHistory = D('KnowledgeHistory', 'Model', 'Common');
				$cnt2 = $mKnowledgeHistory->where($where)->delete();
				// 3. 搜索服务数据表
				$lSearch = D('Search', 'Logic', 'Common');
				if ( $cate['level']==3 ) {
					$_opts = array(
						array($cate['id'], '_multi.cateid')
					);
					$prefix = array();
				} else {
					$_opts = array();
					$prefix = array(array($path, '_multi.catepath'));
				}
				$cnt3 = $lSearch->batchesRemove($_opts, $prefix);
				$result['debug'] = array(
					'knowledge' => $cnt1,
					'history' => $cnt2,
					'engine' => $cnt3,
				);
				$can_destory = !!$cnt3;
			} else {
				$result['reason'] = '当前栏目下尚有知识数据 '.$data_exists.' 条 ，请先处理。';
			}			
		}

		// 针对问答栏目的管理逻辑
		// 暂时直接删除栏目，不处理栏目中包含的问答数据
		if ( $opts['type']=='qa' ) {
			$this->ajax_error('针对问答栏目的删除功能已被禁用! 如有特别需求，请与 #凌雷 联系');
			$can_destory = true;
		}

		if ( $can_destory !== false ) {
			// 4. 栏目信息删除
			$_path = $cate['path'];
			// 删除栏目及子栏目
			$where = array(
				'type' => $cate['type'],
				'path' => array(
					array('like', "{$_path}"),
					array('like', "{$_path}-%"),
					'OR',
				),
			);

			if ( $mCategories->where($where)->delete() ) {
				// 清理栏目树缓存
				// 4. 清理缓存
				$lCate = D('Cate','Logic','Common');
				$lCate->delTreeNode($parent, $id, $opts['type']);
				$result['status'] = true;
				$result['reason'] = '操作成功';

				// 记录审计操作日志
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));

				$this->ajax_return($result);
			} else {
				$error_msg = '系统错误, 操作异常';
				// 修改栏目操作异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$result['reason'] = $error_msg;
			}
		}

		$this->ajax_return($result);
	}


	/**
	 * 栏目异步读取接口
	 * @@action - ajax
	 */
	public function getcates() {
		$id = I('get.id', 0 , 'intval');
		$all = I('get.all', 0, 'intval');
		$type = I('get.type', 'kb', 'strtolower,trim');
		if ( !array_key_exists($type, $this->type_mapping) ) {
			$this->ajax_error('没有指定的业务类型!');
		}

		if ($id < 0) {
			$this->ajax_error('请指定栏目编号!');
		}

		$params = array();
		$lCate = D('Cate','Logic','Common');
		if ( $all===1 ) {
			$params = $lCate->getIndexTopCategories($type);
		} else {
			$list = $lCate->getCateListById($id, $type);
			if ( $list ) {
				foreach ($list as $key => $name) {
					$params[$key]['id'] = $key;
					$params[$key]['name'] = $name;
				}
			}
		}
		if ( $params ) {
			$result = array(
				'status' => true,
				'reason' => '获取成功',
				'list' => array_values($params),
			);
			$this->ajax_return($result);
		} else {
			$this->ajax_error('栏目数据获取失败!');
		}
	}

	/**
	 * 重新栏目树
	 * @@action - ajax
	 */
	public function rebuildtree() {
		$type = I('get.type', 'kb', 'trim,strtolower');
		$types = array('kb', 'qa');
		if ( !array_key_exists($type, $this->type_mapping) ) {
			$this->ajax_error('没有指定的业务类型!');
		}
		$lCate = D('Cate','Logic','Common');
		$lCate->init(1, $type);
		$l = $lCate->toTree(0, $type);
		$result = array(
			'status' => true,
			'reason' => '栏目树数据缓存重建成功!',
			'list' => $l,
		);
		$this->ajax_return($result);
	}
}
