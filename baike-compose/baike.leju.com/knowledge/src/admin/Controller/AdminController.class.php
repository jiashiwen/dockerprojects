<?php
/**
 * 管理员控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class AdminController extends BaseController {
	/**
	 * 系统管理用户管理列表
	 */
	public function index(){
		//权限验证
		$this->checkAuthorization('role/userlist');

		$pagesize = 20;
		$page = I('get.page', 1, 'intval');
		$page = $page < 1 ? 1 : $page;

		$mail = I('get.mail', '', 'trim,strtolower');
		$role_id = I('get.role_id', 0, 'intval');
		$truename = I('get.truename', '', 'strip_tags');
		$ltime = I('get.ltime', '', 'strip_tags');
		// $ltime_s = I('get.ltime_s', '', 'strip_tags');
		// $ltime_e = I('get.ltime_e', '', 'strip_tags');
		$ltime_s = vaild_datestr($ltime, 'Y-m-d');
		$ltime_e = vaild_datestr($ltime_s, 'Y-m-d 23:59:59');
		$city = I('get.city', '', 'strip_tags');

		// 城市列表
		$cities = C('CITIES');
		$cities['ALL']['_'] = array(
			'en' => '_',
			'cn' => '全国',
			'py' => 'quanguo',
		);
		ksort($cities['ALL']);
		$this->assign('cities', $cities);

		// @TODO 可优化
		// @TODO: 1. 搜索用户名时，可优化为前缀匹配
		// @TODO: 2. 查询性能优化，拆联合查询为独立的子查询
		//联合查询
		$mA = D('admins', 'Model', 'Common');
		$mR = D('roles', 'Model', 'Common');

		// 优化
		$where = array();
		$mail && $where['em_email'] = array('like', "{$mail}%");
		$role_id && $where['role_id'] = $role_id;
		$truename && $where['truename'] = array('like', "{$truename}%");
		$city && $where['scope'] = $city;
		($ltime_s && !$ltime_e) && $where['login_time'] = array('EGT', strtotime($ltime_s));
		(!$ltime_s && $ltime_e) && $where['login_time'] = array('ELT',strtotime($ltime_e));
		($ltime_s && $ltime_e) && $where['login_time'] = array('BETWEEN',array(strtotime($ltime_s),strtotime($ltime_e)));
		$total = $mA->where($where)->count();
		$list = $mA->field('*')->where($where)->order('status desc, login_time desc')->page($page, $pagesize)->select();
		// var_dump($total, $where, $list, $mA->getLastSql());

		//分页模块
		if ( $total ) {
			//封装linkopts
			$linkopts = array();
			$mail && array_push($linkopts, "mail={$mail}");
			$role_id && array_push($linkopts, "role_id={$role_id}");
			$truename && array_push($linkopts, "truename={$truename}");
			$ltime_s && array_push($linkopts, "ltime_s={$ltime_s}");
			$ltime_e && array_push($linkopts, "ltime_e={$ltime_e}");
			$city && array_push($linkopts, "city={$city}");
			$linkstring = !empty($linkopts) ? '/Admin/?page=#&'.implode('&',$linkopts) : '/Admin/?page=#';
			$opts = array(
				'first' => true, //首页
				'last' => true,	//尾页
				'prev' => true, //上一页
				'next' => true, //下一页
				'number' => 5, //显示页码数
				'linkstring' => $linkstring
			);
			$pager = pager($page, $total, $pagesize, $opts);
			$this->assign('pager', $pager);
		}
		// 用户列表
		$this->assign('users', $list);
		// 角色
		$_roles = $mR->select();
		$roles = array();
		foreach ( $_roles as $i => $role ) {
			$roles[$role['id']] = $role;
		}
		unset($_roles);
		$this->assign('roles', $roles);
		// 筛选参数
		$this->assign('role_id', $role_id);
		$this->assign('city',$city);
		$this->assign('mail',$mail);
		$this->assign('truename',$truename);
		$this->assign('ltime',$ltime_s);
		// $this->assign('ltime_s',$ltime_s);
		// $this->assign('ltime_e',$ltime_e);

		$this->display();
	}

	/**
	 * 删除用户
	 * @return ajax - json
	 */
	public function del() {
		//权限验证
		$this->checkAuthorization('role/userdel','ajax');

		$uid = I('post.uid',0,'intval');
		if ( $uid == 0 ) {
			$this->ajax_error('请指定要删除的管理员用户编号');
		}

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'userid' => intval($uid),			// 被操作管理员id
		);
		$log_actid = ADMIN_REMOVE_ACT;

		$mAdmins = D('admins', 'Model', 'Common');
		$admin = $mAdmins
				 ->field('id, truename, status')
				 ->where(array('id'=>$uid))
				 ->find();
		if ( $admin ) {
			$status = intval($admin['status']);
			// 被操作管理员
			$log_note['user'] = trim($admin['truename']);
			if ( $status==0 ) {
				$error_msg = $admin['nickname'].'('.$uid.')的用户已被删除，请不要重复删除';
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));

				$this->ajax_error($error_msg);
			}
			// 更新指定管理员数据，将状态设置为已删除 status:=0
			$ret = $mAdmins
					->where(array('id'=>$uid))
					->data(array('status'=>0))
					->save();
			if ( $ret ) {
				$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));

				$result = array('status'=>true, 'info'=>'删除成功');
				$this->ajax_return($result);
			} else {
				$error_msg = $admin['nickname'].'('.$uid.')的用户删除操作失败';
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));

				$this->ajax_error($error_msg);
			}
		} else {
			$error_msg = '您要删除的管理员不存在，请指定有效管理员帐号';
			// 删除异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));
			// 没有指定的管理员帐号，显示异常
			$this->ajax_error($error_msg);
		}
	}

	/**
	 * 恢复用户
	 * @return ajax - json
	 */
	public function undel(){
		//权限验证
		$this->checkAuthorization('role/userdel','ajax');

		$uid = I('post.uid',0,'intval');
		if ( $uid == 0 ) {
			$this->ajax_error('请指定要恢复的被删除管理员用户编号');
		}

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'userid' => intval($uid),			// 被操作管理员id
		);
		$log_actid = ADMIN_RESTORE_ACT;
		$mAdmins = D('admins', 'Model', 'Common');
		$admin = $mAdmins
				 ->field('id, truename, status')
				 ->where(array('id'=>$uid))
				 ->find();
		if ( $admin ) {
			$status = intval($admin['status']);
			// 被操作管理员
			$log_note['user'] = trim($admin['truename']);
			if ( $status==1 ) {
				$error_msg = $admin['nickname'].'('.$uid.')的用户未被删除，不需要恢复';
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));

				$this->ajax_error($error_msg);
			}
			// 更新指定管理员数据，将状态设置为正常 status:=1
			$ret = $mAdmins
					->where(array('id'=>$uid))
					->data(array('status'=>1))
					->save();
			if ( $ret ) {
				$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));

				$result = array('status'=>true, 'info'=>'恢复成功');
				$this->ajax_return($result);
			} else {
				$error_msg = $admin['nickname'].'('.$uid.')的用户恢复操作失败';
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));

				$this->ajax_error($error_msg);
			}
		} else {
			$error_msg = '您要恢复的管理员不存在，请指定有效管理员帐号';
			// 删除异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));
			// 没有指定的管理员帐号，显示异常
			$this->ajax_error($error_msg);
		}			
	}

	/**
	 * 更改用户组
	 * @return ajax - json
	 */
	public function change_role(){
		//权限验证
		$this->checkAuthorization('role/useredit','ajax');

		$id = I('post.id',0,'intval');
		$uid = I('post.uid',0,'intval');
		if ( $id == 0 ) {
			$this->ajax_error('请指定要变更的角色编号');
		}
		if ( $uid == 0 ) {
			$this->ajax_error('请指定要恢复的被删除管理员用户编号');
		}

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'userid' => intval($uid),			// 被操作管理员id
			'roleid' => intval($id),			// 变更后的角色id
		);
		$log_actid = ADMIN_ROLE_ACT;

		// 读取被操作的管理员数据
		$mAdmins = D('admins', 'Model', 'Common');
		$where = array('id'=>$uid);
		$admin = $mAdmins->where($where)->find();
		if ( !$admin ) {
			$error_msg = '您要操作的管理员不存在，请指定有效管理员帐号';
			// 删除异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));
			// 没有指定的管理员帐号，显示异常
			$this->ajax_error($error_msg);
		}

		// 被操作管理员
		$log_note['user'] = trim($admin['truename']);

		$default_role = array( 'id' => 0, 'name' => '未分配角色', );

		// 读取被操作的管理员的角色信息
		$mRoles = D('roles', 'Model', 'Common');
		if ( $id > 0 ) {
			$role_info = $mRoles->where(array('id'=>$id))->find();
			if ( !$role_info ) {
				$error_msg = '您指定的角色不存在，请指定有效角色编号';
				// 删除异常
				$log_note['status'] = false;
				$log_note['reason'] = $error_msg;
				$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));
				// 没有指定的管理员帐号，显示异常
				$this->ajax_error($error_msg);
			}
		} else {
			// 指定管理员的新角色为没有角色，即为清除管理员的角色设置
			$role_info = $default_role;
		}

		// 被操作管理的新角色名称
		$log_note['rolename'] = $role_info['name'];
		// 通过管理员获取原来的角色信息
		if ( intval($admin['role_id'])>0 ) {
			$old_role_info = $mRoles->where(array('id'=>$admin['role_id']))->find();
			if ( !$old_role_info ) {
				$old_role_info = $default_role;
			}
		} else {
			$old_role_info = $default_role;
		}
		// 原来的角色设置
		$log_note['oldroleid'] = intval($admin['role_id']);
		$log_note['oldrolename'] = $old_role_info['name'];

		// 更新管理员角色数据
		$ret = $mAdmins->where(array('id'=>$uid))->data(array('role_id'=>$id))->save();
		if ( $ret ) {
			// 发送角色信息变更的消息通知
			$to = $admin['em_email'];
			$title = '[乐居房产百科管理系统]您的可操作的权限有更新';
			$content = '乐居房产百科管理系统中，您的用户角色由 '.$old_role_info['name'].' 更新为 '.$role_info['name'].'，请您知晓。';
			$mail_info = array(
				'title' => $title,
				'truename' => $admin['truename'],
				'content' => $content,
				'datetime' => date('Y年m月d日'),
			);
			layout(false);
			$this->assign('mail', $mail_info);
			$html = $this->fetch('Public:mail.notice');

			$lEms = D('Ems', 'Logic', 'Common');
			$ret = $lEms->sendMail($to, $title, $html);

			// 被操作管理的新角色名称
			$log_note['rolename'] = $role_info['name'];
			$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));
			$result = array('status'=>true, 'info'=>'角色成功变更为'.$role_info['name']);
			$this->ajax_return($result);
		} else {
			$error_msg = '您指定的管理员角色变更操作失败';
			// 删除异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$log_note['rolename'] = $role_info['name'];
			$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));
			$this->ajax_error($error_msg);
		}
	}

	/**
	 * 更改用户城市权限
	 * @param uid int 用户编号
	 * @param scope string 城市代码 city_en
	 * @return ajax - json
	 */
	public function change_city() {
		//权限验证
		$this->checkAuthorization('role/useredit','ajax');

		$scope = I('post.scope','','trim,strtolower');
		$uid = I('post.uid',0,'intval');

		$cities = C('CITIES.ALL');
		$cities['_'] = array(
			'en' => '_',
			'cn' => '全国',
			'py' => 'quanguo',
		);
		if ( !array_key_exists($scope, $cities) ) {
			$this->ajax_error('城市不存在！');
		}

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'userid' => intval($uid),			// 被操作管理员id
			'city' => $scope,			// 变更后的城市代码
			'cityname' => $cities[$scope]['cn'],			// 变更后的城市名称
		);
		$log_actid = ADMIN_CITY_ACT;

		$mAdmins = D('admins', 'Model', 'Common');
		$where = array('id'=>$uid);
		$admin = $mAdmins->where($where)->find();
		if ( $admin ) {
			// 被操作管理的新角色名称
			$log_note['user'] = $admin['truename'];

			$old_scope = array_key_exists($admin['scope'], $cities) ? $cities[$admin['scope']]['cn'] : '无城市权限';
			// 原来的城市权限设置
			$log_note['oldcity'] = $admin['scope'];
			$log_note['oldcityname'] = $old_scope;

			// 更新城市权限配置
			$ret = $mAdmins->where($where)->data(array('scope'=>$scope))->save();
			if( $ret ) {
				// 发送通知邮件
				$to = $admin['em_email'];
				$title = '[乐居房产百科管理系统]您的可操作城市权限有更新';
				$content = '乐居房产百科管理系统中，您的工作城市由 '.$old_scope.' 更新为 '.$cities[$scope]['cn'].'，请您知晓。';
				$mail_info = array(
					'title' => $title,
					'truename' => $admin['truename'],
					'content' => $content,
					'datetime' => date('Y年m月d日'),
				);
				layout(false);
				$this->assign('mail', $mail_info);
				$html = $this->fetch('Public:mail.notice');

				$lEms = D('Ems', 'Logic', 'Common');
				$ret = $lEms->sendMail($to, $title, $html);

				// 保存审计操作日志
				$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));

				$result = array('status'=>true, 'info'=>'用户城市权限成功更新为'. $cities[$scope]['cn']);
				$this->ajax_return($result);
			} else {
				$error_msg = '用户城市权限不需要更新';
				// // 删除异常
				// $log_note['status'] = false;
				// $log_note['reason'] = $error_msg;
				// $this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));
				$this->ajax_error($error_msg);
			}
		} else {
			$error_msg = '指定的操作用户不存在';
			// 删除异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));

			$this->ajax_error($error_msg);
		}
	}
}