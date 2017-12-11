<?php
/**
 * 首页
 *
 */
namespace ask\Controller;
use Think\Controller;
class IndexController extends BaseController {

	public function index() {
		$lPage = D('Page', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->pc_index_logic();
		$this->setPageInfo($lPage->_getPageinfo('index'));
		$this->setStatsCode($lPage->_getStatsConfig('index'));
		$this->assign_all($binds);
		$this->display();
	}

	// pc版中的加载更多
	public function loadmore() {
		$page = I('get.page', 2, 'intval');
		$pagesize = I('get.pagesize', 3, 'intval');

		$binds = D('Page', 'Logic')->hot_answers($page, $pagesize);
		$this->assign('hot_answers', array('list'=>$binds['list']));
		layout(false);
		$html = $this->fetch('answers.hot');
		unset($binds['list']);
		$binds['html'] = $html;
		$this->ajax_return($binds);
	}

	public function info() {
		if ( !$this->_debug ) { exit; }
		echo 'index page';
		var_dump(I());

		$host = $_SERVER['HTTP_HOST'];
		echo '<h3>页面地址参考</h3><hr><br>', PHP_EOL;
		$links = array(
			'栏目内容列表页1' => url('list', array(123,1), 'pc', 'ask'),	// list
			'栏目内容列表页2' => url('list', array(123,2), 'pc', 'ask'),	// list

			'标签聚合页1' => url('agg', array(321,1), 'pc', 'ask'),		// tag agg
			'标签聚合页2' => url('agg', array(321,2), 'pc', 'ask'),		// tag agg

			'详情页1' => url('show', array(444,1), 'pc', 'ask'),	// show
			'详情页2' => url('show', array(444,2), 'pc', 'ask'),	// show

			'搜索页1' => url('search', array('小/区',1), 'pc', 'ask'),	// search
			'搜索页2' => url('search', array('小/区',2), 'pc', 'ask'),	// search

			'个人中心页 - 默认' => url('profile', array('',1), 'pc', 'ask'),	// search
			'个人中心页 - 默认 分页' => url('profile', array('',2), 'pc', 'ask'),	// search
			'个人中心页 - 我的推荐' => url('profile', array('recommends',1), 'pc', 'ask'),	// search
			'个人中心页 - 我的推荐 分页' => url('profile', array('recommends',2), 'pc', 'ask'),	// search
			'个人中心页 - 我的问题' => url('profile', array('questions',1), 'pc', 'ask'),	// search
			'个人中心页 - 我的问题 分页' => url('profile', array('questions',2), 'pc', 'ask'),	// search
			'个人中心页 - 我的回答' => url('profile', array('answers',1), 'pc', 'ask'),	// search
			'个人中心页 - 我的回答 分页' => url('profile', array('answers',2), 'pc', 'ask'),	// search
			'个人中心页 - 我的关注' => url('profile', array('attentions',1), 'pc', 'ask'),	// search
			'个人中心页 - 我的关注 分页' => url('profile', array('attentions',2), 'pc', 'ask'),	// search
			'个人中心页 - 待我解决' => url('profile', array('todo',1), 'pc', 'ask'),	// search
			'个人中心页 - 待我解决 分页' => url('profile', array('todo',2), 'pc', 'ask'),	// search


		);
		foreach ( $links as $name => $item ) {
			echo '<a href="', $item, '" target="_blank">', $name, '</a> : ', $item, '<br>', PHP_EOL;
		}
	}

	public function getcity() {
		if ( !$this->_debug ) { exit; }
		$location = getCookieLocation($this->_device);
		$result = array('status'=>true, 'location'=>$location);
		$this->ajax_return($result);
	}
}
