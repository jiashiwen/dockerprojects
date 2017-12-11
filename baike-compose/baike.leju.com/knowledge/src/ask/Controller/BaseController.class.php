<?php
/**
 * 问答子系统前台模块基础类
 * @author Robert <yongliang1@leju.com>
 */
namespace ask\Controller;
use Think\Controller;
class BaseController extends Controller {

	protected $_debug = false;	// 是否调试模式
	protected $_userid = 0; // 访问者用户编号
	protected $_browserid = ''; // 浏览器标识编号
	protected $_userinfo = array(); // 登录用户的信息
	protected $_islogined = false; // 用户是否已经登录
	protected $_theme = 'default';
	protected $_city = array();
	protected $_device = 'mobile';
	protected $_isapp = false;
	protected $_def_city = 'bj'; //默认城市
	protected $site = '问答 - 乐居知识系统';
	protected $pageinfo = array(); // 基本页面属性
	protected $lFront = null;	// 前端逻辑对象
	protected $_flush = array('data'=>false, 'tpl'=>false);	// 是否重置缓存
	protected $_deploy = 'dev';
	protected $_host = '';

	public function __construct() {
		parent::__construct();
		// 开启自动跳转机制
		$autofit = !I('get.nofit', 0, 'intval');
		autofit($autofit, 'ask');

		// 是否开启调试模式
		$this->_debug = I('dbg', 0, 'intval') === 35940 ? true : false;

		// 匹配部署模式
		$this->_deploy = defined('APP_DEPLOY') ? APP_DEPLOY : 'dev';
		$dbg = [
			'app_deploy' => APP_DEPLOY,
			'app_debug' => APP_DEBUG,
		];
		debug('当前部署状态', $dbg, false, true);
		// 根据域名判断使用的展现模版
		// 直接对请求域名做设备识别，并针对请求域名做设备识别 (更通用些)
		$subdomains = array('mobile'=>'m', 'pc'=>'');
		$this->_device = $subdomain = ( strpos($_SERVER['HTTP_HOST'], 'm.')!==false ) ? 'mobile' : 'pc';
		// 新添加，判断是否 app 进入，如果app进入，则不显示导航头
		$is_app = I('ljmf_s', cookie('isapp'), 'trim,strtolower');
		$allowed_apps = array('yd_kdlj');
		$is_app = in_array($is_app, $allowed_apps) ? $is_app : 'notapp';
		cookie('isapp', $is_app);
		$this->_isapp = ( $is_app == 'notapp' ? false : true );
		$this->assign('isapp', $is_app);
		// 为可用的主题模版列表
		$themes = array('v1');
		// 通用参数，可强行指定要使用的主题模版
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

		// 基本页页属性
		$this->pageinfo = array(
			'title' => '首页'.' - '.$this->site,
			'keywords' => '乐居,知识,百科,词条,地产,企业,名人,政策,关键词,解读',
			'description' => '乐居知识系统知识子系统介绍信息',
		);

		// 自动加载前端处理逻辑
		$Device = ucfirst($this->_device);
		// $this->lFront = D( $Device.'Page', 'Logic');
		// $this->lFront->setFlag('flush', $this->_flush['data'] ? false : true);

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
				 '<!--', PHP_EOL, '当前设备 : ', $this->_device, PHP_EOL, '-->', PHP_EOL,
				 '<!--', PHP_EOL, '当前主题 : ', $this->_theme, PHP_EOL, 
				 '可用主题: ', print_r($themes, true), PHP_EOL, '-->', PHP_EOL,
				 '<!--', PHP_EOL, '当前城市 : ', print_r($this->_city, true), PHP_EOL, '-->', PHP_EOL,
				 PHP_EOL;
		}
		return true;
	}

	public function info() {
		if ( $this->_debug ) {
			echo PHP_EOL, '$SERVER', PHP_EOL, var_export($_SERVER, true), PHP_EOL,
				 PHP_EOL, '$ENV', PHP_EOL, var_export($_ENV, true), PHP_EOL,
				 PHP_EOL, 'CONFIG', PHP_EOL, var_export(C(), true), PHP_EOL,
				 PHP_EOL;
		}
	}


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

	// 设置页面 SEO 信息
	public function setPageInfo( $info=array() ) {
		$pageinfo = array_merge($this->pageinfo, $info);
		$this->assign('pageinfo', $pageinfo);
	}
	public function setStatsCode( $info=array() ) {
		$statscode = array(
			'city' => 'quanguo',
			'level1_page' => '',
			'level2_page' => '',
			'level3_page' => '',
			'custom' => '',
			'news_source' => '',
		);
		$statscode = array_merge($statscode, $info);
		$this->assign('statscode', $statscode);
	}

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

	protected function show_error( $msg='非常抱歉，无法打开页面' ) {
		$device = $this->_device=='pc' ? 'PC' : 'TOUCH';

		$redis = S(C('REDIS'));
		$key = 'QA:ERRORPAGE:'.$device.':DATA';
		$expire = 300;
		$cache_data = $redis->get($key);
		if ( !$cache_data || $this->_flush['data'] ) {
			if ( $device == 'PC' ) {
				// PC 版页面，乐居标准头尾模版读取
				$page = D('Front', 'Logic', 'Common'); 
				$cache_data['tpl'] = $page->getPCPublicTemplate($flush);

				// pc 页面，公用头部显示热搜关键词和知识列表
				$cities = C('CITIES.CMS');
				$city_code = I('get.city', '', 'clear_all');
				$city_code = $city_code == '' ? cookie('citypub') : $city_code;
				// 统一城市信息
				if ( !array_key_exists($city_code, $cities) ) {
					$city_code = 'bj';
				}
				$city = $cities[$city_code];
				$city['code'] = $city_code;
				$cache_data['city'] = $city;

				// 栏目信息列表
				$lCate = D('Cate','Logic','Common');
				$cache_data['cateid'] = $lCate->getFirstTopCateid();
				$cache_data['cate_all'] = $lCate->getIndexTopCategories();

				// 推荐信息列表
				$result = $page->getSuggest('', $city['cn'], $city['code']);
				$cache_data['result'] = $result;
			}
			if ( $device=='TOUCH' ) {
				// 移动端异常页面处理
				$cities = C('CITIES.ALL');
				$city_code = I('get.city', '', 'trim,strip_tags,htmlspecialchars');
				$city_code = $city_code == '' ? cookie('B_CITY') : $city_code;
				// 统一城市信息
				if ( !array_key_exists($city_code, $cities) ) {
					$city_code = 'bj';
				}
				$city = $cities[$city_code];
				$city['code'] = $city_code;
				$cache_data['city'] = $city;

				// 推荐信息列表
				$page = D('Front', 'Logic', 'Common'); 
				$result = $page->getSuggest('', $city['cn'], $city['code']);
				$cache_data['result'] = $result;
			}
			$redis->setEx($key, $expire, json_encode($cache_data));
			$cache_data['cached'] = false;
		} else {
			$cache_data['cached'] = true;
		}

		$cache_data['device'] = $this->_device;
		foreach ( $cache_data as $key => $data ) {
			$this->assign($key, $data);
		}

		layout(false);
		$this->assign('error_message', $msg);
		$this->display('Public/error');
		exit;
	}
	protected function ajax_error($msg) {
		$result = array('status'=>false,'reason'=>trim($msg));
		$this->ajax_return($result);
	}
	protected function ajax_return($data) {
		set_ajax_output(true);
		echo json_encode($data);
		exit;
	}
}