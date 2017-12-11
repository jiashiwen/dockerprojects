<?php
/**
 * 管理员控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class AdminController extends BaseController {
	/**
	 * 系统管理用户管理列表
	 */
	public function index(){
		//权限验证
		$this->checkAuthorization('role/userlist');

		$pagesize = 20;
		$page = I('get.page', '', 'intval') ? I('get.page', '', 'intval') : 1;
		$mail = I('get.mail');
		$role_id = I('get.role_id',0,'intval');
		$truename = I('get.truename', '', 'strip_tags');
		$ltime_s = I('get.ltime_s', '', 'strip_tags');
		$ltime_e = I('get.ltime_e', '', 'strip_tags');
		$city = I('get.city', '', 'strip_tags');

		//城市列表
		$cities = C('CITIES');
		$cities['ALL']['_'] = array(
			'en' => '_',
			'cn' => '全国',
			'py' => 'quanguo',
		);
		ksort($cities['ALL']);
		$this->assign('cities', $cities);

		//联合查询
		$mA = D('admins', 'Model', 'Common');
		$mR = D('roles', 'Model', 'Common');

		$where = array();
		$mail && $where['admins.em_email'] = $mail;
		$role_id && $where['roles.id'] = $role_id;
		$truename && $where['admins.truename'] = $truename;
		($ltime_s && !$ltime_e) && $where['admins.login_time'] = array('EGT', strtotime($ltime_s));
		(!$ltime_s && $ltime_e) && $where['admins.login_time'] = array('ELT',strtotime($ltime_e));
		($ltime_s && $ltime_e) && $where['admins.login_time'] = array('BETWEEN',array(strtotime($ltime_s),strtotime($ltime_e)));

		//$sql = 'SELECT a.*,r.name,r.city FROM admins a left join roles r on a.role_id = r.id';
		$users = $mA->field('admins.*,roles.name as role_cn,roles.city')->join('roles ON admins.role_id = roles.id','LEFT')->where($where)->order('id ASC')->select();
		$total = count($users);

		//处理city
		if($city)
		{
			foreach($users as $k=>$v)
			{
				if(stripos($v['city'], $city) === false)
				{
					unset($users[$k]);
				}
			}
			$users = array_values($users);
			$total = count($users);
		}
		//处理分页
		if($page)
		{
			$users = array_slice($users, ($page-1)*$pagesize, $pagesize);
		}

		if($users)
		{
			foreach($users as $k=>$v)
			{
				//获取权限中的城市并转换成中文
				$city_cn = explode(',', $v['city']);
				foreach($city_cn as &$g)
				{
					if($g == '_')
					{
						$g = '全国';
					}
					else
					{
						$g = $cities['ALL'][$g]['cn'];
					}
				}
				$city_cn = implode(',', $city_cn);
				$users[$k]['city'] = $city_cn;
			}

			//分页模块
			if($total)
			{
				//封装linkopts
				$linkopts = array();
				$mail && array_push($linkopts, "mail={$mail}");
				$role_id && array_push($linkopts, "role_id={$role_id}");
				$truename && array_push($linkopts, "truename={$truename}");
				$ltime_s && array_push($linkopts, "ltime_s={$ltime_s}");
				$ltime_e && array_push($linkopts, "ltime_e={$ltime_e}");
				$city && array_push($linkopts, "city={$city}");
				$linkstring = !empty($linkopts) ? '/Admin/?page=#&'.implode('&',$linkopts) : '/Admin/?page=#';
				$opts = array(
					'first' => true, //首页
					'last' => true,	//尾页
					'prev' => true, //上一页
					'next' => true, //下一页
					'number' => 5, //显示页码数
					'linkstring' => $linkstring
				);
				$pager = pager($page, $total, $pagesize, $opts);
				$this->assign('pager', $pager);
			}

			$this->assign('users', $users);
		}

		$role = $mR->select();
		$this->assign('role', $role);
		$this->assign('role_id', $role_id);
		$this->assign('city',$city);
		$this->assign('mail',$mail);
		$this->assign('truename',$truename);
		$this->assign('ltime_s',$ltime_s);
		$this->assign('ltime_e',$ltime_e);

		$this->display();
	}

	/**
	 * 删除用户
	 * @return ajax - json
	 */
	public function del(){
		//权限验证
		$this->checkAuthorization('role/userdel','ajax');

		$uid = I('post.uid',0,'intval');
		$mA = D('admins', 'Model', 'Common');
		$ret = $mA->where(array('id'=>$uid))->data(array('status'=>0))->save();
		if($ret)
		{
			ajax_succ();
		}
		else
		{
			ajax_error('删除失败');
		}
	}

	/**
	 * 恢复用户
	 * @return ajax - json
	 */
	public function undel(){
		//权限验证
		$this->checkAuthorization('role/userdel','ajax');

		$uid = I('post.uid',0,'intval');
		$mA = D('admins', 'Model', 'Common');
		$ret = $mA->where(array('id'=>$uid))->data(array('status'=>1))->save();
		if($ret)
		{
			ajax_succ();
		}
		else
		{
			ajax_error('恢复失败');
		}
	}

	/**
	 * 更改用户组
	 * @return ajax - json
	 */
	public function change_role(){
		//权限验证
		$this->checkAuthorization('role/useredit','ajax');

		$id = I('post.id',0,'intval');
		$uid = I('post.uid',0,'intval');

		$mR = D('roles', 'Model', 'Common');
		$role_info = $mR->where(array('id'=>$id))->find();
		if ( empty($role_info) ) {
			ajax_error('id不存在');
		}

		$mA = D('admins', 'Model', 'Common');
		$where = array('id'=>$uid);
		$admin = $mA->where($where)->find();
		if ( $admin ) {
			$ret = $mA->where(array('id'=>$uid))->data(array('role_id'=>$id))->save();
			if($ret)
			{
				$to = $admin['em_email'];
				$title = '[知识百科系统]您的可操作的权限有更新';
				$content = $admin['truename'].' 您好：'.PHP_EOL
					.'您在知识百科系统中的可操作的权限有更新，您现在的角色是'.$role_info['name'].'!'.PHP_EOL
					.'请重新登录您的知识百科系统进行查看！'.PHP_EOL;
				$lEms = D('Ems', 'Logic', 'Common');
				$ret = $lEms->sendMail($to, $title, $content);
				ajax_succ('角色成功变更为'.$role_info['name']);
			} else {
				ajax_error('与当前角色一致');
			}
		} else {
			ajax_error('指定的操作用户不存在！');
		}
	}

	/**
	 * 更改用户城市权限
	 * @param uid int 用户编号
	 * @param scope string 城市代码 city_en
	 * @return ajax - json
	 */
	public function change_city() {
		//权限验证
		// $this->checkAuthorization('role/useredit','ajax');

		$scope = I('post.scope','','trim,strtolower');
		$uid = I('post.uid',0,'intval');

		$cities = C('CITIES.ALL');
		$cities['_'] = array(
			'en' => '_',
			'cn' => '全国',
			'py' => 'quanguo',
		);
		if ( !array_key_exists($scope, $cities) ) {
			ajax_error('城市不存在！');
		}

		$mA = D('admins', 'Model', 'Common');
		$where = array('id'=>$uid);
		$admin = $mA->where($where)->find();
		if ( $admin ) {
			$ret = $mA->where($where)->data(array('scope'=>$scope))->save();
			if( $ret ) {
				$to = $admin['em_email'];
				$title = '[知识百科系统]您的可操作城市权限有更新';
				$content = $admin['truename'].' 您好：'.PHP_EOL
					.'您在知识百科系统中的可操作城市权限有更新，现在的城市操作权限是'.$cities[$scope]['cn'].'!'.PHP_EOL
					.'请重新登录您的知识百科系统进行查看！'.PHP_EOL;
				$lEms = D('Ems', 'Logic', 'Common');
				$ret = $lEms->sendMail($to, $title, $content);
				ajax_succ('用户城市权限成功更新为'. $cities[$scope]['cn']);
			} else {
				ajax_error('用户城市权限不需要更新！');
			}
		} else {
			ajax_error('指定的操作用户不存在！');
		}
	}
}