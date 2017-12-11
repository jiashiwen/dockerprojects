<?php
/**
 * 搜索统计
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;

class SearchStatsModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'search_stats';
	// 允许统计的数据类型 对应数据表的 enum 类型定义
	protected $reltypes = array('_', 'kb', 'qa', 'wiki');

	/**
	 * 添加搜索统计日志
	 * @param $keyword int 被访问的数据编号
	 * @param $reltype string 被访问的数据所在的子系统
	 * @param $source string 搜索来源 0通用入口 11移动 12PC
	 * @param $uid int 访问者用户编号，0为游客
	 * @param mixed 成功返回日志 id 编号，失败返回 false
	 */
	public function insertLog( $keyword, $reltype='_', $source=0, $uid=0 ) {
		if ( !in_array($reltype, $this->reltypes) ) {
			return false;
		}

		if ( $keyword==='' ) {
			return false;
		}

		$data = array(
			'uid' => $uid,
			'reltype' => $reltype,
			'source' => $source,
			'keyword' => $keyword,
			'ctime' => NOW_TIME,
		);

		return $this->add($data);
	}

}
