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

		$pagesize = 10;
		$page = I('get.page', '', 'intval') ? I('get.page', '', 'intval') : 1;
		$mail = I('get.mail');
		$role_id = I('get.role_id',0,'intval');
		$truename = I('get.truename', '', 'strip_tags');
		$ltime_s = I('get.ltime_s', '', 'strip_tags');
		$ltime_e = I('get.ltime_e', '', 'strip_tags');
		$city = I('get.city', '', 'strip_tags');

		//城市列表
		$cities = C('CITIES');
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
		if(empty($role_info))
		{
			ajax_error('id不存在');
		}

		$mA = D('admins', 'Model', 'Common');
		$ret = $mA->where(array('id'=>$uid))->data(array('role_id'=>$id))->save();
		if($ret)
		{
			ajax_succ();
		}
		else
		{
			ajax_error('与当前角色一致');
		}
	}
}