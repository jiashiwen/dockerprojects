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
	protected $site = '知识 - 乐居知识系统';
	protected $pageinfo = array(); // 基本页面属性
	protected $lFront = null;	// 前端逻辑对象
	protected $_flush = array();

	protected $member = null;
	public function __construct() {
		parent::__construct();

		// 开启自动跳转机制
		$autofit = !I('get.nofit', 0, 'intval');
		autofit($autofit);

		// 是否开启调试模式
		$this->_debug = I('dbg', 0, 'intval') === 35940 ? true : false;

		// 匹配部署模式
		$this->_deploy = defined('APP_DEPLOY') ? APP_DEPLOY : 'dev';

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

		if ( I('get.flush_data', 0, 'intval')==1 ) {
			$this->_flush['data'] = true;
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
			$city_code = $city_code == '' ? cookie('M_CITY') : $city_code;
		}

		// 基本页页属性
		$this->pageinfo = array(
			'title' => '首页'.' - '.$this->site,
			'keywords' => '乐居,知识,百科,词条,地产,企业,名人,政策,关键词,解读',
			'description' => '乐居知识系统知识子系统介绍信息',
		);

		// 自动加载前端处理逻辑
		$Device = ucfirst($this->_device);
		$this->lFront = D( $Device.'Page', 'Logic' );
		$this->lFront->setFlag('flush', I('get.nc', 0, 'intval')==0 ? false : true);

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


	protected function setStatCode ($scope=false, $cid=0, $type='other') {
		return false;// 暂时不启用
		$type = $type == 'detail' ? 'TOUCH_INFO' : 'TOUCH_CATE';
		// 统计代码配置
		$count_code = C('FRONT_BAIKE_COUNT_CODE');
		$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
		$level2_page = ($this->_device == 'pc') ? $count_code['PC_ALL'][$cid] : $count_code[$type][$cid];
		// 移动端 Touch 页，没有 level3 统计代码
		$level3_page = ($this->_device == 'pc') ? 'info' : '';
		$news_source = $scope == '_' ? '全国' : '';

		// @changelog : 2017-1-18: 所有统计城市代码按 city_en 参数代入 遇到内容为全国的数据，城市指定为 all
		$this->_city['stat'] = $detail['scope']=='_' ? 'all' : $this->_city['en'];
		$this->assign('city', $this->_city);

		$this->assign('level1_page', $level1_page);
		$this->assign('level2_page', $level2_page);
		$this->assign('level3_page', $level3_page);
		$this->assign('news_source', $news_source);

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