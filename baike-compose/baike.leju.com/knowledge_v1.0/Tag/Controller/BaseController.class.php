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
	
	//词条分类
	protected $_category = array("0" => "人物", "1" => "机构");

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