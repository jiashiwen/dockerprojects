<?php
/**
 * 角色控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class RolesController extends BaseController {
	/**
	 * 系统权限角色管理列表
	 */
	public function index(){
		//权限验证
		$this->checkAuthorization('role/list');

		$mA = D('admins', 'Model', 'Common');
		$mR = D('roles', 'Model', 'Common');
		$list = $mR->select();
		foreach($list as $k=>$v)
		{
			$list[$k]['icount'] = $mA->where(array('role_id'=>$v['id']))->count();
		}
		$this->assign('list',$list);
		$this->display();
	}

	/**
	 * 添加系统角色
	 */
	public function add(){
		//权限验证
		$this->checkAuthorization('role/add');

		$pageinfo = array(
			'crumb' => array(),
			'title' => '创建系统权限角色管理',
		);
		$this->assign('pageinfo', $pageinfo);

		if(IS_POST)
		{
			$data = array();
			$data['name'] = I('post.title');
			$data['city'] = I('post.city');
			$data['city'] && $data['city'] = json_decode($data['city']);

			if(is_array($data['city']) && !empty($data['city']))
			{
				$data['city'] = implode(',', $data['city']);
			}

			//处理auth集合
			$authorities = array();
			$auth = I('post.auth');
			foreach($auth as $v)
			{
				if(stripos($v, ':') !== false)
				{
					$t = explode(':', $v);
					$authorities[$t[0]][] = $t[1];
				}
				else
				{
					$authorities[$v] = NULL;
				}
			}
			$authorities && $data['authorities'] = json_encode($authorities);

			$mR = D('roles', 'Model', 'Common');
			$ret = $mR->data($data)->filter('strip_tags')->add();
			if($ret)
			{
				$lR = D('Role', 'Logic', 'admin');
				$lR->flushRoleList();

				ajax_succ();
			}
			else
			{
				ajax_error('添加失败');
			}
		}
		else
		{
			//权限列表
			$auth = C('AUTH_ROUTE');
			$this->assign('auth', $auth);

			//一级栏目列表
			$mC = D('Categories', 'Model', 'Common');
			$lm = $mC->getCateList(0);
			$this->assign('lm', $lm);

			$this->display('editor');
		}
	}

	/**
	 * 编辑系统角色
	 */
	public function edit(){
		//权限验证
		$this->checkAuthorization('role/edit');

		$pageinfo = array(
			'crumb' => array(),
			'title' => '编辑角色权限信息',
		);
		$this->assign('pageinfo', $pageinfo);

		$mR = D('roles', 'Model', 'Common');
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			$role_info = $mR->where(array('id'=>$id))->find();
			if(empty($role_info))
			{
				ajax_error('id不存在');
			}

			$data = array();
			$data['city'] = I('post.city');
			$data['city'] && $data['city'] = json_decode($data['city']);

			if(is_array($data['city']) && !empty($data['city']))
			{
				$data['city'] = implode(',', $data['city']);
			}

			//处理auth集合
			$authorities = $data['authorities'] = array();
			$auth = I('post.auth');
			foreach($auth as $v)
			{
				if(stripos($v, ':') !== false)
				{
					$t = explode(':', $v);
					$authorities[$t[0]][] = $t[1];
				}
				else
				{
					$authorities[$v] = NULL;
				}
			}
			$authorities && $data['authorities'] = json_encode($authorities);

			$ret = $mR->where(array('id'=>$role_info['id']))->data($data)->filter('strip_tags')->save();
			if($ret)
			{
				$lR = D('Role', 'Logic', 'admin');
				$lR->flushRoleList();

				ajax_succ();
			}
			else
			{
				ajax_error('保存失败');
			}
		}
		else
		{
			$id = I('get.id',0,'intval');
			$role_info = $mR->where(array('id'=>$id))->find();
			if(empty($role_info))
			{
				redirect('/Roles/Add');
			}

			//权限列表
			$auth = C('AUTH_ROUTE');
			$this->assign('auth', $auth);

			//渲染城市
			$city = array();
			$cities = C('CITIES')['ALL'];
			if(!empty($role_info['city']))
			{
				$role_info['city'] = explode(',', $role_info['city']);
				foreach($role_info['city'] as $v)
				{
					if($v == '_')
					{
						$city['_'] = '全国';
						continue;
					}
					$city[$v] = $cities[$v]['cn'];
				}
			}
			$role_info['city'] = $city;

			//渲染权限
			$authorities = array();
			$temp_auth = json_decode($role_info['authorities'],true);
			foreach($temp_auth as $k=>$v)
			{
				if(is_array($v) && !empty($v))
				{
					foreach($v as $b)
					{
						array_push($authorities, $k.':'.$b);
					}
				}
				else
				{
					array_push($authorities, $k);
				}
			}

			//一级栏目列表
			$mC = D('Categories', 'Model', 'Common');
			$lm = $mC->getCateList(0);
			$this->assign('lm', $lm);

			$this->assign('id', $id);
			$this->assign('authorities',$authorities);
			$this->assign('role_info', $role_info);
			$this->display('editor');
		}
	}

	public function ajax_get_cities()
	{
		//城市列表
		$result = array();
		$cities = C('CITIES');

		array_push($result, array('value'=>'全国','cid'=>'_'));

		foreach($cities['ALL'] as $k=>$v)
		{
			array_push($result, array('value'=>$v['cn'],'cid'=>$k));
		}

		ajax_succ($result);
	}
}