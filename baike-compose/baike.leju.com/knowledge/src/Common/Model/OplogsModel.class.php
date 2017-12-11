<?php
/**
 * 操作日志数据模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class OplogsModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'oplogs';

	protected $dict_acts = array(
		11 => '关注问题',
		21 => '最佳回复',
		31 => '给好评',
		32 => '给差评',
		51 => '问题详情统计计数',
		52 => '栏目列表统计计数',
		53 => '标签列表统计计数',
	);
}
