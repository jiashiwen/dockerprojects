<?php
/**
 * 标签库模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;

class TagsModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'tags';
	// 已知的数据来源
	protected $sources = array(0=>'乐居自有', 1=>'新闻池标签库', 150=>'链家网', 151=>'搜房网');

	/**
	 * 批量添加，适用于导入数据
	 * @param $list array 批量添加的标签词条
	 * @param $source int 标签词条来源 0为乐居 1乐居标签库
	 * @param $unique bool 标签词条是否通过数据库排重
	 * @return bool 成功返回 true 失败返回 false
	 */
	public function bulkAdd ( $list, $source=0, $unique=false ) {
		$lists = array();
		foreach ( $list as $i => $item ) {
			$word = str_replace(' ', '', trim($item['word']));
			if ( strpos($word, ',')!==false ) {
				continue;
			}
			if ( $unique===true ) {
				$where = array(
					'source' => $source,
					'name' => $word,
				);
				if ( $this->where($where)->count() > 0 ) {
					continue;
				}
			}
			$lists[] = $word;
		}
		$lists = array_unique($lists);
		// 拆分，并批量写入
		$lists = array_chunk($lists, 500);
		foreach ( $lists as $g => $list ) {
			$datalist = array();
			foreach ( $list as $i => &$item ) {
				$datalist[] = array('name'=>$item, 'source'=>$source);
			}
			$this->addAll($datalist);
		}
		return true;
	}

	public function countSource($sourceid=false) {
		$where = array();
		if ( $sourceid!==false ) {
			$where['source'] = $sourceid;
		}
		if ( !empty($where) ) {
			$this->where($where);
		}
		$count = $this->count();
		return $count;
	}

	/**
	 * @IMPORTANT!!!
	 * 联想词查询 仅作临时支持使用
	 */
	public function suggest($word, $limit=5) {
		if ( !is_string($word) || $word=='' ) {
			return false;
		}
		// @TODO 此处需要做注入过滤
		//$where = array('source'=>1, "`name` LIKE '{$word}%'");
		$where['source'] = 1;
		$where['name'] = array('like', "%{$word}%");
		$list = $this->where($where)->limit($limit)->field(array('name','source'))->select();
		return $list;
	}
}
