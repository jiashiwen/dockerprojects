<?php
namespace baike\Model;
use Think\Model;

define('OPLOG_ATTENTION', 11);		// 关注问题
// define('OPLOG_UNATTENTION', 12);	// 取消关注问题
define('OPLOG_BEST_REPLY', 21);		// 最佳回复
define('OPLOG_GOOD_REPLY', 31);		// 好评
define('OPLOG_BAD_REPLY', 32);		// 差评

class OplogsModel extends Model {

	protected function _parseWhere ( $act=false, $uid=false, $relid=false ) {
		$where = array();
		if ( $uid ) {
			$where[] = "`uid`='{$uid}'";
		}
		if ( $act ) {
			$where[] = "`act`='{$act}'";
		}
		if ( $relid ) {
			$where[] = "`relid`='{$relid}'";
		}
		$where = implode(' AND ', $where);
		return $where;
	}

	public function countLog($act=false, $uid=false, $relid=false) {
		$this->_parseWhere($act, $uid, $relid);
		$count = $this->where($where)->count();
		return $count;
	}

	public function existsLog($act=false, $uid=false, $relid=false) {
		$this->_parseWhere($act, $uid, $relid);
		$log = $this->where($where)->find();
		return $log;
	}

	public function addLog($act, $uid, $relid) {

		$log = $this->existsLog($act, $uid, $relid);
		$exists = !!$log ? true : false;
		if ( $exists ) {
			// 已经做过此操作日志
			return false;
		}

		$data = array(
			'act' => $act,
			'uid' => $uid,
			'relid' => $relid,
			'ctime' => NOW_TIME,
		);
		$id = $this->data($data)->add();
		var_dump($act, $uid, $relid, $id);
		return $id>0 ? true : false;
	}

	public function delLog($act, $uid, $relid) {

		$log = $this->existsLog($act, $uid, $relid);
		$exists = !!$log ? true : false;
		if ( $exists ) {
			$where = "`id`='".$log['id']."'";
			$ret = $this->where($where)->limit(1)->delete();
			// 已经做过此操作日志
		}

		var_dump($act, $uid, $relid);
		return true;
	}



}
