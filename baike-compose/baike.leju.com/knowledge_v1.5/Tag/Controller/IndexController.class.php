<?php
/**
 * 知识库词条主页
 * @author Robert <yongliang1@leju.com>
 */
namespace Tag\Controller;

class IndexController extends BaseController {
	/*plantB才用--------
	protected $_cache_time = 86400;
	protected $_cache_keys2 = array(
		'focus' => 'wiki:tag:focus2',
		'hot' => 'wiki:tag:hot2',
		'human' => 'wiki:tag:human2',
		'organization' => 'wiki:tag:organization2',
		'fresh' => 'wiki:tag:fresh2',
	);
	------------------*/

	/**
	 * 首页
	 */
	public function index(){
		$pageLogic = D($this->_device.'Page', 'Logic' );

		//焦点图api
		$focus = $pageLogic->initIndexFocus($this->_cache_keys['focus']);
		$this->assign('focus', $focus);

		//热门词条api
		$hot = $pageLogic->initIndexHot($this->_cache_keys['hot']);
		$this->assign('hot', $hot);

		//房产名人百科api
		$human = $pageLogic->initIndexHuman($this->_cache_keys['human']);
		$this->assign('human', $human);

		//房产机构百科api
		$organization = $pageLogic->initIndexOrganization($this->_cache_keys['organization']);
		$this->assign('organization', $organization);

		//最新词条api
		$fresh = $pageLogic->initIndexFresh($this->_cache_keys['fresh']);
		$this->assign('fresh', $fresh);

		//统计代码
		$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
		$level2_page = ($this->_device == 'pc') ? 'pc_ct_index' : 'wd_index';
		$this->assign('level1_page', $level1_page);
		$this->assign('level2_page', $level2_page);

		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->index();

		$this->setPageInfo($seo);

		$this->display();
	}

	/**
	 * 首页替代版
	 */
	/*
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
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_index');

		$this->setPageInfo();
		$this->display();
	}*/
}