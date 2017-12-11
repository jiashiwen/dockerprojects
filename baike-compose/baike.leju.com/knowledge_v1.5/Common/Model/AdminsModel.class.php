<?php
namespace Common\Model;
use Think\Model;

class AdminsModel extends Model {
	protected $trueTableName = 'admins';

	/**
	 * 以关键字查询用户
	 */
	public function suggest ( $prefix='', $num=5 ) {
		if ( $prefix == '' ) {
			return false;
		}
		// 获取字符串中的汉字 @ /configs/Common/function.php
		$prefix = fetchChinese($prefix);
		// 在用户名与真实姓名中以前缀匹配方式查询用户
		$where = array(
			'username' => array('like', "{$prefix}%"),
			'realname' => array('like', "{$prefix}%"),
			'_logic' => 'or',
		);
		$list = $this->where($where)->limit($num)->select();
		return $list;
	}
}
