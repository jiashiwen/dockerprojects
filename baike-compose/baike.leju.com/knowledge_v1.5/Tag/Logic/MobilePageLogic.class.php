<?php
/**
 * 移动端页面逻辑
 * @author Robert <yongliang1@leju.com>
 */

namespace Tag\Logic;

class MobilePageLogic extends PageLogic {

	public function initIndexFocus($redis_key)
	{
		return $this->getFocus($redis_key);
	}

	public function initIndexHot($redis_key)
	{
		$frontLogic = D('Front', 'Logic','Common');
		$result = $frontLogic->getHot();
		//$result = $this->getHot($redis_key);
		return array_slice($result, 0, 6);
	}

	public function initIndexHuman($redis_key)
	{
		$result = $this->getHuman($redis_key);
		return array_slice($result, 0, 4);
	}

	public function initIndexOrganization($redis_key)
	{
		$result = $this->getOrganization($redis_key);
		return array_slice($result, 0, 4);
	}

	public function initIndexFresh($redis_key)
	{
		$result = $this->getFresh($redis_key);
		return array_slice($result, 0, 6);
	}

	public function initListAll($redis_key)
	{
		return $this->getList($redis_key);
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
	public function getList($key)
	{
		$list = $this->r->get($key);
		if($list)
		{
			return $list;
			exit;
		}
		$list = array();
		$url = C('DATA_TRANSFER_API_URL') . "api/item?sort=pinyin";
		$result = curl_get($url);
		$list = array();
		if($result['status'] == true)
		{
			$list = json_decode($result['result'], true);
			$list = $list['result'][0];
			$this->r->set($key, $list, $this->_cache_time);
		}
		return $list;
	}

}