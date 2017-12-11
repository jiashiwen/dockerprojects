<?php
/**
 * 管理后台的控制器基础类
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;
class BaseController extends Controller {
	protected $_host = null;
	protected $_user = array();
	protected $_login_expire = 7200; // 2小时过期

	protected $_auth_key = '';
	protected $_auth_host = '';
	protected $_env = '';

	protected $_debug = false;

	protected $_logger = null;

	// 调试模式下的主机过滤，判断为开发或联调主机时，跳过身份验证
	protected $ignores = array(
		'ld.admin.baike.leju.com' => 'dev',
		'dev.admin.baike.leju.com' => 'test',
		'dev.admin.baike.leju.com:8080' => 'dev',
	);

	public function __construct() {
		parent::__construct();

		$debug = I('get.dbg', 0, 'intval');
		$this->_debug = ( $debug == 35940 ) ? true : false;

		// 匹配部署模式
		$this->_deploy = defined('APP_DEPLOY') ? APP_DEPLOY : 'dev';
		$this->_host = strtolower($_SERVER['HTTP_HOST']);
		// 测试环境中使用伪身份，便于调试
		if( array_key_exists($this->_host, $this->ignores) ) {
			$mode = $this->ignores[$this->_host];
			$this->_auth_key = '95d7d0a55d897beec95aa36ac9c1a64e';
			$this->_auth_host = "http://test.admin.house.sina.com.cn/";
			$this->_env = 'development';
		} else {
			$this->_auth_key = 'ae09afeec9200bc513892514a6263127';
			$this->_auth_host = "http://admin.house.sina.com.cn/";
			$this->_env = 'publish';
		}

		// 登录判断
		if(CONTROLLER_NAME !== 'Login') {
			$this->checkLogin();
		}
		$this->_logger = D('Adminlogs', 'Model', 'Common');
	}

	/*
	 * 登录验证
	 */
	protected function checkLogin() {
		if ( !empty($this->_user) )
			return true;

		$login_page = $this->_auth_host.'welcome/login?returnurl=http://'.$this->_host;
		$login_info = cookie('unitive_login');
		$permit = cookie('permit');
		$userinfo = json_decode(decode($login_info), true);
		if ( empty($userinfo) || !$permit ) {
			$this->error('请先登录', $login_page);
		} elseif($userinfo['status'] != '1') {
			$this->error('您的账户已注销');
		}
		$passport_id = intval($userinfo['passport_id']);
		$key = 'ADMIN:LOGIN:'.$passport_id;
		$cacher = S(C('REDIS'));
		$_permit = $cacher->Get($key);
		if ( !$_permit ) {
			$this->error('主重新登录', $login_page);
		}
		// TODO: 在此添加强制退出提示，并跳转到登录页面
		$relogins = array(
			// 0 不需要重新登录
			1 => array(
				'code' => 1,
				'desc' => '您在房产百科管理系统的管理帐号权限有变更，请您重新登录以获取新的操作权限',
				'login' => true,
			),
			2 => array(
				'code' => 2,
				'desc' => '您的帐号在异地登录，请您重新登录。可能您的帐号泄漏，建议您更新密码。',
				'login' => true,
			),
			3 => array(
				'code' => 3,
				'desc' => '您的帐号被删除或被禁用，请联系管理员',
				'login' => false,
			),
		);
		$_code = 0;	// 模拟一个几点号在异地登录的状况
		if ( $permit!=$_permit ) {
			$_code = 2;
		}
		$ret = array();
		$ret['status'] = !!array_key_exists($_code, $relogins);	// 是否需要强制退出
		if ( $ret['status'] ) {
			$ret['reason'] = $relogins[$_code]['desc'];
			$ret['code'] = $_code;
			$ret['relogin'] = $relogins[$_code]['login'];
		}
		// var_dump($_code, $permit, $_permit, $ret); exit;
		if ( $ret['status'] ) {
			if ( $ret['relogin'] ) {
				$this->error($ret['reason'], $login_page);
			} else {
				$this->error($ret['reason']);
			}
		}

		$lR = D('Role', 'Logic', 'admin');
		$role = $lR->getRoleList();
		$userinfo['role_name'] = $role[$userinfo['role_id']]['name'] ? $role[$userinfo['role_id']]['name'] : '待分配';
		$this->_user = $userinfo;

		// 开发者权限追回
		$userinfo['_developer'] = $this->isDeveloper();
		$this->assign('userinfo', $userinfo);
	}

	/*
	 * 权限验证
	 * @document：如果有第2参数传入的话，会返回所有auth_id对应的权限
	 * @param: func(page|ajax)
	 */
	protected function checkAuthorization($route, $func='page', $id=null) {
		$authorities = $this->getAuthorities();
		// var_dump($role, $this->_user);exit;
		// 父权限验证
		if ( !array_key_exists($route, $authorities) ) {
			($func == 'ajax') || IS_AJAX ? $this->ajax_error('权限不够') : $this->error('权限不够');
		}

		// 子权限验证
		if ( !empty($id) ) {
			if(in_array($id, $authorities[$route])) {
				return $authorities[$route];
			} else {
				($func == 'ajax') || IS_AJAX ? $this->ajax_error('栏目权限不够') : $this->error('栏目权限不够');
			}
		}
		return true;
	}

	/**
	 * 操作权限验证
	 */
	protected function getAuthorities ( $role_id=false ) {
		$lR = D('Role', 'Logic', 'admin');
		$role = $lR->getRoleList();
		$role_id = ( $role_id===false ) ? $this->_user['role_id'] : intval($role_id);
		if ( array_key_exists($role_id, $role) ) {
			$authorities = json_decode($role[$role_id]['authorities'], true);
		} else {
			$authorities = array();
		}
		$this->authorities = $authorities;
		// 返回操作权限验证
		return $authorities;
	}

	/**
	 * 当前用户是否是开发人员
	 *
	 */
	protected function isDeveloper() {
		$email = trim($this->_user['em_email']);
		$developers = array('yongliang1@leju.com');
		$this->_user['_developer'] = !!in_array($email, $developers);
		return $this->_user['_developer'];
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