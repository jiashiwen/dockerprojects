<?php
/**
 * 移动端页面逻辑
 * @author Robert <yongliang1@leju.com>
 */

namespace Tag\Logic;

class TouchPageLogic extends PageLogic {

	public function initIndexFocus() {
		return $this->getFocus();
	}

	public function initIndexHot() {
		$result = D('Front', 'Logic','Common')->getHotWords();
		return array_slice($result, 0, 6);
	}

	public function initIndexHuman()
	{
		$result = $this->getPersons();
		return array_slice($result, 0, 4);
	}

	public function initIndexOrganization()
	{
		$result = $this->getCompanies();
		return array_slice($result, 0, 4);
	}

	public function initIndexFresh()
	{
		$result = $this->getLatest();
		return array_slice($result, 0, 6);
	}

	public function initListAll()
	{
		return $this->_getList($redis_key);
	}

	public function initDetailNews($detail)
	{
		return $this->getNews($detail);
	}

	public function initDetailHouse($detail)
	{
		return $this->getHouse($detail);
	}

	/*
	 * 全部词条移动
	 */
	public function getListAll() {
		$key = 'WIKI:PUBLISHED:LISTALL';
		$result = $this->redis->Get($key);
		if( $result && !$this->_flush ) {
			return $result;
		}
		$allsize = 10000;
		$where = array();
		$order = 'firstletter ASC, utime DESC';
		$fields = array('id', 'title', 'cateid', 'hits', 'utime', 'tags', 'tagids', 'editor', 'firstletter');
		$result = $this->_getList($where, $order, 1, $allsize, $fields);
		$list = array();
		foreach ( $result['list'] as $i => $item ) {
			$letter = strtoupper($item['firstletter']);
			if ( !array_key_exists($letter, $list) ) {
				$list[$letter] = array();
			}
			array_push($list[$letter], $item);
		}
		$result['list'] = $list;
		if ( $result ) {
			$this->redis->set($key, $result, $this->_cache_time);
		}
		return $result;
	}

	/**
	 * 获取相关新闻 PC版
	 * 规则 按当前百科词条设置的标签，提取新闻池近期新闻
	 * 4、相关新闻
	 * 调取该词条关联的标签在发布系统中的新闻按照时间倒序排序，若为空则，调取标签房产下的新闻，最多显示4条
	 */
	public function getRelationNews( $tagids=array(), $page=1, $pagesize=20 ) {
		$list = parent::getRelationNews($tagids, 'touch');
		$count = count($list);
		$_list = array_chunk($list, $pagesize);
		$page = $page<=1 ? 0 : $page-1;
		if ( array_key_exists($page, $_list) ) {
			$list = $_list[$page];
			foreach ( $list as $i => &$item ) {
				$item['url'] = 'http://m.leju.com/news-'.$item['city'].'-'.$item['id'].'.html';
			}
		} else {
			$list = array();
		}
		return $list;
	}
}