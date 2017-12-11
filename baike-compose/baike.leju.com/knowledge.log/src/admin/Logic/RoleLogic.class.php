<?php
/**
 * 权限逻辑
 */
namespace admin\Logic;

class RoleLogic {
	protected $cacher = null;
	protected $key = '';

	public function __construct() {
		$this->cacher = S(C('REDIS'));
		$this->key = C('CACHER_KEYS.ADMIN_ROLE_LIST');
	}

	/**
	 * 读取用户角色列表
	 */
	public function getRoleList() {
		$role = $this->cacher->Get($this->key);
		if( empty($role) ) {
			$role = array();
			$mR = D('roles', 'Model', 'Common');
			$result = $mR->select();
			foreach($result as $v)
			{
				$role[$v['id']] = $v;
			}

			$this->cacher->Set($this->key, $role, 86400);
		}
		return $role;
	}

	/**
	 * 重建系统角色列表缓存
	 */
	public function flushRoleList() {
		$this->cacher->Del($this->key);

		$role = array();
		$mR = D('roles', 'Model', 'Common');
		$result = $mR->select();
		foreach($result as $v)
		{
			$role[$v['id']] = $v;
		}

		$r->Set($this->key, $role, 86400);
	}
}