<?php
/**
 * 知识库控制器基础类
 *
 */
namespace Baike\Controller;
use Think\Controller;

class BaseController extends Controller {
	protected $_debug = false;	// 是否调试模式
	protected $_userid = 0; // 访问者用户编号
	protected $_theme = 'default';
	protected $_city = array();
	protected $_device = 'mobile';
	protected $_def_city = 'bj'; //默认城市

	protected $member = null;
	public function __construct() {
		parent::__construct();
		// 是否开启调试模式
		$this->_debug = I('dbg', 0, 'intval') === 35940 ? true : false;
		// 标识是否进入 Baike Module 中
		if ( $this->_debug ) {
			echo '<!-- This is Leju Baike From 2016-11-? -->', PHP_EOL;
		}

		// 根据域名判断使用的展现模版
		// 方式 1 : 在 Web Server 的配置文件中，对域名绑定做处理，并针对请求域名做设备识别
		// $this->_device = $subdomain = $_SERVER['VISITOR_DEVICE'];

		// 方式 2 : 直接对请求域名做设备识别，并针对请求域名做设备识别 (更通用些)
		$subdomains = array('mobile'=>'m', 'pc'=>'');
		$this->_device = $subdomain = ( strpos($_SERVER['HTTP_HOST'], 'm.')!==false ) ? 'mobile' : 'pc';

		// 新添加，判断是否 app 进入，如果app进入，则不显示导航头
		$is_app = I('ljmf_s', cookie('isapp'), 'trim,strtolower');
		$allowed_apps = array('yd_kdlj');
		$is_app = in_array($is_app, $allowed_apps) ? $is_app : 'notapp';
		cookie('isapp', $is_app);
		$this->assign('isapp', $is_app);

		// @debug :
		if ( $this->_debug ) {
			echo '<!--', PHP_EOL, '当前设备 : ', $this->_device, PHP_EOL, '-->', PHP_EOL;
		}

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
		if ( $this->_debug ) {
			echo '<!--', PHP_EOL, '当前主题: ', $this->_theme, PHP_EOL, '可用主题: ', print_r($themes, true), PHP_EOL, '-->', PHP_EOL;
		}


		// PC 版控制器入口 前置处理逻辑段
		if ( $this->_device == 'pc' ) {
			$page = D('Front', 'Logic', 'Common');
			// PC 版页面，乐居标准头尾模版读取
			$flush = I('get.flush_tpl', 0, 'intval') == 1 ? true : false;
			$this->assign('common_tpl', $page->getPCPublicTemplate($flush));
			// pc 页面，公用头部显示热搜关键词和知识列表
			$cities = C('CITIES.CMS');
			$this->assign('cities',$cities);
			$city_code = I('get.city', '', 'clear_all');
			$city_code = $city_code == '' ? cookie('citypub') : $city_code;
			// $list = $page->getSuggest('', $city_code);
		}
		// 移动版控制器入口 前置处理逻辑段
		// 移动版城市逻辑全程使用 city_en
		if ( $this->_device == 'mobile' ) {
			$cities = C('CITIES.ALL');
			$city_code = I('get.city', '', 'trim,strip_tags,htmlspecialchars');
			$city_code = $city_code == '' ? cookie('B_CITY') : $city_code;
		}

		// 统一城市信息
		if ( !array_key_exists($city_code, $cities) ) {
			$city_code = $this->_def_city;
		}
		$this->_city = $cities[$city_code];
		$this->_city['code'] = $city_code;
		$this->assign('city', $this->_city);

		return true;

	}

	// // 对于提问和回答时，需要做表单验证，避免反复提交
	// public function genToken() {
	// 	return rand(0, 9999);
	// }
	// public function checkToken($token) {
	// 	if ( isset($_SESSION['token']) && trim(strval($token)) == trim(strval($_SESSION['token'])) ) {
	// 		unset($_SESSION['token']);
	// 		return true;
	// 	}
	// 	return false;
	// }

	// 设置页面 SEO 信息
	public function setPageInfo( $info=array() ) {
		$site = '知识 - 乐居知识系统';
		$pageinfo = array(
			'title' => '首页'.' - '.$site,
			'keywords' => '乐居,知识,百科,词条,地产,企业,名人,政策,关键词,解读',
			'description' => '乐居知识系统知识子系统介绍信息',
		);
		$pageinfo = array_merge($pageinfo, $info);
		$this->assign('pageinfo', $pageinfo);
	}

	// 根据用户 COOKIE 获取用户当前访问站点城市
	public function getUserCity( $default='bj' ) {
		$this->_mcity = isset($_COOKIE['M_CITY']) ? intval($_COOKIE['M_CITY']) : $default;
		return $this->_mcity;
	}

	protected function ajax_return($data) {
		set_ajax_output(true);
		die(json_encode($data));
	}
}