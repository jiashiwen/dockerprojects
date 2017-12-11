<?php
/**
 * 用户模型
 *
 */
namespace baike\Model;
use Think\Model;

class UsersModel extends Model {

	protected $userlist = array();

	/**
	 * 按用户编号数据，获取用户列表信息
	 * @param $userids array 用户id列表
	 */
	public function getUsers($userids=array()) {
		foreach ( $userids as $i => $uid ) {
			$uid = intval($uid);
			// 异常，用户编号必须是大于或等于0的数值 0表示为游客
			if ( $uid < 0 ) {
				continue;
			}
			// 如果用户已经在缓存列表中，忽略
			if ( array_key_exists(0, $this->userlist) ) {
				continue;
			}

			if ( intval($uid) == 0 ) {
				$userlist[0] = C('DICT.guest');
			} else {
				$userlist[$uid] = $this->getUser($uid);
			}
		}

		return $this->userlist;
	}

}
