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

	public function todayClickByCity($begin,$end,$limit,$city)
	{
		$sql = "SELECT relid,COUNT(relid) AS total FROM `visit_stats` WHERE `reltype` = 'kb' AND city='{$city}' AND `ctime` BETWEEN $begin AND $end GROUP BY relid ORDER BY total DESC LIMIT {$limit}";
		$list = $this->query($sql);
		return $list;
	}

	/**
	 * 获取 百科词条 的 热词排行
	 * @param $num int 指定返回词条数量，默认返回 10 条
	 * @param $cycle int 指定查询周期天数，默认统计 7 天，即近 1 周内的热词
	 * @param $endtime int 指定查询数据的最后时间，时间戳，默认以今天凌晨00:00:00结束
	 */
	public function statsWikiHotRank( $num=10, $cycle=7, $endtime=0 ) {
		$result = [];
		$num = intval($num);
		// 指定返回的条数如果小于等于0，默认返回空
		if ( $num <=0 ) {
			return $result;
		}
		$cycle = intval($cycle);
		// 指定查询天数如果小于等于0，默认返回空
		if ( $cycle <= 0 ) {
			return $result;
		}
		$cycle = $cycle * 86400;

		$endtime = intval($endtime);
		if ( $endtime <= 0 ) {
			$endtime = strtotime('today 00:00:00');
		}
		$starttime = $endtime - $cycle;
		// 计算查询开始时间如果小于等于0，默认返回空
		if ( $starttime <= 0 ) {
			return $result;
		}

		$sql = "select w.id, w.title, w.cateid, t.cnt from ( select relid, count(id) as cnt from visit_stats where reltype='wiki' and ctime between '{$starttime}' and '{$endtime}' group by relid ) t left join wiki w on t.relid=w.id order by t.cnt desc, w.id desc limit {$num}";
		$result = $this->query($sql);
		return $result;
	}

	/**
	 * 获取 百科词条 的 七天热度趋势
	 * @param $cycle int 指定查询周期天数，默认统计 7 天，即近 1 周内的热词
	 * @param $endtime int 指定查询数据的最后时间，时间戳，默认以今天凌晨00:00:00结束
	 * @return [{day:'m-d', total:int}]
	 */
	public function statsWikiHotTrend( $cycle=7, $endtime=0 ) {
		$result = [];
		$cycle = intval($cycle);
		// 指定查询天数如果小于等于0，默认返回空
		if ( $cycle <= 0 ) {
			return $result;
		}
		$_cycle = $cycle * 86400;

		$endtime = intval($endtime);
		if ( $endtime <= 0 ) {
			$endtime = strtotime('today 00:00:00');
		}
		$starttime = $endtime - $_cycle;
		// 计算查询开始时间如果小于等于0，默认返回空
		if ( $starttime <= 0 ) {
			return $result;
		}

		$sql = [];
		for ( $i=$cycle; $i>0; $i-- ) {
			$_sp = $endtime - ($i * 86400);
			$_ep = $_sp + 86400;
			$day = date('m-d', $_sp);
			array_push($sql, "select '{$day}' as 'day', count(id) as 'total' from visit_stats where reltype='wiki' and ctime between '{$_sp}' and '{$_ep}'");
		}
		$sql = implode(" union all ", $sql);
		$result = $this->query($sql);
		return $result;
	}

	/**
	 * 获取 百科词条 的 七天更新趋势
	 * @param $cycle int 指定查询周期天数，默认统计 7 天，即近 1 周内的热词
	 * @param $endtime int 指定查询数据的最后时间，时间戳，默认以今天凌晨00:00:00结束
	 * @return [{day:'m-d', total:int}]
	 */
	public function statsWikiUpdate( $cycle=7, $endtime=0 ) {
		$result = [];
		$cycle = intval($cycle);
		// 指定查询天数如果小于等于0，默认返回空
		if ( $cycle <= 0 ) {
			return $result;
		}
		$_cycle = $cycle * 86400;

		$endtime = intval($endtime);
		if ( $endtime <= 0 ) {
			$endtime = strtotime('today 00:00:00');
		}
		$starttime = $endtime - $_cycle;
		// 计算查询开始时间如果小于等于0，默认返回空
		if ( $starttime <= 0 ) {
			return $result;
		}

		$sql = [];
		for ( $i=$cycle; $i>0; $i-- ) {
			$_sp = $endtime - ($i * 86400);
			$_ep = $_sp + 86400;
			$day = date('m-d', $_sp);
			array_push($sql, "select '{$day}' as 'day', count(id) as 'total' from wiki where `status`='9' and ctime between '{$_sp}' and '{$_ep}'");
		}
		$sql = implode(" union all ", $sql);
		$result = $this->query($sql);
		return $result;
	}
}
