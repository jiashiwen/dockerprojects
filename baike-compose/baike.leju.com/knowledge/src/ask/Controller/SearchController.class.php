<?php
/**
 * 搜索逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace ask\Controller;
use Think\Controller;
class SearchController extends BaseController {

	public function index(){
		# 页面参数
		$keyword = I('get.k', '', 'trim');
		$length = abslength($keyword);
		if ( $length > 50 ) {
			$this->show_error('您输入的搜索关键词过长，请换一个要搜索的关键词再试试');
		}
		// if($keyword && !preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\'\"“”‘’，\??？\s,]+$/u",$keyword)) {
		// 	$this->error('关键字含有非法字符');
		// }
		$lPage = D('Page', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->pc_search_logic($keyword);
		$this->setPageInfo($lPage->_getPageinfo('search', $binds['keyword']));
		$this->setStatsCode($lPage->_getStatsConfig('search', $binds['keyword']));
		$binds['_list_source_flag'] = 'pc_qa_search';
		$this->assign_all($binds);
		$this->display();
	}

	/**
	 * 乐道问答迭代 - 公司问答搜索结果页
	 * @date: 2017-10-16
	 */
	public function company() {
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		# 页面参数
		$keyword = I('get.k', '', 'trim,clear_all,clean_xss');
		$length = abslength($keyword);
		if ( $length > 50 ) {
			$this->show_error('您输入的搜索关键词过长，请换一个要搜索的关键词再试试');
		}

		$lPage = D('CompanyPage', 'Logic');
		if ( $keyword != '' ) {
			$page = 1;
			$pagesize = 10;
			$binds = $lPage
					->setFlush($this->_flush['data'])
					->setDevice($this->_device)
					->LogicTouchSearch($keyword, $page, $pagesize);
			// $this->setPageInfo($lPage->_getPageinfo('search', $binds['keyword']));
			$binds['islogined'] = $this->_islogined;
			$binds['opts'] = ['keyword'=>$keyword];
			$this->assign_all($binds);
		} else {
			$lPage->setFlush($this->_flush['data'])->setDevice($this->_device);
			$binds = [
				'opts' => ['keyword'=>''],
			];
		}
		$this->setStatsCode($lPage->_getStatsConfig('search', $binds));
		// echo PHP_EOL, '<!-- <pre>', PHP_EOL, print_r($binds, true), PHP_EOL, '</pre> -->', PHP_EOL;
		$this->display('company_result');
	}
	/**
	 * 乐道问答 - 搜索结果页中加载更多的数据接口
	 */
	public function more_lds() {
		if ( $this->_device=='pc' ) {
			$this->ajax_error('暂时仅支持移动端访问');
		}
		# 页面参数
		$keyword = I('get.k', '', 'trim,clear_all,clean_xss');
		$length = abslength($keyword);
		if ( $length > 50 ) {
			$this->show_error('您输入的搜索关键词过长，请换一个要搜索的关键词再试试');
		}

		$page = I('get.page', 1, 'intval');
		$page = $page <= 0 ? 1 : $page;
		$pagesize = I('get.pagesize', 10, 'intval');
		if ( $pagesize < 1 || $pagesize >50 ) {
			$pagesize = 10;
		}

		$lPage = D('CompanyPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->LogicTouchSearch($keyword, $page, $pagesize);
		$binds['opts'] = ['keyword'=>$keyword];
		// echo PHP_EOL, '<!-- <pre>', PHP_EOL, print_r($binds, true), PHP_EOL, '</pre> -->', PHP_EOL;exit;
		$result = ['status'=>true] + $binds;
		$this->ajax_return($result);
	}
	/**
	 * 乐道问答 - 搜索时的联想搜索接口
	 */
	public function ld_suggest() {
		if ( $this->_device=='pc' ) {
			$this->ajax_error('暂时仅支持移动端访问');
		}
		# 页面参数
		$keyword = I('get.k', '', 'trim,clear_all,clean_xss');
		$length = abslength($keyword);
		if ( $length < 1 || $length > 50 ) {
			$this->ajax_return(['status'=>true,'list'=>[]]);
		}

		$page = 1;
		$pagesize = 10;
		$lPage = D('CompanyPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->LogicTouchSuggest($keyword, $page, $pagesize);
		// $binds['opts'] = ['keyword'=>$keyword, 'page'=>$page, 'pagesize'=>$pagesize];
		// echo PHP_EOL, '<!-- <pre>', PHP_EOL, print_r($binds, true), PHP_EOL, '</pre> -->', PHP_EOL;exit;
		$result = ['status'=>true] + $binds;
		$this->ajax_return($result);
	}


	// // // // // // // // // // // // // // // // // // // // 
	/**
	 * 人物问答迭代 - 人物问答搜索结果页
	 * @date: 2017-11-23
	 */
	public function person() {
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		# 页面参数
		$keyword = I('get.k', '', 'trim,clear_all,clean_xss');
		$length = abslength($keyword);
		if ( $length > 50 ) {
			$this->show_error('您输入的搜索关键词过长，请换一个要搜索的关键词再试试');
		}

		$lPage = D('PersonPage', 'Logic');
		if ( $keyword != '' ) {
			$page = 1;
			$pagesize = 10;
			$binds = $lPage
					->setFlush($this->_flush['data'])
					->setDevice($this->_device)
					->LogicTouchSearch($keyword, $page, $pagesize);
			// $this->setPageInfo($lPage->_getPageinfo('search', $binds['keyword']));
			$binds['islogined'] = $this->_islogined;
			$binds['opts'] = ['keyword'=>$keyword];
			$this->assign_all($binds);
		} else {
			$lPage->setFlush($this->_flush['data'])->setDevice($this->_device);
			$binds = [
				'opts' => ['keyword'=>''],
			];
		}
		$this->setStatsCode($lPage->_getStatsConfig('search', $binds));
		// echo PHP_EOL, '<!-- <pre>', PHP_EOL, print_r($binds, true), PHP_EOL, '</pre> -->', PHP_EOL;
		$this->display('person_result');
	}
	/**
	 * 人物问答 - 搜索结果页中加载更多的数据接口
	 */
	public function more_pns() {
		if ( $this->_device=='pc' ) {
			$this->ajax_error('暂时仅支持移动端访问');
		}
		# 页面参数
		$keyword = I('get.k', '', 'trim,clear_all,clean_xss');
		$length = abslength($keyword);
		if ( $length > 50 ) {
			$this->show_error('您输入的搜索关键词过长，请换一个要搜索的关键词再试试');
		}

		$page = I('get.page', 1, 'intval');
		$page = $page <= 0 ? 1 : $page;
		$pagesize = I('get.pagesize', 10, 'intval');
		if ( $pagesize < 1 || $pagesize >50 ) {
			$pagesize = 10;
		}

		$lPage = D('PersonPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->LogicTouchSearch($keyword, $page, $pagesize);
		$binds['opts'] = ['keyword'=>$keyword];
		// echo PHP_EOL, '<!-- <pre>', PHP_EOL, print_r($binds, true), PHP_EOL, '</pre> -->', PHP_EOL;exit;
		$result = ['status'=>true] + $binds;
		$this->ajax_return($result);
	}
	/**
	 * 乐道问答 - 搜索时的联想搜索接口
	 */
	public function pn_suggest() {
		if ( $this->_device=='pc' ) {
			$this->ajax_error('暂时仅支持移动端访问');
		}
		# 页面参数
		$keyword = I('get.k', '', 'trim,clear_all,clean_xss');
		$length = abslength($keyword);
		if ( $length < 1 || $length > 50 ) {
			$this->ajax_return(['status'=>true,'list'=>[]]);
		}

		$page = 1;
		$pagesize = 10;
		$lPage = D('PersonPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->LogicTouchSuggest($keyword, $page, $pagesize);
		// $binds['opts'] = ['keyword'=>$keyword, 'page'=>$page, 'pagesize'=>$pagesize];
		// echo PHP_EOL, '<!-- <pre>', PHP_EOL, print_r($binds, true), PHP_EOL, '</pre> -->', PHP_EOL;exit;
		$result = ['status'=>true] + $binds;
		$this->ajax_return($result);
	}



	// pc版中的加载更多
	public function loadmore() {
		$keyword = I('get.k', '', 'trim');
		$page = I('get.page', 2, 'intval');
		$pagesize = I('get.pagesize', 10, 'intval');

		$binds = D('Page', 'Logic')
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setType('api')
				->pc_search_logic($keyword);
		$total = intval($binds['total']);
		unset($binds['total']);
		$binds['pager'] = array(
			'page' => $page,
			'pagesize' => $pagesize,
			'pagecount' => ceil($total/$pagesize),
			'total' => $total,
		);
		$binds['pager']['is_last'] = ( $page >= $binds['pager']['pagecount'] ) ? 1 : 0;
		$binds['_list_source_flag'] = 'pc_qa_search';
		$this->assign_all($binds);
		layout(false);
		$html = $this->fetch('Public:list');
		unset($binds['list']);
		$binds['html'] = $html;
		$this->ajax_return($binds);
	}


	public function page() {
		$keyword = I('get.k', '', 'trim');
		$lPage = D('Page', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setType('api')
				->pc_search_logic($keyword);
		// 清理无用数据
		unset($binds['catetree']);
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
