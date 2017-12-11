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
	// 调试模式下的主机过滤，判断为开发或联调主机时，跳过身份验证
	protected $ignores = array(
		'ld.admin.baike.leju.com' => 'dev',
		'dev.admin.baike.leju.com' => 'test',
		'dev.admin.baike.leju.com:8080' => 'dev',
	);

	public function __construct() {
		parent::__construct();

		$this->_host = strtolower($_SERVER['HTTP_HOST']);
		// 测试环境中使用伪身份，便于调试
		if( array_key_exists($this->_host, $this->ignores) )
		{
			$mode = $this->ignores[$this->_host];
			if ( APP_DEBUG===true && $mode=='dev' ) {
				$this->_user['role_id'] = 1;
			}
			$this->_auth_key = '95d7d0a55d897beec95aa36ac9c1a64e';
			$this->_auth_host = "http://test.admin.house.sina.com.cn/";
			$this->_env = 'development';
		}
		else
		{
			$this->_auth_key = 'ae09afeec9200bc513892514a6263127';
			$this->_auth_host = "http://admin.house.sina.com.cn/";
			$this->_env = 'publish';
		}

		//登录判断
		if(CONTROLLER_NAME !== 'Login')
		{
			$this->checkLogin();
		}
	}
	/*
	 * 登录验证
	 */
	protected function checkLogin()
	{
		if(empty($this->_user))
		{
			$login_info = cookie('unitive_login');
			$userinfo = json_decode(decode($login_info), true);
			if(empty($userinfo))
			{
				$re = $this->_auth_host.'welcome/login?returnurl=http://'.$this->_host;
				$this->error('请先登录', $re);
			}
			elseif($userinfo['status'] != '1')
			{
				$this->error('您的账户已注销');
			}

			$lR = D('Role', 'Logic', 'admin');
			$role = $lR->getRoleList();
			$userinfo['role_name'] = $role[$userinfo['role_id']]['name'] ? $role[$userinfo['role_id']]['name'] : '待分配';

			$this->_user = $userinfo;
			$this->assign('userinfo',$userinfo);
		}
	}

	/*
	 * 权限验证
	 * @document：如果有第2参数传入的话，会返回所有auth_id对应的权限
	 * @param: func(page|ajax)
	 */
	protected function checkAuthorization($route, $func = 'page', $id = null)
	{
		$lR = D('Role', 'Logic', 'admin');
		$role = $lR->getRoleList();
		$authorities = json_decode($role[$this->_user['role_id']]['authorities'], true);

		//父权限验证
		if(!array_key_exists($route, $authorities))
		{
			($func == 'ajax') ? ajax_error('权限不够') : $this->error('权限不够');
		}

		//子权限验证
		if(!empty($id))
		{
			if(in_array($id, $authorities[$route]))
			{
				return $authorities[$route];
			}
			else
			{
				($func == 'ajax') ? ajax_error('栏目权限不够') : $this->error('栏目权限不够');
			}
		}
	}

	protected function ajax_return($data) {
		set_ajax_output(true);
		echo json_encode($data);
		exit;
	}
}