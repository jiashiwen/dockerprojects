<?php
/**
 * 知识库 词条列表页面
 * @author Robert <yongliang1@leju.com>
 */
namespace Tag\Controller;

class ListController extends BaseController {

	/**
	 * 词条列表展现
	 */
	public function index() {
		$cateid = I('cateid', 0, 'intval');
		if ( $cateid !== '' ) {
			$this->cate($cateid);
		} else {
			// 没有指定分类id或查询关键字时，展现全部
			$this->all();
		}
	}

	/**
	 * 全部词条
	 */
	public function all() {
		//接口请求数据
		if($this->_device != 'pc') {
			$ret = $this->lPage->setFlush(true)->getListAll();
			$list = $ret['list'];
			// echo '<!--', PHP_EOL, var_export($list, true), PHP_EOL, '-->', PHP_EOL;
		} else {
			$page = I('get.page', 1, 'intval');
			$pagesize = I('get.pagesize', 16, 'intval');
			$cateid = I('get.cateid', false, 'intval');

			$ret = $this->lPage->getList($page, $pagesize, $cateid);

			$total = intval($ret['pager']['total']);
			$list = $ret['list'];
			//分页
			if ( $total ) {
				$linkstring = url('listall', array('page'=>'#'), 'pc', 'wiki');
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

			//热门词条api
			$hot = $this->lPage->initIndexHot();
			$hot = array_slice($hot, 0, 6);
			$this->assign('hot', $hot);

			//获取热门百科知识
			$knowledge = D('Front', 'Logic','Common');
			$hot_know = $knowledge->getHotSearchList($this->_city['code'],$this->_city['cn']);
			$hot_know = array_slice($hot_know, 0, 8);
			$this->assign('hot_know', $hot_know);
		}

		$pageinfo = array();
		$pageinfo['subtitle'] = '全部词条';

		//SEO
		$seoLogic = D('WikiSeo','Logic','Common');
		$seo = $seoLogic->llist();
		$alt_device = $this->_device=='pc' ? 'touch' : 'pc';
		$seo['alt_url'] = url('listall', array(), $alt_device, 'wiki');
		$pageinfo = array_merge($pageinfo, $seo);

		$this->setPageInfo($pageinfo);

		//统计代码
		$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
		$level2_page = ($this->_device == 'pc') ? 'pc_ct_list' : 'wd_list';
		$this->assign('level1_page', $level1_page);
		$this->assign('level2_page', $level2_page);

		$this->showList($list);
	}

	/**
	 * 分类词条
	 * 移动专属
	 */
	public function cate( $cateid=0 ) {
		 // _getList( $conditions=[], $order='id DESC', $page=1, $pagesize=20, $fields=array()
		if ( $cateid === '' ) {
			$this->all();
		}

		$opts = array(
			'cateid'=> $cateid,
		);
		$order = 'id DESC';
		if ( $this->_device!='pc' ) {
			$page = 1;
			$pagesize = 10000;
		} else {
			$page = I('get.page', 1, 'intval');
			$pagesize = I('get.pagesize', 20, 'intval');
		}
		$ret = $this->lPage->getList($opts, $order, $page, $pagesize);

		$pageinfo = array();
		$pageinfo['subtitle'] = $category[$cateid].'类词条';
		if ( $this->_device != 'pc' ) {
			$list = array();
			foreach ( $ret['list'] as $i => $item ) {
				$head = strtoupper($item['firstletter']);
				if ( !array_key_exists($head, $list) ) {
					$list[$head] = array();
				} 
				array_push($list[$head], $item);
			}
			if ( !empty($list) ) {
				ksort($list);
			}
		} else {
			$list = $ret['list'];
			$total = intval($ret['pager']['total']);
		}

		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->llist($cateid);
		$pageinfo = array_merge($pageinfo,$seo);
		$seo['alt_url'] = url('list', array('cateid'=>$cateid), 'touch', 'wiki');
		$this->setPageInfo($pageinfo);

		//统计代码
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_tag_list');
		$this->assign('custom_id', $cateid);

		$this->showList($list);
	}
	/**
	 * PC标签词条
	 * PC专属
	 */
	public function tags() {
		if ( $this->_device == 'mobile' ) {
			send_http_status(404);
			exit;
		}
		// $tag = I('get.tag', '', 'trim,strip_tags,htmlspecialchars');
		$tagid = I('get.tag', 0, 'intval');
		$page = I("get.page", 1, "intval");
		$pagesize = I("get.pagesize", 16, "intval");
		$key = $this->_cache_keys['tag'].$tagid;
		$taginfo = D('Tags', 'Logic', 'Common')->getTagnameByTagid($tagid);
		if ( !$taginfo ) {
			$this->error('您指定的标签不存在');
		}
		$tag = $taginfo['name'];
		//接口请求数据
		$ret = $this->lPage->getTagList($this->_cache_keys['tag'].$tag, $tag, 1, $getall);
		$total = $ret['pager']['total'];
		$pagecount = ceil( $total / $pagesize );
		$new_page = $page <= 1 ? 1 : $page;
		$new_page = $page > $pagecount ? $pagecount : $page;
		$new_page = $new_page - 1;

		$list = $ret['list'];
		//分页
		if ( $total ) {
			$linkopts = array();
			$tag && array_push($linkopts, "tag={$tagid}");
			// $linkstring = !empty($linkopts) ? '/tag/agg.html/?page=#&'.implode('&',$linkopts) : '/tag/agg/?page=#';
			$linkstring = url('agg', array('tag'=>$tagid, '#'), 'pc', 'wiki');

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

		//热门词条api
		$hot = $this->lPage->initIndexHot($this->_cache_keys['hot']);
		$hot = array_slice($hot, 0, 6);
		$this->assign('hot', $hot);

		//获取热门百科知识
		$knowledge = D('Front', 'Logic','Common');
		$hot_know = $knowledge->getHotSearchList($this->_city['code'],$this->_city['cn']);
		$hot_know = array_slice($hot_know, 0, 8);
		$this->assign('hot_know', $hot_know);

		$pageinfo = array();
		$pageinfo['subtitle'] = $tag.'标签词条';

		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->tag($tag);
		$seo['alt_url'] = url('listall', array(), 'touch', 'wiki');
		$pageinfo = array_merge($pageinfo,$seo);
		$this->setPageInfo($pageinfo);

		//统计代码
		$this->assign('level1_page', 'pc_fcbk');
		$this->assign('level2_page', 'pc_ct_agg');
		$this->assign('custom_id', $tag);

		$this->assign('tag', $tag);
		$this->showList($list);
	}

	/**
	 * 调用显示
	 */
	protected function showList( $list = array() ) {
		$this->assign('list', $list);
		$this->display('index');
		exit;
	}
}