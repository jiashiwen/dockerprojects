<?php
/**
 * 知识库控制器基础类
 *
 */
namespace Baike\Controller;
use Think\Controller;

class BaseController extends Controller {
	protected $_debug = 0;	// 是否调试模式
	protected $_userid = 0; // 访问者用户编号
	protected $_theme = 'default';
	protected $_city = array();
	protected $_device = 'mobile';

	protected $member = null;
	public function __construct() {
		parent::__construct();
		// 是否开启调试模式
		$this->_debug = I('dbg', 0, 'intval') == 0 ? 0 : 1;

		// 标识是否进入 Baike Module 中
		if ( $this->_debug === 1 ) {
			echo '<!-- This is Baike -->', PHP_EOL;
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

		// 获取城市 ip 定位
		if ( !isset($_COOKIE['city_cn']) ) {
			$_COOKIE['city_ip'] = get_client_ip();
			$city = getIPLocation($_COOKIE['city_ip']);
			$_COOKIE['city_cn'] = $city['city_cn'];
			$_COOKIE['city_en'] = $city['city_en'];
			cookie('city_cn',$_COOKIE['city_cn']);
			cookie('city_en',$_COOKIE['city_en']);
		}
		$this->_city['cn'] =& $_COOKIE['city_cn'];
		$cities = C('CITIES.all');
		if ( array_key_exists($this->_city['cn'], $cities) ) {
			$this->_city = array_merge($this->_city, $cities[$this->_city['cn']]);
		}
		$this->assign('location', $this->_city);

		return true;
		// $subdomain = !isset($_SERVER['VISITOR_DEVICE']) ? 'mobile' : strtolower($_SERVER['VISITOR_DEVICE']);
		// if ( !array_key_exists($subdomain, $subdomains) ) {
		// 	$subdomain = 'mobile';
		// }
		// $this->_device = $subdomain;

		// 从 COOKIE 获取访问者用户编号
		$this->_userid = isset($_COOKIE['M_UID']) ? intval($_COOKIE['M_UID']) : 0;
		// if ( $this->_userid > 0 ) {
		// 	$this->member = D('Members', 'Model');
		// 	// 获取登录用户基本信息
		// 	$userinfo = $this->member->getUserByID($this->_userid);
		// 	$this->_userinfo = $userinfo ? $userinfo : $this->member->getUserByID(0);
		// } else {
		// 	$this->_userinfo = $this->member->getUserByID(0);
		// }
		// var_dump($_SERVER);

		// @debug :
		if ( $this->_debug === 1 ) {
			echo '<!--', PHP_EOL, print_r($_SESSION, true), PHP_EOL, '-->', PHP_EOL;
		}

		
		$this->assign('refs', C('JUMP_REF'));

		$this->assign('userinfo', $this->_userinfo);

		$this->parseCategories();
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