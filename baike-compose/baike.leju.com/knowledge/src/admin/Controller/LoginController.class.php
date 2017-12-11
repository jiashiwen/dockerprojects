<?php
/**
 * 统一登录
 * @author Yangyang <yangyang13@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class LoginController extends BaseController {
	/**
	 * 统一登录平台登录
	 */
	public function index()
	{
		$admin_permit = I('get.admin_permit');
		$passport_id = $this->checkPermitCookie($this->_auth_key, $admin_permit);
		if ( intval($passport_id) ) {
			$valids = array();
			parse_str($admin_permit, $valids);
			$valid_code = $valids['permit'];
			// 设置当前浏览器中的登录检验
			$opt = array(
				'path' => '/',
				'expire' => 7200,
				'domain' => 'leju.com',
			);
			cookie('permit', $valid_code, $opt);
			// 管理员登录缓存
			// 1. 用于判断管理员是否重复登录，包括同主机不同浏览器登录或同一帐号多点登录判断
			// 2. 用于判断管理员登录是否超时
			$key = 'ADMIN:LOGIN:'.$passport_id;
			$cacher = S(C('REDIS'));
			$cacher->SetEx($key, $this->_login_expire, $valid_code);

			$mA = D('admins', 'Model', 'Common');
			$userinfo = $mA->where(array('passport_id'=>$passport_id))->find();
			if ( $userinfo ) {
				$uid = intval($userinfo['id']);
				// 创建审计日志可读文本
				$log_note = array(
					'status' => true, // 操作执行状态
					'reason' => '',	// 错误时的原因
					'actor' => '',	// 操作者默认为系统
					'actorid' => 0,	// 操作者默认为系统
					'id' => $uid,	// 登录的管理员
					'user' => trim($userinfo['truename']), // 登录的用户姓名
					'passportid' => $passport_id, // 登录的帐号编号
					'passportname' => trim($userinfo['passport_name']), // 登录的帐号名
				);
				$log_actid = ADMIN_LOGIN_ACT;

				if($userinfo['status'] == '1') {
					$userinfo['login_time'] = time();
					$this->writeCookie($userinfo);
					$mA->where(array('passport_id'=>$userinfo['passport_id']))->data(array('login_time'=>$userinfo['login_time']))->save();
				} else {
					$error_msg = '您的账户已注销';
					// 删除异常
					$log_note['status'] = false;
					$log_note['reason'] = $error_msg;
					$this->error($error_msg);
				}
				// 记录审计日志
				$this->_logger->addLog(0, $log_actid, $uid, json_encode($log_note));
			} else {

				//根据UID获取用户信息入库
				$userinfo = $this->getUserInfo($passport_id);
				if ( $userinfo ) {
					$this->registry($userinfo);
					$uid = intval($userinfo['id']);
					// 创建审计日志可读文本
					$log_note = array(
						'status' => true, // 操作执行状态
						'reason' => '',	// 错误时的原因
						'actor' => '',	// 操作者默认为系统
						'actorid' => 0,	// 操作者默认为系统
						'id' => $uid,	// 登录的管理员
						'user' => trim($userinfo['truename']), // 登录的用户姓名
						'passportid' => $passport_id, // 登录的帐号编号
						'passportname' => trim($userinfo['passport_name']), // 登录的帐号名
					);
					$log_actid = ADMIN_SYNC_ACT;
					// 记录审计日志
					$this->_logger->addLog(0, $log_actid, $uid, json_encode($log_note));
					//给默认权限
					$userinfo['role_id'] = 0;
					$userinfo['login_time'] = NOW_TIME;
					$this->writeCookie($userinfo);
					$mA->where(array('passport_id'=>$userinfo['passport_id']))->data(array('login_time'=>$userinfo['login_time']))->save();
				}
			}
			redirect('http://'.DOMAIN_NAME);
		}
		else
		{
			redirect($this->_auth_host.'welcome/login?returnurl=http://'.DOMAIN_NAME);
		}
	}

	/*
	 * 统一平台推送用户信息入库
	 * 回调业务接口时，统一登录系统不会传递 city_en 参数
	 */
	public function receive()
	{
		$keys = I('post.keys');
		$datas = json_decode($_POST['datas'], JSON_UNESCAPED_UNICODE);

		$result = false;
		$valid_key = $this->get_cms_key($datas, $datas['passport_name']);

		if($valid_key == $keys)
		{
			// 重新读取用户信息
			$userinfo = $this->getUserInfo($datas['passport_id']);
			$datas['city_en'] = isset($userinfo['city_en']) ? $userinfo['city_en'] : '';

			//①判断库里是否有用户 如果有 更新
			//②判断库里是否有用户 如果没 入库
			$mA = D('admins', 'Model', 'Common');
			$userinfo = $mA->where(array('passport_id'=>$datas['passport_id']))->find();
			if($userinfo)
			{
				$this->update($datas);
			}
			else
			{
				$this->registry($datas);
			}
			$result = true;
		}

		$this->ajax_return(array('result'=>$result));
	}

	//登出
	public function logout()
	{
		cookie('unitive_login',null);
		$re = $this->_auth_host.'welcome/login?returnurl=http://'.DOMAIN_NAME;
		$this->success('您已登出', $re);
	}

	/*
	 * 从统一平台获取用户信息
	 */
	private function getUserInfo($passport_id)
	{
		//正式阶段
		if($this->_env === 'publish')
		{
			$url = $this->_auth_host.'welcome/getuser';
			$userinfo = curl_post($url, array('key'=>$this->_auth_key, 'passport_id'=>$passport_id));
		}
		//开发阶段
		else
		{
			$url = 'http://10.207.0.186/welcome/getuser';
			$userinfo = curl_post($url, array('key'=>$this->_auth_key, 'uid'=>$passport_id),array('Host:test.admin.house.sina.com.cn'));
		}

		$userinfo = @json_decode($userinfo['result'],true);
		$userinfo = @$userinfo['userinfo'];
		$userinfo || $userinfo = false;

		return $userinfo;
	}

	/*
	 * 注册cookie
	 */
	private function writeCookie($userinfo)
	{
		$userinfo_encode = encode(json_encode($userinfo));
		cookie('unitive_login', $userinfo_encode);
		return true;
	}

	/*
	 * 用户数据更新
	 */
	private function update ( $userinfo ) {
		$mA = D('admins', 'Model', 'Common');

		$city = isset($userinfo['city_en']) ? strtolower($userinfo['city_en']) : '';
		if ( $city == 'all' ) {
			$city = '_';
		}
		//入库数据封装
		$sql_data = array(
			'passport_name'=>$userinfo['passport_name'],
			'truename'=>$userinfo['truename'],
			'em_email'=>$userinfo['employee_email'],
			'em_sn'=>$userinfo['employee_number'],
			'em_tel'=>$userinfo['telephone'],
			'mobile'=>$userinfo['mobile'],
			'city'=> $city,
			'update_time'=>time(),
			'status' => 1,
		);

		$ret = $mA->where(array('passport_id'=>$userinfo['passport_id']))->data($sql_data)->filter('strip_tags')->save();
		return $ret;
	}

	/*
	 * 用户注册入库
	 */
	private function registry ( $userinfo ) {
		$mA = D('admins', 'Model', 'Common');

		$city = isset($userinfo['city_en']) ? strtolower($userinfo['city_en']) : '';
		if ( $city == 'all' ) {
			$city = '_';
		}
		//入库数据封装
		$sql_data = array(
			'id'=>$userinfo['id'],
			'passport_id'=>$userinfo['passport_id'],
			'passport_name'=>$userinfo['passport_name'],
			'truename'=>$userinfo['truename'],
			'em_email'=>$userinfo['employee_email'],
			'em_sn'=>$userinfo['employee_number'],
			'em_tel'=>$userinfo['telephone'],
			'mobile'=>$userinfo['mobile'],
			'city'=>$city,
			'scope'=>$city,
			'create_time'=>time(),
			'status' => 1,
		);

		$ret = $mA->data($sql_data)->filter('strip_tags')->add();
		return $ret;
	}

	/*
	 * 检查用户登陆的cookie
	 * @param string $key 项目私有key
	 * @param string $str 非新浪域下cookie是作为参数传过来的,新浪域下不需要传这个参数
	 * @return 正确返回uid,错误返回false
	 */
	private function checkPermitCookie($key,$str='')
	{
		$arr = array();
		if(!empty($str))
		{
			parse_str(urldecode($str),$arr);
		}
		$permit = count($arr) > 0 ? json_encode($arr) : $_COOKIE['admin_permit'];
		if(!empty($permit) && !empty($key))
		{
			//验证cookie有效性
			$permit = json_decode($permit,true);
			if(is_array($permit) && isset($permit['uid'])){
				$pro_key = md5($key.'-'.$permit['lt'].'-'.$permit['uid']);
				$key = substr($pro_key,2,1) . substr($pro_key,7,1) . substr($pro_key,17,1) . substr($pro_key,25,1) . substr($pro_key,31,1);
				$key_pos = strpos($permit['permit'],$key);
				if($key_pos !== false && ($key_pos % 5) == 0  && (time()-$permit['lt']) < 72000)
				{
					//非新浪域下cookie是作为参数传过来的,验证正确后为本域种cookie,后续登陆验证用此cookie,有效期20个小时
					if(!isset($_COOKIE['admin_permit']) && !empty($str)) {
					//if ( !empty($arr) ) {
						setcookie('admin_permit', json_encode($arr), time()+72000,'/', 'leju.com');
					}
					return $permit['uid'];
				}
			}
		}
		return false;
	}

	/**
	 * 生成加密key
	 * @param array|string $data 数据
	 * @param string $key
	 * @return string
	 */
	private function get_cms_key($data,$key)
	{
		$string = '';
		if(is_array($data))
		{
			foreach ($data as $v)
			{
				$string .= $v;
			}
		}
		else
		{
			$string = $data;
		}

		return md5($key.$string.$key);
	}

}
