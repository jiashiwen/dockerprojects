<?php
namespace ask\Controller;
use Think\Controller;
class AggController extends BaseController {

	public function index(){
		$lPage = D('Page', 'Logic');
		$binds = $lPage
			->setFlush($this->_flush['data'])
			->setDevice($this->_device)
			->pc_agg_logic();
		$this->setPageInfo($lPage->_getPageinfo('agg', $binds['taginfo']));
		$this->setStatsCode($lPage->_getStatsConfig('agg', $binds['taginfo']));
		$this->assign_all($binds);
		$this->display();
	}

	// pc版中的加载更多
	public function loadmore() {
		$page = I('get.page', 2, 'intval');
		$pagesize = I('get.pagesize', 10, 'intval');

		$binds = D('Page', 'Logic')
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setType('api')
				->pc_agg_logic();
		$total = intval($binds['total']);
		unset($binds['total']);
		$binds['pager'] = array(
			'page' => $page,
			'pagesize' => $pagesize,
			'pagecount' => ceil($total/$pagesize),
			'total' => $total,
		);
		$binds['pager']['is_last'] = ( $page >= $binds['pager']['pagecount'] ) ? 1 : 0;
		$this->assign_all($binds);
		layout(false);
		$html = $this->fetch('Public:list');
		unset($binds['list']);
		$binds['html'] = $html;
		$this->ajax_return($binds);
	}

	public function page() {
		$lPage = D('Page', 'Logic');
		$binds = $lPage
			->setFlush($this->_flush['data'])
			->setDevice($this->_device)
			->setType('api')
			->pc_agg_logic();
		// 清理无用数据
		unset($binds['catetree']);
		unset($binds['taginfo']);
		unset($binds['latest_answers']);
		if ( !isset($binds['list']) ) {
			$binds['list'] = array();
		}
		unset($binds['hot_tags']);

		// 更新 pager
		$pager = &$binds['pager'];
		$filter = array('page'=>'', 'pagesize'=>'', 'total'=>'', 'count'=>'', );
		$pager = array_intersect_key($pager, $filter);
		$pager['hasnext'] = ( $pager['count'] > $pager['page'] ) ? 1 : 0;
		// 更新列表
		$list = &$binds['list'];
		$fields = array(
			'id'=>'', 'title'=>'', 'tags'=>'', 'scope'=>'', 'catepath'=>'', 'ctime'=>'', 'cateid'=>'',
			'anonymous'=>'', 'usernick'=>'', 'i_hits'=>'', 'i_replies'=>'', 'i_attention'=>'',
			'tagsinfo'=>'', 'catenamepath'=>'', 'catesinfo'=>'',
		);
		foreach ( $list as $i => &$item ) {
			$item = array_intersect_key($item, $fields);
			$item['dsp_time'] = date('m-d', $item['ctime']);
			$item['url'] = url('show', array($item['id'], $item['cateid']), 'touch', 'ask');
			// 栏目信息
			$item['catesinfo'] = array();
			$_cates = explode('-', str_replace('0-', '', $item['catepath']));
			foreach ( $_cates as $_i => $_cid ) {
				array_push($item['catesinfo'], array(
					'id' => $_cid,
					'name' => $item['catenamepath'][$_cid],
					'url' => url('list', array($_cid), 'touch', 'ask'),
				));
			}
			// 标签列表
			foreach ( $item['tagsinfo'] as $k => $tag ) {
				$item['tagsinfo'][$k]['url'] = url('agg', array($tag['id']), 'touch', 'ask');
				unset($item['tagsinfo'][$k]['i_total']);
				unset($item['tagsinfo'][$k]['source']);
				unset($item['tagsinfo'][$k]['status']);
			}
		}
		$this->ajax_return($binds);
	}
}
