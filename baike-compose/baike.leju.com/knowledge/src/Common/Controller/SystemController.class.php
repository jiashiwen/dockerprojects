<?php
/**
 * 系统控制器基础类
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Controller;
use Think\Controller;

class SystemController extends Controller {

	protected $_debug = false;	// 是否调试模式
	protected $_userid = 0; // 访问者用户编号
	protected $_browserid = ''; // 浏览器标识编号
	protected $_userinfo = array(); // 登录用户的信息
	protected $_islogined = false; // 用户是否已经登录
	protected $_theme = 'default';
	protected $_city = array();
	protected $_def_city = 'bj'; //默认城市
	protected $_device = 'mobile';
	protected $_isapp = false;
	protected $site = '知识 - 乐居知识系统';
	protected $pageinfo = array(); // 基本页面属性
	protected $_flush = array('data'=>false, 'tpl'=>false);	// 是否重置缓存
	protected $member = null;

	// 所有页面变量
	protected $_alldata = array();

	protected $lPage = null;	// 前端页面逻辑对象


	public function __construct() {
		parent::__construct();
		// var_dump('@SystemController', $this);

		// 开启自动跳转机制 @@nofit!=0
		$autofit = !I('get.nofit', 0, 'intval');
		autofit($autofit);

		// 是否开启调试模式 @@dbg=35940
		$this->_debug = I('dbg', 0, 'intval') === 35940 ? true : false;

		// 根据域名判断使用的展现模版
		// 方式 1 : 在 Web Server 的配置文件中，对域名绑定做处理，并针对请求域名做设备识别
		// $this->_device = $subdomain = $_SERVER['VISITOR_DEVICE'];

		// 方式 2 : 直接对请求域名做设备识别，并针对请求域名做设备识别 (更通用些)
		$subdomains = array('touch'=>'m', 'pc'=>'');
		$this->_device = $subdomain = ( strpos($_SERVER['HTTP_HOST'], 'm.')!==false ) ? 'touch' : 'pc';

		// 新添加，判断是否 app 进入，如果app进入，则不显示导航头 @@ljmf_s=yd_kdlj
		$is_app = I('ljmf_s', cookie('isapp'), 'trim,strtolower');
		$allowed_apps = array('yd_kdlj');
		$is_app = in_array($is_app, $allowed_apps) ? $is_app : 'notapp';
		cookie('isapp', $is_app);
		$this->assign('isapp', $is_app);

		/**
 		 * @var $themes array 为可用的主题模版列表
		 */
		$themes = array('v1');
		// 通用参数，可强行指定要使用的主题模版 @@theme=''
		$theme = strtolower(I('get.theme', '', 'trim'));
		$this->_theme = $subdomains[$subdomain] . ( in_array($theme, $themes) ? $theme : C('DEFAULT_THEME') );
		// 更新 综合处理之后的模版主题
		C('DEFAULT_THEME', $this->_theme);
		// 为业务请求选定要使用的模版主题
		$this->theme($this->_theme);

		if ( I('get.flush_data', 0, 'intval')==1 ) {
			$this->_flush['data'] = true;
		}
		if ( I('get.flush_tpl', 0, 'intval')==1 ) {
			$this->_flush['tpl'] = true;
		}

		// 判断访客是否已经登录
		$this->check_login($this->_device);


		// PC 版控制器入口 前置处理逻辑段
		if ( $this->_device == 'pc' ) {
			$page = D('Front', 'Logic', 'Common');
			// PC 版页面，乐居标准头尾模版读取
			$this->assign('common_tpl', $page->getPCPublicTemplate($this->_flush['tpl']));
			// pc 页面，公用头部显示热搜关键词和知识列表
			$cities = C('CITIES.CMS');
			$this->assign('cities',$cities);
			$city_code = I('get.city', '', 'clear_all');
			$city_code = $city_code == '' ? cookie('citypub') : $city_code;
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


		// 调试时显示以下内容
		if ( $this->_debug ) {
			echo '<!-- This is Leju Baike From 2016-11-? -->', PHP_EOL,
				 '<!--', PHP_EOL, '当前设备类型 : ', $this->_device, PHP_EOL, '-->', PHP_EOL,
				 '<!--', PHP_EOL, '当前主题名称 : ', $this->_theme, PHP_EOL, 
				 '可用主题列表: ', print_r($themes, true), PHP_EOL, '-->', PHP_EOL,
				 '<!--', PHP_EOL, '当前城市代码 : ', print_r($this->_city, true), PHP_EOL, '-->', PHP_EOL,
				 PHP_EOL;
		}

		return true;

	}

	// 判断用户是否登录用户
	public function check_login( $entry='pc' ) {
		$this->_userid = intval($_COOKIE['M_UID']);
		if ( $this->_userid > 0 ) {
			$this->_userinfo = json_decode(clean_xss($_COOKIE['M_INFO']), true);
			if ( trim($this->_userinfo['headurl'])=='' ) {
				$this->_userinfo['headurl'] = 'http://cdn.leju.com/encypc/images/temp/wd_face.jpg';
			}
			// 用户更新
			$mMembers = D('Members', 'Model', 'Common');
			$exists = $mMembers->find($this->_userid);
			if ( !$exists ) {
				$data = array('uid'=>$this->_userid, 'ctime'=>NOW_TIME, 'data'=>json_encode(array()));
				$mMembers->data($data)->add();
			}
		}
		$this->_islogined = ( $this->_userid !== 0 );
		$this->_browserid = clean_xss(cookie('newgatheruuid'));
		return $this->_islogined;
	}

	// 模版参数统一传值
	protected function assign_all($all) {
		$status = isset($all['status']) ? !!$all['status'] : true;
		unset($all['status']);
		if ( !$status ) {
			$this->show_error($all['message']);
		}
		foreach ( $all as $key => $data ) {
			$this->assign($key, $data);
		}
		return true;
	}

	// 设置页面 SEO 信息
	public function setPageInfo( $info=array() ) {
		$pageinfo = array_merge($this->pageinfo, $info);
		$this->assign('pageinfo', $pageinfo);
	}

	// 根据用户 COOKIE 获取用户当前访问站点城市
	public function getUserCity( $default='bj' ) {
		$this->_mcity = isset($_COOKIE['M_CITY']) ? intval($_COOKIE['M_CITY']) : $default;
		return $this->_mcity;
	}

	protected function ajax_error($msg) {
		$result = array('status'=>false,'reason'=>trim($msg));
		$this->ajax_return($result);
	}
	protected function ajax_return($data) {
		set_ajax_output(true);
		die(json_encode($data));
	}
}