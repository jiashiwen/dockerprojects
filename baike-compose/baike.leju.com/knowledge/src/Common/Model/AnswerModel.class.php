<?php
/**
 * 问答系统 回答数据模型类
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;

class AnswerModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'answers';

	// 获取回复信息的状态字典定义
	public function getStatusDict() {
		return array(
			0 => array('label'=>'已删除', 'class'=>'gray'),
			11 => array('label'=>'待确认', 'class'=>'gray'),
			12 => array('label'=>'待审核', 'class'=>'l_org'),
			21 => array('label'=>'正常', 'class'=>'black'),
			22 => array('label'=>'已置顶', 'class'=>'yellow'),
			23 => array('label'=>'已采纳', 'class'=>'l_grn1'),
		);
	}
}
