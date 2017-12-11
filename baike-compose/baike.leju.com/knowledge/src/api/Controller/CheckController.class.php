<?php
/**
 * 重新发布知识内容
 * @author Robert <yongliang1@leju.com>
 */
namespace api\Controller;
use Think\Controller;

class CheckController extends Controller {

	public function index(){
		send_http_status(404);
		exit;
	}

	public function checkfix() {
		// 设置城市
		$scope = I('get.city', '', 'trim');
		if ( $scope!='' ) {
			$cities = C('CITIES.ALL');
			$cities['_'] = array(
				'l' => 'Q',
				'en' => '_',
				'cn' => '全国',
				'py' => 'quanguo',
			);
			if ( !array_key_exists($scope, $cities) ) {
				$scope = '';
			}
		}

		// 时间范围限制 默认为昨天一天内的发布数据 最多提供7天内的数据重推
		$days = I('get.d', 1, 'intval');
		if ( $days<=0 ) {
			$days = 1;
		}
		if ( $days>31 ) {
			$days = 7;
		}

		$group_num = 20;

		$to = strtotime(date('Y-m-d', NOW_TIME));
		$from = strtotime('-'.$days.' days', $to);
		$knowledge = D('Knowledge', 'Model', 'Common');
		$fields = 'id';
		$where = array(
			'status' => 9,
			'version' => array(array('egt', $from), array('lt', $to),),
		);
		if ( $scope != '' ) {
			$where['scope'] = $scope;
		}
		// var_dump($where);
		$lists = $knowledge->field($fields)->where($where)->select();
		$ids = array();
		foreach ( $lists as $i => $item ) {
			array_push($ids, $item['id']);
		}
		$chunk_ids = array_chunk($ids, 500);

		$lSearch = D('Search', 'Logic', 'Common');
		$es_ids = array();
		foreach ( $chunk_ids as $i => $g ) {
			$_ids = implode(',', $g);
			$page = 1;
			$pagesize = count($g);
			$keyword = '';
			$opts = array(array($_ids,'_id'));
			if ( $scope!='' ) {
				$opts[] = array($cities[$scope]['cn'], '_scope');
			}
			$prefix = array();
			$order = array('_doccreatetime', 'desc');
			$fields=array('_id','_title','_scope','_docupdatetime');
			$ds = 0;
			$business = 'knowledge';

			$ret = $lSearch->select($page,$pagesize,$keyword,$opts,$prefix,$order,$fields,$ds,$business);

			foreach ( $ret['list'] as $j => $esitem ) {
				if ( $esitem['_docupdatetime']>0 ) {
					array_push($es_ids, $esitem['_id']);
				}
			}
		}

		// 索引数据集合查询的数据量不等于数据库id量 (索引数据量应该小于数据库id量)
		// 此时需要同步数据
		if ( count($es_ids) != count($ids) ) {

			$sync = array_diff($ids, $es_ids);
			$sync = array_chunk($sync, $group_num);

			// $sync2 = array();
			// foreach ( $ids as $i => $id ) {
			// 	if ( !in_array($id, $es_ids) ) {
			// 		array_push($sync2, $id);
			// 	}
			// }

			// var_dump($sync, $sync2);
			// exit;
			$publisher = D('BaikePublish', 'Logic', 'Common');
			$done = 0;
			foreach ( $sync as $group_id => $group ) {
				$cnt = count($group);
				$group = implode(',', $group);
				$where = array(
					'status' => 9,
					'id' => array('in', $group),
				);
				$lists = $knowledge->where($where)->select();
				$ret = $publisher->batchPublish($lists);
				// var_dump($group_id, $group, $lists);
				if ( $ret ) {
					$done += $cnt;
				}
			}
			echo '[INFO] 知识数据需要修正', PHP_EOL, '共修复 ', $done, ' 条知识数据', PHP_EOL;
		} else {
			// 数据正常
			echo '[INFO] 知识数据状态正常，无需修复！', PHP_EOL;
		}

	}


	// 重新发布数据
	public function republish() {
		$list = I('post.ids','','trim');
		if ( $list=='' ) {
			echo '请指定要同步的知识编号';
			exit;
		}

		$list = explode(',', $list);
		foreach ( $list as $i => &$id ) {
			$id = intval($id);
			if ( $id <= 0 ) {
				unset($list[$i]);
			}
		}
		$knowledge = D('Knowledge', 'Model', 'Common');
		$where = array(
			'status' => 9,
			'id' => array('in', $list),
		);
		$lists = $knowledge->where($where)->select();

		$publisher = D('BaikePublish', 'Logic', 'Common');
		$ret = $publisher->batchPublish($lists);
		var_dump($ret);
	}
}