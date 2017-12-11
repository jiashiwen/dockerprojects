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

	/**
	 * 首页
	 */
	public function index(){
		$this->lPage->setFlush(true);
		//焦点图api
		$focus = $this->lPage->initIndexFocus();
		$this->assign('focus', $focus);
		// var_dump('focus', $focus);
		//热门词条api
		$hot = $this->lPage->initIndexHot();
		$this->assign('hot', $hot);
		// var_dump('hot', $hot);

		//房产名人百科api
		$human = $this->lPage->initIndexHuman();
		$this->assign('human', $human);
		// var_dump('human', $human);

		//房产机构百科api
		$organization = $this->lPage->initIndexOrganization();
		$this->assign('organization', $organization);
		// var_dump('organization', $organization);

		//最新词条api
		$fresh = $this->lPage->initIndexFresh();
		$this->assign('fresh', $fresh);
		// var_dump('fresh', $fresh);

		//统计代码
		$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
		$level2_page = ($this->_device == 'pc') ? 'pc_ct_index' : 'wd_index';
		$this->assign('level1_page', $level1_page);
		$this->assign('level2_page', $level2_page);

		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->index("wiki");
		$alt_device = $this->_device=='pc' ? 'touch' : 'pc';
		$seo['alt_url'] = url('index', array(), $alt_device, 'wiki');
		$this->setPageInfo($seo);

		$this->display();
	}

}