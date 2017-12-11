<?php
/**
 * 审计日志控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class LogsController extends BaseController {

	/**
	 * 审计日志查看操作
	 */
	public function index() {
		//权限验证
		$this->checkAuthorization('adminlogs/list');

		// 搜索和分页逻辑
		$pagesize = 20;
		$page = I('get.page', 1, 'intval');
		$page = $page < 1 ? 1 : $page;

		$act = I('get.act', 0, 'intval');
		$admin = I('get.admin', '', 'trim,filterInput,clean_xss');
		$adminid = '';
		if ( $admin != '' ) {
			$mAdmins = D('admins', 'Model', 'Common');
			$where = array(
				'truename'=>$admin,
			);
			$admin = $mAdmins->where($where)->find();
			if ( $admin ) {
				$adminid = intval($admin['id']);
				$admin = trim($admin['truename']);
			} else {
				$adminid = '';
				$admin = '';
			}
		}
		// 取 1 天的数据
		$ltime = I('get.ltime', '', 'strip_tags');
		// $ltime_s = I('get.ltime_s', '', 'strip_tags');
		// $ltime_e = I('get.ltime_e', '', 'strip_tags');
		$ltime_s = vaild_datestr($ltime, 'Y-m-d');
		$ltime_e = vaild_datestr($ltime_s, 'Y-m-d 23:59:59');

		$params = array(
			'act' => $act,
			'admin' => $admin,
			'adminid' => $adminid,
			'ltime' => $ltime,
		);
		$this->assign('params', $params);

		// 所有操作类型字典加载
		$acts = $this->_logger->initActs();
		$this->assign('acts', $acts);

		$mAdminlogs = D('Adminlogs', 'Model', 'Common');
		$where = array( 'act'=>array('gt', 0), );
		if ( $act!=0 ) { $where['act'] = $act; }
		if ( $adminid!='' ) { $where['admin'] = $adminid; }
		if ( $ltime!='' ) { $where['ctime'] = array('between', array($ltime_s, $ltime_e)); }
		$list = $mAdminlogs->where($where)->page($page, $pagesize)->order('id DESC')->select();
		$logs = array();
		foreach ( $list as $i => $record ) {
			$record['note'] = json_decode($record['note'], true);
			$msg = actMsg($record);
			// echo PHP_EOL, $msg, PHP_EOL, print_r($record, true), PHP_EOL, PHP_EOL;
			$actor = ( isset($record['note']['actor']) && trim($record['note']['actor'])!='' )
					 ?
					 trim($record['note']['actor'])
					 :
					 '[系统]';
			$logs[] = array(
				'id' => $record['id'],
				'actor' => $actor,
				'actorid' => $record['note']['actorid'],
				'act' => $record['act'],
				'actname' => $acts[$record['act']]['NAME'],
				'success' => !!$record['note']['status'],
				'msg' => $msg,
			);
		}
		// var_dump($where, $page, $pagesize, $logs);
		$this->assign('logs', $logs);
		$this->display();
	}
}