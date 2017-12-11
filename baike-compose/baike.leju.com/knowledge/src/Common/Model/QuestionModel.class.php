<?php
/**
 * 问答系统 问题数据模型类
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;

class QuestionModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'question';




	public function countAll() {
		$result = array(
			'all' => $this->count_all(),	// 所有问题
			'unsolved' => $this->count_unsolved(), // 未解决问题
			'answered' => $this->count_answered(),	// 已回复问题
			'best' => $this->count_best(),	// 已采纳问题
			'unverified' => $this->count_unverified(),	// 未审核问题
			'lastweek' => $this->count_lastweek(),	// 近7天新增问题
		);
		return $result;
	}
	public function count_all() {
		$where = array('id' => array('gt', 0));
		$result = $this->where($where)->count();
		return $result;
	}
	public function count_unsolved() {
		$where = array('id' => array('gt', 0), 'status'=>'21');
		$result = $this->where($where)->count();
		return $result;
	}
	public function count_answered() {
		$where = array('id' => array('gt', 0), 'status'=>'22');
		$result = $this->where($where)->count();
		return $result;
	}
	public function count_best() {
		$where = array('id' => array('gt', 0), 'status'=>'23');
		$result = $this->where($where)->count();
		return $result;
	}
	public function count_unverified() {
		$where = array('id' => array('gt', 0), 'status'=>'12');
		$result = $this->where($where)->count();
		return $result;
	}
	public function count_lastweek() {
		$today_zero = strtotime(date('Y-m-d 00:00:00', NOW_TIME));
		$range = array(
			'start' => strtotime('-7 days', $today_zero),
			'end' => $today_zero,
		);
		$where = array(
			'id' => array('gt', 0),
			'ctime' => array('between', array($range['start'], $range['end'])),
		);
		$result = $this->where($where)->count();
		return $result;
	}
}
