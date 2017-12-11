<?php
/**
 * 权限逻辑
 */
namespace admin\Logic;

class RoleLogic {

	public function getRoleList()
	{
		$r = S(C('REDIS'));
		$role = $r->get('admin:role:list');
		if(empty($role))
		{
			$role = array();
			$mR = D('roles', 'Model', 'Common');
			$result = $mR->select();
			foreach($result as $v)
			{
				$role[$v['id']] = $v;
			}

			$r->set('admin:role:list', $role, 86400);
		}
		return $role;
	}

	public function flushRoleList()
	{
		$r = S(C('REDIS'));
		$r->rm('admin:role:list');

		$role = array();
		$mR = D('roles', 'Model', 'Common');
		$result = $mR->select();
		foreach($result as $v)
		{
			$role[$v['id']] = $v;
		}

		$r->set('admin:role:list', $role, 86400);
	}
}