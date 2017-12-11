<?php
/**
 * 访问统计
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;

class VisitStatsModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'visit_stats';
	// 允许统计的数据类型 对应数据表的 enum 类型定义
	protected $reltypes = array('kb', 'qa', 'wiki');

	/**
	 * 添加访问统计日志
	 * @param $relid int 被访问的数据编号
	 * @param $relcateid string 被访问的数据分类路径
	 * @param $reltype string 被访问的数据所在的子系统
	 * @param $uid int 访问者用户编号，0为游客
	 * @param mixed 成功返回日志 id 编号，失败返回 false
	 */
	public function insertLog( $relid, $relcateid='0-', $reltype='kb', $uid=0 ) {
		if ( !in_array($reltype, $this->reltypes) ) {
			return false;
		}

		$data = array(
			'uid' => $uid,
			'reltype' => $reltype,
			'relid' => $relid,
			'relcateid' => $relcateid,
			'ctime' => NOW_TIME,
		);

		return $this->add($data);
	}

	public function todayHot()
	{
		$condition['reltype'] = 'kb';
		$condition['ctime'] = array('between',"{getDayTime()['begin']},{getDayTime()['end']}");
		return $this->where($condition)->count();
	}

	public function todayCate()
	{
		$getDayTime = getDayTime();
		$flag = '0-1-';//需要指定知识栏目一级ID
		$condition['reltype'] = 'kb';
		$condition['ctime'] = array('between',"{$getDayTime['begin']},{$getDayTime['end']}");
		$where['relcateid'] = array('like', "%{$flag}%");
		return $this->where($condition)->count();
	}

	public function todayClick($begin,$end,$limit)
	{
		$sql = "SELECT relid,COUNT(relid) AS total FROM `visit_stats` WHERE `reltype` = 'kb' AND `ctime` BETWEEN $begin AND $end GROUP BY relid ORDER BY total DESC LIMIT {$limit}";
		$list = $this->query($sql);
		return $list;
	}
}
