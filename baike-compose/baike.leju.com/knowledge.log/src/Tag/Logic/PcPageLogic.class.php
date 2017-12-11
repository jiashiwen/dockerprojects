<?php
/**
 * pc页面核心逻辑基础类
 * @author Robert <yongliang1@leju.com>
 */

namespace Tag\Logic;

class PcPageLogic extends PageLogic{

	public function initIndexFocus($redis_key)
	{
		return false;
	}

	public function initIndexHot($redis_key)
	{
		$frontLogic = D('Front', 'Logic','Common');
		$result = $frontLogic->getHot();
		return $result;
		//return $this->getHot($redis_key);
	}

	public function initIndexHuman($redis_key)
	{
		return $this->getHuman($redis_key);
	}

	public function initIndexOrganization($redis_key)
	{
		return $this->getOrganization($redis_key);
	}

	public function initIndexFresh($redis_key)
	{
		return $this->getFresh($redis_key);
	}

	public function initListAll($redis_key, $page, $pagesize)
	{
		return $this->getPCList($redis_key, $page, $pagesize);
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
	 * 全部词条PC
	 */
	public function getList($key, $page=1, $pagesize=16)
	{
		$list = $this->r->get($key);

		if(!$list)
		{
			$url = C('DATA_TRANSFER_API_URL') . "api/item?sort=time";
			$result = curl_get($url);
			$list = array();
			if($result['status'] == true)
			{
				$list = json_decode($result['result'], true);
				//$list = $list['result'];
				$this->r->set($key, $list, $this->_cache_time);
			}
		}

		return $list;
	}

	/*
	 * 搜索词条
	*/
	public function getSearchList($key, $page=1, $pagesize=16)
	{
		$url = C('DATA_TRANSFER_API_URL') . "api/item?sort=time&client=1&entry={$key}&search=1&page={$page}&pagesize={$pagesize}";
		$result = curl_get($url);
		$list = array();
		if($result['status'] == true)
		{
			$list = json_decode($result['result'], true);
		}

		return $list;
	}

	/*
	 * 按标签查询词条
	*/
	public function getTagList($key, $tag, $page=1, $pagesize=16)
	{
		$list = $this->r->get($key);
		if(!$list)
		{
			$url = C('DATA_TRANSFER_API_URL') . "api/item?sort=time&client=1&search=1&tag={$tag}";
			$result = curl_get($url);
			$list = array();
			if($result['status'] == true)
			{
				$list = json_decode($result['result'], true);
				$this->r->set($key, $list, $this->_cache_time);
			}
		}

		return $list;
	}

}
