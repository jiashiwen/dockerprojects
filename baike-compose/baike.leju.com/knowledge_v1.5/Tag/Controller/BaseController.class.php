<?php
/**
 * 百科词条子系统控制器 基础类
 * @author Robert <yongliang1@leju.com>
 */
namespace Tag\Controller;
use Think\Controller;

class BaseController extends Controller {
	protected $_debug = 0;	// 是否调试模式
	protected $_theme = DEFAULT_THEME;
	// protected $_cur_city = 'bj'; //默认城市
	protected $_city = array();
	protected $_def_city = 'bj'; //默认城市

	//词条分类
	protected $_category = array("0" => "人物", "1" => "机构");

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

	protected $member = null;
	public function __construct() {
		parent::__construct();
		// 是否开启调试模式
		$this->_debug = I('dbg', 0, 'intval') == 0 ? 0 : 1;

		// 标识是否进入 Baike Module 中
		if ( $this->_debug === 1 ) {
			echo '<!-- This is Tag SubSystem -->', PHP_EOL;
		}

		// 根据域名判断使用的展现模版
		// 方式 1 : 在 Web Server 的配置文件中，对域名绑定做处理，并针对请求域名做设备识别
		// $this->_device = $subdomain = $_SERVER['VISITOR_DEVICE'];

		// 方式 2 : 直接对请求域名做设备识别，并针对请求域名做设备识别 (更通用些)
		$subdomains = array('mobile'=>'m', 'pc'=>'');
		$this->_device = $subdomain = ( strpos($_SERVER['HTTP_HOST'], 'm.')!==false ) ? 'mobile' : 'pc';
		// @debug :
		if ( $this->_debug === 1 ) {
			echo '<!--', PHP_EOL, print_r($this->_device, true), PHP_EOL, '-->', PHP_EOL;
		}

		// 新添加，判断是否 app 进入，如果app进入，则不显示导航头
		$is_app = I('ljmf_s', cookie('isapp'), 'trim,strtolower');
		$allowed_apps = array('yd_kdlj');
		$is_app = in_array($is_app, $allowed_apps) ? $is_app : 'notapp';
		cookie('isapp', $is_app);
		$this->assign('isapp', $is_app);


		/**
 		 * @var $themes array 为可用的主题模版列表
		 */
		$themes = array('v1');
		// 通用参数，可强行指定要使用的主题模版
		$theme = strtolower(I('get.theme', '', 'trim'));
		$this->_theme = $subdomains[$subdomain] . ( in_array($theme, $themes) ? $theme : C('DEFAULT_THEME') );
		// 更新 综合处理之后的模版主题
		C('DEFAULT_THEME', $this->_theme);
		// 为业务请求选定要使用的模版主题
		$this->theme($this->_theme);
		// @debug :
		if ( $this->_debug === 1 ) {
			echo '<!--', PHP_EOL, '当前主题: ', $this->_theme, PHP_EOL, '可用主题: ', print_r($themes, true), PHP_EOL, '-->', PHP_EOL;
		}

		//移动默认城市
		if($this->_device == 'mobile')
		{
			// $cities = C('CITIES.ALL');
			// $cur_city = I('get.city', cookie('B_CITY'), 'trim,strip_tags,htmlspecialchars');
			$cities = C('CITIES.ALL');
			$city_code = I('get.city', '', 'trim,strip_tags,htmlspecialchars');
			$city_code = $city_code == '' ? cookie('B_CITY') : $city_code;
		}
		else
		{
			$page = D('Front', 'Logic', 'Common');
			// PC 版页面，乐居标准头尾模版读取
			$flush = I('get.flush_tpl', 0, 'intval') == 1 ? true : false;
			$this->assign('common_tpl', $page->getPCPublicTemplate($flush));

			// pc 页面，公用头部显示热搜关键词和知识列表
			$cities = C('CITIES.CMS');
			$this->assign('cities',$cities);
			$city_code = I('get.city', '', 'clear_all');
			$city_code = $city_code == '' ? cookie('citypub') : $city_code;

			//获取知识全部栏目
			$cate = D('Cate', 'Logic','Common');
			$cate_all = $cate->getIndexTopCategories();
			$this->assign('cate_all', $cate_all);
			// //PC默认城市
			// $cities = C('CITIES.CMS');
			// $cur_city = I('get.city', cookie('citypub'), 'trim,strip_tags,htmlspecialchars');
		}
		// array_key_exists($cur_city, $cities) && $this->_cur_city = $cur_city;
		// $this->assign('city', $this->_cur_city);
		// $this->assign('cities_all', $cities);


		// 统一城市信息
		if ( !array_key_exists($city_code, $cities) ) {
			$city_code = $this->_def_city;
		}
		$this->_city = $cities[$city_code];
		$this->_city['code'] = $city_code;
		$this->assign('city', $this->_city);

		// $heard = file_get_contents('http://bj.leju.com/include/leju/pc/2016/topnav.shtml');
		// $this->assign('heard', $heard);
		// $footer = file_get_contents('http://bj.leju.com/include/leju/pc/2016/footer.shtml ');
		// $this->assign('footer', $footer);

		return true;
	}

	public function setPageInfo( $info=array() ) {
		$site = '百科词条 - 乐居知识系统';
		$pageinfo = array(
			'title' => '首页'.' - '.$site,
			'keywords' => '乐居,知识,百科,词条,地产,企业,名人,政策,关键词,解读',
			'description' => '乐居知识系统百科词条子系统介绍信息',
		);
		$pageinfo = array_merge($pageinfo, $info);
		// var_dump($pageinfo);
		$this->assign('pageinfo', $pageinfo);
	}

	protected function ajax_return($data) {
		header('Content-Type: text/json');
		echo json_encode($data);
		exit;
	}
}