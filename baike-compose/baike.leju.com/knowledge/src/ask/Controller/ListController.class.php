<?php
namespace ask\Controller;
use Think\Controller;
class ListController extends BaseController {

	public function index(){
		$lPage = D('Page', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->pc_list_logic();
		$this->setPageInfo($lPage->_getPageinfo('list', $binds));
		$this->setStatsCode($lPage->_getStatsConfig('list', $binds));
		$binds['_list_source_flag'] = 'pc_qa_list';
		$this->assign_all($binds);
		$this->display();
	}

	/**
	 * 乐道问答迭代 - 公司问答聚合页
	 * @date: 2017-10-16
	 */
	public function company() {
		layout(false);
		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$this->error('没有指定的公司企业');
		}
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		# 公司关注状态 / 公司问答列表的关注状态数据从用户中心获取
		$lPage = D('CompanyPage', 'Logic');
		$binds = $lPage->setFlush($this->_flush['data'])
				 ->setDevice($this->_device)
				 ->setUserid($this->_userid)
				 ->LogicTouchCompany($id);
		if ( !$binds ) {
			$this->error('您访问的公司不存在');
		}
		$binds['islogined'] = $this->_islogined;
		$this->setPageInfo($lPage->_getPageinfo('company', $binds));
		$this->setStatsCode($lPage->_getStatsConfig('company', $binds));
		//echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign_all($binds);
		$this->display();
	}
	/**
	 * 乐道问答 - 公司聚合页中加载更多的数据接口
	 */
	public function more_ldq() {
		if ( $this->_device=='pc' ) {
			$this->ajax_error('暂时仅支持移动端访问');
		}
		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('没有指定的公司企业');
		}
		$page = I('get.page', 1, 'intval');
		$page = $page <= 0 ? 1 : $page;
		$pagesize = I('get.pagesize', 10, 'intval');
		if ( $pagesize < 1 || $pagesize >50 ) {
			$pagesize = 10;
		}
		$order = I('get.order', 'latest', 'strtolower,trim');
		if ( !in_array($order, ['latest', 'essence']) ) {
			$order = 'latest';
		}

		# 公司关注状态 / 公司问答列表的关注状态数据从用户中心获取
		$lPage = D('CompanyPage', 'Logic');
		$binds = $lPage->setFlush($this->_flush['data'])
				 ->setDevice($this->_device)
				 ->setUserid($this->_userid)
				 ->LogicMoreQuestions($id, $page, $pagesize, $order);
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$result = ['status'=>true] + $binds;
		$this->ajax_return($result);
	}

	/**
	 * 人物问答迭代 - 人物问答聚合页
	 * @date: 2017-11-23
	 */
	public function person() {
		layout(false);
		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$this->error('没有指定的人物');
		}
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		# 公司关注状态 / 公司问答列表的关注状态数据从用户中心获取
		$lPage = D('PersonPage', 'Logic');
		$binds = $lPage->setFlush($this->_flush['data'])
				 ->setDevice($this->_device)
				 ->setUserid($this->_userid)
				 ->LogicTouchPerson($id);
		if ( !$binds ) {
			$this->error('您访问的人物不存在');
		}
		$binds['islogined'] = $this->_islogined;
		$this->setPageInfo($lPage->_getPageinfo('person', $binds));
		$this->setStatsCode($lPage->_getStatsConfig('person', $binds));
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign_all($binds);
		$this->display();
	}
	/**
	 * 乐道问答 - 公司聚合页中加载更多的数据接口
	 */
	public function more_pnq() {
		if ( $this->_device=='pc' ) {
			$this->ajax_error('暂时仅支持移动端访问');
		}
		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('没有指定的人物');
		}
		$page = I('get.page', 1, 'intval');
		$page = $page <= 0 ? 1 : $page;
		$pagesize = I('get.pagesize', 10, 'intval');
		if ( $pagesize < 1 || $pagesize >50 ) {
			$pagesize = 10;
		}
		$order = I('get.order', 'latest', 'strtolower,trim');
		if ( !in_array($order, ['latest', 'essence']) ) {
			$order = 'latest';
		}

		# 人物关注状态 / 人物问答列表的关注状态数据从用户中心获取
		$lPage = D('PersonPage', 'Logic');
		$binds = $lPage->setFlush($this->_flush['data'])
				 ->setDevice($this->_device)
				 ->setUserid($this->_userid)
				 ->LogicMoreQuestions($id, $page, $pagesize, $order);
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$result = ['status'=>true] + $binds;
		$this->ajax_return($result);
	}


	// pc版中的加载更多
	public function loadmore() {
		$page = I('get.page', 2, 'intval');
		$pagesize = I('get.pagesize', 10, 'intval');
		$lPage = D('Page', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setType('api')
				->pc_list_logic();
		$total = intval($binds['total']);
		unset($binds['total']);
		$binds['pager'] = array(
			'page' => $page,
			'pagesize' => $pagesize,
			'pagecount' => ceil($total/$pagesize),
			'total' => $total,
		);
		$binds['pager']['is_last'] = ( $page >= $binds['pager']['pagecount'] ) ? 1 : 0;
		$binds['_list_source_flag'] = 'pc_qa_list';
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
				->pc_list_logic();

		// 清理无用数据
		unset($binds['catetree']);
		unset($binds['cateinfo']);
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
			$item['url'] = url('show', array($item['id']), 'touch', 'ask');
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
