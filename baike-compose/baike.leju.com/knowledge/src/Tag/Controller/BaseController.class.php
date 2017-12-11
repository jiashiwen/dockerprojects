<?php
/**
 * 百科词条子系统控制器 基础类
 * @author Robert <yongliang1@leju.com>
 */
namespace Tag\Controller;
use Common\Controller;

class BaseController extends \Common\Controller\SystemController {

	protected $redis = null;
	protected $_cache_time = 86400;

	protected $_cache_keys = array(
			'focus' => 'wiki:tag:focus',
			'hot' => 'wiki:tag:hot',
			'human' => 'wiki:tag:human',
			'organization' => 'wiki:tag:organization',
			'fresh' => 'wiki:tag:fresh',
			'pcall' => 'wiki:tag:list:pcall',
			'cate' => 'wiki:tag:list:cate-',
			'all' => 'wiki:tag:list:all',
			'detail' => 'wiki:tag:detail:',
			'tag' => 'wiki:tag:tag:',
	);


	public function __construct() {
		parent::__construct();

		$this->redis = S(C('REDIS'));
		// 自动加载前端处理逻辑
		$Device = ucfirst($this->_device);
		$this->lPage = D( $Device.'Page', 'Logic' );
		// 匹配部署模式
		$this->_deploy = defined('APP_DEPLOY') ? APP_DEPLOY : 'dev';
		return true;
	}

	protected function error($message='',$jumpUrl='',$ajax=false) {
		parent::error($message, $jumpUrl, $ajax);
		$this->display(C('ERROR_PAGE'));
		exit;
	}

	// 设置页面 SEO 信息
	public function setPageInfo( $info=array() ) {
		$alt_device = $this->_device!='pc' ? 'pc' : 'touch';
		$pageinfo = array(
			'title' => trim($info['title']),
		);
		if ( isset($info['seo']) ) {
			$pageinfo['alt_url'] = url('show', array($info['id'], $info['cateid']), $alt_device, 'wiki');
			$pageinfo['seo_title'] = trim($info['seo']['title']);
			$pageinfo['keywords'] = trim($info['seo']['keywords']);
			$pageinfo['description'] = trim($info['seo']['description']);
		} else {
			$pageinfo = array_merge($this->pageinfo, $info);
		}
		$this->assign('pageinfo', $pageinfo);
	}

}