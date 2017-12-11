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
		if(intval($passport_id))
		{
			$mA = D('admins', 'Model', 'Common');
			$userinfo = $mA->where(array('passport_id'=>$passport_id))->find();
			if($userinfo)
			{
				if($userinfo['status'] == '1')
				{
					$userinfo['login_time'] = time();
					$this->writeCookie($userinfo);
					$mA->where(array('passport_id'=>$userinfo['passport_id']))->data(array('login_time'=>$userinfo['login_time']))->save();
				}
				else
				{
					$this->error('您的账户已注销');
				}
			}
			else
			{
				//根据UID获取用户信息入库
				$userinfo = $this->getUserInfo($passport_id);
				if($userinfo)
				{
					$this->registry($userinfo);

					//给默认权限
					$userinfo['role_id'] = 0;
					$userinfo['login_time'] = time();
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
	 */
	public function receive()
	{
		$keys = I('post.keys');
		$datas = json_decode($_POST['datas'], JSON_UNESCAPED_UNICODE);

		$result = false;
		$valid_key = $this->get_cms_key($datas, $datas['passport_name']);

		if($valid_key == $keys)
		{
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

		echo json_encode(array('result'=>$result));
		exit;
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
		cookie('unitive_login',$userinfo_encode);
		return true;
	}

	/*
	 * 用户数据更新
	 */
	private function update($userinfo)
	{
		$mA = D('admins', 'Model', 'Common');

		//入库数据封装
		$sql_data = array(
			'passport_name'=>$userinfo['passport_name'],
			'truename'=>$userinfo['truename'],
			'em_email'=>$userinfo['employee_email'],
			'em_sn'=>$userinfo['employee_number'],
			'em_tel'=>$userinfo['telephone'],
			'mobile'=>$userinfo['mobile'],
			'update_time'=>time()
		);

		$ret = $mA->where(array('passport_id'=>$userinfo['passport_id']))->data($sql_data)->filter('strip_tags')->save();
		return $ret;
	}

	/*
	 * 用户注册入库
	 */
	private function registry($userinfo)
	{
		$mA = D('admins', 'Model', 'Common');

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
			'create_time'=>time()
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
	                if(!isset($_COOKIE['admin_permit']) && !empty($str))
	                {
	                    setcookie('admin_permit',json_encode($arr),time()+72000,'/', 'leju.com');
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
