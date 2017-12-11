<?php
/**
 * 页面核心逻辑基础类
 * @author Robert <yongliang1@leju.com>
 */

namespace Tag\Logic;

class PageLogic{
	 protected $r;
	 protected $_cache_time = 86400;
	 public function __construct() {
	 	$this->r = S(C('REDIS'));
	 }

	/*
	 * 词条首页焦点图
	 */
	public function getFocus($key, $page=1,$pagesize=5 )
	{
		//焦点图api
		$focus = $this->r->get($key);
		if($focus)
		{
			return $focus;
		}
		else
		{
			$focus_api = curl_get(C('DATA_TRANSFER_API_URL')."api/item?recommend=0&page={$page}&pagesize={$pagesize}&sort=time");
			$focus = json_decode($focus_api['result'], true);
			if(!empty($focus['result']))
			{
				$this->r->set($key, $focus['result'], $this->_cache_time);
				return $focus['result'];
			}
		}
	}

	/*
	 * 首页热门词条
	 * 暂无使用已移至common/logic/frontlogic中
	 */
	public function getHot($key, $page=1,$pagesize=12 )
	{
		//热门词条api
		$hot = $this->r->get($key);
		if($hot)
		{
			return $hot;
		}
		else
		{
			$hot_api = curl_get(C('DATA_TRANSFER_API_URL')."api/item?page={$page}&pagesize={$pagesize}&sort=hits");
			$hot = json_decode($hot_api['result'], true);
			if(!empty($hot['result']))
			{
				$this->r->set($key, $hot['result'], 300);
				return $hot['result'];
			}
		}
	}

	/*
	 * 词条首页名人
	 */
	public function getHuman($key, $page=1,$pagesize=6)
	{
		$human = $this->r->get($key);
		if($human)
		{
			return $human;
		}
		else
		{
			$human_api = curl_get(C('DATA_TRANSFER_API_URL')."api/item?recommend=1&page={$page}&pagesize={$pagesize}&sort=time");
			$human = json_decode($human_api['result'], true);
			if(!empty($human['result']))
			{
				$this->r->set($key, $human['result'], $this->_cache_time);
				return $human['result'];
			}
		}
	}

	/*
	 * 词条首页房产机构
	 */
	public function getOrganization($key, $page=1,$pagesize=6)
	{
		$organization = $this->r->get($key);
		if($organization)
		{
			return $organization;
		}
		else
		{
			$organization_api = curl_get(C('DATA_TRANSFER_API_URL')."api/item?recommend=2&page={$page}&pagesize={$pagesize}&sort=time");
			$organization = json_decode($organization_api['result'], true);
			if(!empty($organization['result']))
			{
				$this->r->set($key, $organization['result'], $this->_cache_time);
				return $organization['result'];
			}
		}
	}

	/*
	 * 首页最新词条
	 */
	public function getFresh($key, $page=1,$pagesize=12)
	{
		$fresh = $this->r->get($key);
		if($fresh)
		{
			return $fresh;
		}
		else
		{
			$fresh_api = curl_get(C('DATA_TRANSFER_API_URL')."api/item?page={$page}&pagesize={$pagesize}&sort=time");
			$fresh = json_decode($fresh_api['result'], true);
			if(!empty($fresh['result']))
			{
				$this->r->set($key, $fresh['result'], $this->_cache_time);
				return $fresh['result'];
			}
		}
	}

/*
	 * 相关资讯
	 */
	public function getNews($detail)
	{
		//百科相关咨询
		$recommends = D('Infos', 'Logic', 'Common');
		$news = array();
		if(!empty($detail['news']))
		{
			//拼接ids
			$temp_news = $temp_push = array();
			foreach($detail['news'] as $v)
			{
				$temp_news[$v['id']] = $v;
				array_push($temp_push, $v['id']);
			}

			$info = $recommends->getNews($temp_push);

			//过滤已删除的新闻
			foreach($temp_news as $k=>$v)
			{
				if(array_key_exists($k, $info))
				{
					$temp_news[$k]['title'] || $temp_news[$k]['title'] = $info[$k]['title'];
					$temp_news[$k]['media'] = $info[$k]['media'];
					$temp_news[$k]['createtime'] = $info[$k]['createtime'];
					$temp_news[$k]['m_url'] = $info[$k]['m_url'];
				}
				else
				{
					unset($temp_news[$k]);
				}
			}

			$news = array_values($temp_news);
		}
		else
		{
			//没有词条的话需要解析标签
			$tags = $recommends->relNews($detail['tags'], 5);
			$news = array_values($tags);
		}
		return $news;
	}

	/*
	 * 相关楼盘
	 */
	public function getHouse($detail)
	{
		$recommends = D('Infos', 'Logic', 'Common');
		if(!empty($detail['house']))
		{
			//拼接ids
			$temp_houses = $temp_push = array();
			foreach($detail['house'] as $v)
			{
				$temp_houses[$v['site'].$v['hid']] = $v;
				array_push($temp_push, $v['site'].$v['hid']);
			}

			$info = $recommends->getHouse($temp_push);

			//过滤已删除的楼盘
			foreach($temp_houses as $k=>$v)
			{
				if(array_key_exists($k, $info))
				{
					$temp_houses[$k]['salephone'] = $info[$k]['phone_extension'] ? "4006108616,2{$info[$k]['phone_extension']}" : '4006108616';
					$temp_houses[$k]['price_display'] = $info[$k]['price_display'];
					$temp_houses[$k]['pic_s320'] = $info[$k]['pic_s320'];
					$temp_houses[$k]['m_url'] = $info[$k]['m_url'];
					$temp_houses[$k]['city'] = $info[$k]['city'];
				}
				else
				{
					unset($temp_houses[$k]);
				}
			}

			return array_values($temp_houses);
		}
	}
}
