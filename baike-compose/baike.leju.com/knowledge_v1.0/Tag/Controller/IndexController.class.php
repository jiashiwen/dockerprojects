<?php
/**
 * 知识库词条主页
 * @author Robert <yongliang1@leju.com>
 */
namespace Tag\Controller;

class IndexController extends BaseController {
	protected $_cache_time = 86400;
	protected $_cache_keys = array(
		'focus' => 'wiki:tag:focus',
		'hot' => 'wiki:tag:hot',
		'human' => 'wiki:tag:human',
		'organization' => 'wiki:tag:organization',
		'fresh' => 'wiki:tag:fresh',
	);
	protected $_cache_keys2 = array(
		'focus' => 'wiki:tag:focus2',
		'hot' => 'wiki:tag:hot2',
		'human' => 'wiki:tag:human2',
		'organization' => 'wiki:tag:organization2',
		'fresh' => 'wiki:tag:fresh2',
	);

	/**
	 * 首页
	 */
	public function index(){
		$r = S(C('REDIS'));

		//焦点图api
		$focus = $r->get($this->_cache_keys['focus']);
		if($focus)
		{
			$this->assign('focus', $focus);
		}
		else
		{
			$focus_api = curl_get(C('DATA_TRANSFER_API_URL').'api/item?recommend=0&page=1&pagesize=5&sort=time');
			$focus = json_decode($focus_api['result'], true);
			if(!empty($focus['result']))
			{
				$this->assign('focus',$focus['result']);
				$r->set($this->_cache_keys['focus'], $focus['result'], $this->_cache_time);
			}
		}

		//热门词条api
		$hot = $r->get($this->_cache_keys['hot']);
		if($hot)
		{
			$this->assign('hot', $hot);
		}
		else
		{
			$hot_api = curl_get(C('DATA_TRANSFER_API_URL').'api/item?page=1&pagesize=6&sort=hits');
			$hot = json_decode($hot_api['result'], true);
			if(!empty($hot['result']))
			{
				$this->assign('hot',$hot['result']);
				$r->set($this->_cache_keys['hot'], $hot['result'], 300);
			}
		}

		//房产名人百科api
		$human = $r->get($this->_cache_keys['human']);
		if($human)
		{
			$this->assign('human', $human);
		}
		else
		{
			$human_api = curl_get(C('DATA_TRANSFER_API_URL').'api/item?recommend=1&page=1&pagesize=4&sort=time');
			$human = json_decode($human_api['result'], true);
			if(!empty($human['result']))
			{
				$this->assign('human',$human['result']);
				$r->set($this->_cache_keys['human'], $human['result'], $this->_cache_time);
			}
		}

		//房产机构百科api
		$organization = $r->get($this->_cache_keys['organization']);
		if($organization)
		{
			$this->assign('organization', $organization);
		}
		else
		{
			$organization_api = curl_get(C('DATA_TRANSFER_API_URL').'api/item?recommend=2&page=1&pagesize=4&sort=time');
			$organization = json_decode($organization_api['result'], true);
			if(!empty($organization['result']))
			{
				$this->assign('organization',$organization['result']);
				$r->set($this->_cache_keys['organization'], $organization['result'], $this->_cache_time);
			}
		}

		//最新词条api
		$fresh = $r->get($this->_cache_keys['fresh']);
		if($fresh)
		{
			$this->assign('fresh', $fresh);
		}
		else
		{
			$fresh_api = curl_get(C('DATA_TRANSFER_API_URL').'api/item?page=1&pagesize=6&sort=time');
			$fresh = json_decode($fresh_api['result'], true);
			if(!empty($fresh['result']))
			{
				$this->assign('fresh',$fresh['result']);
				$r->set($this->_cache_keys['fresh'], $fresh['result'], $this->_cache_time);
			}
		}

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_index');

		$this->setPageInfo();
		$this->display();
	}

	/**
	 * 首页替代版
	 */
	public function index2(){
		$r = S(C('REDIS'));
		$mW = D('wiki', 'Model', 'Common');

		//焦点图api
		$focus = $r->get($this->_cache_keys2['focus']);
		if($focus)
		{
			$this->assign('focus', $focus);
		}
		else
		{
			$focus = array();
			$result = $mW->where('focus_time > 0')->order(array('focus_time'=>'desc'))->limit(5)->select();

			if(!empty($result))
			{
				foreach($result as $k=>$v)
				{
					$focus[$k]['id'] = $v['title'];
					$focus[$k]['pic'] = $v['focus_pic'];
					$focus[$k]['title'] = $v['focus_title'];
				}
				$this->assign('focus',$focus);
				$r->set($this->_cache_keys2['focus'], $focus, $this->_cache_time);
			}
		}

		//热门词条api
		$hot = $r->get($this->_cache_keys2['hot']);
		if($hot)
		{
			$this->assign('hot', $hot);
		}

		//房产名人百科api
		$human = $r->get($this->_cache_keys2['human']);
		if($human)
		{
			$this->assign('human', $human);
		}
		else
		{
			$human = array();
			$result = $mW->where('celebrity_time > 0')->order(array('celebrity_time'=>'desc'))->limit(4)->select();
			if(!empty($result))
			{
				foreach($result as $k=>$v)
				{
					$human[$k]['id'] = $v['title'];
					$human[$k]['pic'] = $v['celebrity_pic'];
					$human[$k]['title'] = $v['celebrity_title'];
				}
				$this->assign('human',$human);
				$r->set($this->_cache_keys2['human'], $human, $this->_cache_time);
			}
		}

		//房产机构百科api
		$organization = $r->get($this->_cache_keys2['organization']);
		if($organization)
		{
			$this->assign('organization', $organization);
		}
		else
		{
			$organization = array();
			$result = $mW->where('company_time > 0')->order(array('company_time'=>'desc'))->limit(4)->select();
			if(!empty($result))
			{
				foreach($result as $k=>$v)
				{
					$organization[$k]['id'] = $v['title'];
					$organization[$k]['pic'] = $v['company_pic'];
					$organization[$k]['title'] = $v['company_title'];
				}
				$this->assign('organization',$organization);
				$r->set($this->_cache_keys2['organization'], $organization, $this->_cache_time);
			}
		}

		//最新词条api
		$fresh = $r->get($this->_cache_keys2['fresh']);
		if($fresh)
		{
			$this->assign('fresh', $fresh);
		}
		else
		{
			$fresh = array();
			$result = $mW->where('ptime > 0')->order(array('ptime'=>'desc'))->limit(6)->select();
			if(!empty($result))
			{
				foreach($result as $k=>$v)
				{
					$fresh[$k]['title'] = $v['title'];
				}
				$this->assign('fresh',$fresh);
				$r->set($this->_cache_keys2['fresh'], $fresh, $this->_cache_time);
			}
		}

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_index');

		$this->setPageInfo();
		$this->display();
	}
}