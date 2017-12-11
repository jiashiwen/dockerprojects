<?php
/**
 * 角色控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class RolesController extends BaseController {
	/**
	 * 系统权限角色管理列表
	 */
	public function index(){
		//权限验证
		$this->checkAuthorization('role/list');

		$mA = D('admins', 'Model', 'Common');
		$mR = D('roles', 'Model', 'Common');
		$list = $mR->select();
		foreach($list as $k=>$v)
		{
			$list[$k]['icount'] = $mA->where(array('role_id'=>$v['id']))->count();
		}
		$this->assign('list',$list);
		$this->display();
	}

	/**
	 * 添加系统角色
	 */
	public function add(){
		//权限验证
		$this->checkAuthorization('role/add');

		$pageinfo = array(
			'crumb' => array(),
			'title' => '创建系统权限角色管理',
		);
		$this->assign('pageinfo', $pageinfo);

		if ( IS_POST ) {
			$id = 0;
			$data = I('post.');

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
			);
			$log_actid = ROLE_ADD_ACT;

			$ret = $this->save($id, $data);
			if ( $ret === true ) {
				$log_note['roleid'] = $this->_role_info['id'];
				$log_note['rolename'] = $this->_role_info['title'];
				$this->_logger->addLog($this->_user['id'], $log_actid, 0, json_encode($log_note));
				$result= array('status'=>true, 'info'=>'角色权限创建成功');
				$this->ajax_return($result);
			} else {
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $this->error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, 0, json_encode($log_note));
				$this->ajax_error($this->error_msg);
			}
		} else {
			$this->editor(0);
		}
	}

	/**
	 * 编辑系统角色
	 */
	public function edit(){
		//权限验证
		$this->checkAuthorization('role/edit');

		$pageinfo = array(
			'crumb' => array(),
			'title' => '编辑角色权限信息',
		);
		$this->assign('pageinfo', $pageinfo);

		$mR = D('roles', 'Model', 'Common');
		if ( IS_POST ) {
			$id = I('post.id',0,'intval');
			$data = I('post.');

			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
			);
			$log_actid = ROLE_MOD_ACT;

			$ret = $this->save($id, $data);
			if ( $ret === true ) {
				$log_note['roleid'] = $this->_role_info['id'];
				$log_note['rolename'] = $this->_role_info['title'];
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$result= array('status'=>true, 'info'=>'角色权限更新成功');
				$this->ajax_return($result);
			} else {
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $this->error_msg;
				$log_note['roleid'] = $id;
				if ( isset($this->_role_info['title']) ) {
					$log_note['rolename'] = $this->_role_info['title'];
				}
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
				$this->ajax_error($this->error_msg);
			}
		} else {
			$id = I('get.id', 0, 'intval');
			$this->editor($id);
		}
	}


	/**
	 * 提交之后的保存逻辑
	 * REQUEST METHOD : POST
	 * @description
	 * <创建> => <保存> : 创建
	 * <编辑> => <保存> : 更新
	 */
	protected function save ( $id=0, $data=array() ) {
		$result = false;
		if ( empty($data) ) {
			$this->error_msg = '没有表单数据';
			return $result;
		}

		$mRoles = D('Roles', 'Model', 'Common');

		// 判断角色名称是否重复
		$data['name'] = trim($data['title']);

		// 当 id 不为 0 时表示更新
		if ( $id > 0 ) {
			$role_info = $mRoles->where(array('id'=>$id))->find();
			if ( empty($role_info) ) {
				$this->error_msg = '没有指定要编辑的角色';
				return $result;
			}
			$exists = $mRoles->where(array('id'=>array('neq', $id), 'name'=>$data['title']))->count();
			if ( intval($exists) > 0 ) {
				$this->error_msg = '修改的名称为 '.$data['title'].'，同名角色已经存在，请不要重复命名';
				return $result;
			}
		} else {
			$exists = $mRoles->where(array('name'=>$data['title']))->count();
			if ( intval($exists) > 0 ) {
				$this->error_msg = '名为 '.$data['title'].' 的角色已经存在，请不要重复创建';
				return $result;
			}
		}

		// 处理 auth 集合
		$authorities = $data['authorities'] = array();
		// auth 为表单数据
		foreach ( $data['auth'] as $v ) {
			if(stripos($v, ':') !== false) {
				$t = explode(':', $v);
				$authorities[$t[0]][] = $t[1];
			} else {
				$authorities[$v] = NULL;
			}
		}
		unset($data['auth']);
		$authorities && $data['authorities'] = json_encode($authorities);

		$ret = false;
		$this->_role_info = $data;
		if ( $id > 0 ) {
			$ret = $mRoles->where(array('id'=>$id))->data($data)->filter('strip_tags')->save();
			$this->_role_info['id'] = $id;
		} else {
			$ret = $mRoles->data($data)->filter('strip_tags')->add();
			$this->_role_info['id'] = $ret;
		}
		// var_dump($id, $ret);
		// echo $mRoles->getLastSql();
		if ( $ret ) {
			$lRole = D('Role', 'Logic', 'admin');
			$lRole->flushRoleList();
			$result = true;
		} else {
			$this->error_msg = '操作失败';
		}

		return $result;
	}

	/**
	 * 显示角色编辑表单
	 * REQUEST METHOD : GET
	 */
	protected function editor ( $id=0 ) {
		// 从配置信息中读取系统权限列表
		$auth = C('AUTH_ROUTE');
		// var_dump($auth);
		$this->assign('auth', $auth);

		//一级栏目列表
		$mCategories = D('Categories', 'Model', 'Common');
		$knowledge_cates = $mCategories->getCateList(0, 'kb');
		$question_cates = $mCategories->getCateList(0, 'qa');
		$this->assign('kbcates', $knowledge_cates);
		$this->assign('qacates', $question_cates);

		// 如果 id > 0 时，表示编辑一个已有的角色设置
		if ( $id > 0 ) {
			$mRoles = D('Roles', 'Model', 'Common');
			$role_info = $mRoles->where(array('id'=>$id))->find();
			if ( empty($role_info) ) {
				redirect('/Roles/Add');
			}

			// 渲染权限
			$authorities = array();
			$auth = json_decode($role_info['authorities'], true);
			foreach ( $auth as $k => $v ) {
				if ( is_array($v) && !empty($v) ) {
					foreach($v as $b) {
						array_push($authorities, $k.':'.$b);
					}
				} else {
					array_push($authorities, $k);
				}
			}

			$this->assign('id', $id);
			$this->assign('authorities',$authorities);
			$this->assign('role_info', $role_info);
		}
		// var_dump($id, $authorities, $role_info, $knowledge_cates, $question_cates);

		$this->display('editor');
	}

	/**
	 * 复用的接口
	 * 1. 管理员管理列表中，使用
	 * 2. 角色创建或编辑表单中，使用(需废弃)
	 */
	public function ajax_get_cities() {
		//城市列表
		$result = array();
		$cities = C('CITIES');

		array_push($result, array('value'=>'全国','cid'=>'_'));

		foreach ( $cities['ALL'] as $k => $v ) {
			array_push($result, array('value'=>$v['cn'],'cid'=>$k));
		}

		$result = array('status'=>true, 'info'=>$result);
		$this->ajax_return($result);
	}


	/**
	 * 删除指定角色
	 * 条件：角色组内无任何用户关联
	 */
	public function remove() {
		//权限验证
		$this->checkAuthorization('role/del');
		$id = I('get.id', 0, 'intval');
		if ( $id==0 ) {
			$this->ajax_error('请指定要删除的角色编号');
		}

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'roleid' => $id,
		);
		$log_actid = ROLE_DEL_ACT;

		$mRoles = D('Roles', 'Model', 'Common');
		$role_info = $mRoles->where(array('id'=>$id))->find();
		if ( $role_info ) {
			$role_relcount = D('Admins', 'Model', 'Common')
				->where(array('role_id'=>$id))
				->count();
			if ( $role_relcount==0 ) {
				$ret = $mRoles->where(array('id'=>$id))->delete();
				$result = array('status'=>true, 'info'=>$ret);
				$this->ajax_return($result);
			} else {
				$error_msg = '待删除的角色不存在';
			}
		} else {
			$error_msg = '还有属于这个角色的管理员，不能删除';
		}
		// 删除异常
		$log_note['status'] = false;
		$log_note['reason'] = $error_msg;
		$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
		$this->ajax_error($error_msg);
	}
}