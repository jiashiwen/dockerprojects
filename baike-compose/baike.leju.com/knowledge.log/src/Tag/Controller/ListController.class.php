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
		$pageLogic = D($this->_device.'Page', 'Logic' );
		if($this->_device == 'mobile')
		{
			$list = $pageLogic->getList($this->_cache_keys['all']);
		}
		else
		{
			$page = I("get.page", 1, "intval");
			$pagesize = I("get.pagesize", 16, "intval");
			$list = $pageLogic->getList($this->_cache_keys['pcall'], $page, $pagesize);

			$total = $list['total'];
			$list = $list['result'];
			$new_page = ($page == 1) ? 0: $pagesize*($page-1);
			$list = array_slice($list, $new_page, $pagesize);

			//分页
			if($total)
			{
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
			$hot = $pageLogic->initIndexHot($this->_cache_keys['hot']);
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
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->llist();
		$pageinfo = array_merge($pageinfo,$seo);

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
		$category = $this->_category;
		if ( $cateid === '' ) {
			$this->all();
		}
		//接口请求数据
		$url = C('DATA_TRANSFER_API_URL') . "api/item?category={$cateid}&sort=pinyin";
		$result = curl_get($url);

		$list = array();
		if($result['status'] == true)
		{
			$list = json_decode($result['result'], true);
			$list = $list['result'][0];
		}

		$pageinfo = array();
		$pageinfo['subtitle'] = $category[$cateid].'类词条';

		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->llist($cateid);
		$pageinfo = array_merge($pageinfo,$seo);

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
		$tag = I('get.tag', '', 'trim,strip_tags,htmlspecialchars');
		$page = I("get.page", 1, "intval");
		$pagesize = I("get.pagesize", 16, "intval");
		//接口请求数据
		$PageLogic = D($this->_device.'Page', 'Logic' );
		$list = $PageLogic->getTagList($this->_cache_keys['tag'].$tag, $tag, $page, $pagesize);

		$total = $list['total'];
		$new_page = ($page == 1) ? 0: $pagesize*($page-1);
		$list = array_slice($list['result'], $new_page, $pagesize);

		//分页
		if($total)
		{
			$linkopts = array();
			$tag && array_push($linkopts, "tag={$tag}");
			$linkstring = !empty($linkopts) ? '/tag/agg.html/?page=#&'.implode('&',$linkopts) : '/tag/agg/?page=#';

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
		$hot = $PageLogic->initIndexHot($this->_cache_keys['hot']);
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

	/**
	 * 全部词条备份
	 */
	/*
	public function all2() {
		$r = S(C('REDIS'));
		$all = $r->get("wiki:tag:list:all");
		if($all)
		{
			$this->assign('all', $all);
		}
		else
		{
			$lSearch = D('Search','Logic','Common');
			$opts = array(array('false', '_deleted'));
			$order = array('_multi.title_pinyin', 'asc');
			$fields = array('_id','_multi.title_pinyin');
			$result = $lSearch->select(1, 5000,'', $opts, array(), $order, $fields, 0, 'wiki');

			$all = array();
			foreach($result['list'] as $k=>$v)
			{
				$pinyin = strtoupper($v['_multi']['title_pinyin']);
				$all[$pinyin[0]][] = $v;
			}

			ksort($all);
			foreach($all as $k=>$v)
			{
				sort($all[$k]);
			}

			$r->set("wiki:tag:list:all", $all, 86400);
			$this->assign('all', $all);
		}

		//统计代码
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_list');

		$pageinfo = array();
		$pageinfo['subtitle'] = '全部词条';
		$this->setPageInfo($pageinfo);

		$this->display('index2');
	}
*/
	/**
	 * 分类词条备份
	 */
	/*
	public function cate2( $cateid=0 ) {
		$category = $this->_category;
		if ( $cateid === '' ) {
			$this->all();
		}

		$r = S(C('REDIS'));
		$all = $r->get("wiki:tag:list:cate-{$cateid}");
		if($all)
		{
			$this->assign('all', $all);
		}
		else
		{
			$lSearch = D('Search','Logic','Common');
			$opts = array(array('false', '_deleted'), array($cateid, '_multi.cateid'));
			$order = array('_multi.title_pinyin', 'asc');
			$fields = array('_id','_multi.title_pinyin');
			$result = $lSearch->select(1, 5000,'', $opts, array(), $order, $fields, 0, 'wiki');

			$all = array();
			foreach($result['list'] as $k=>$v)
			{
				$pinyin = strtoupper($v['_multi']['title_pinyin']);
				$all[$pinyin[0]][] = $v;
			}

			ksort($all);
			foreach($all as $k=>$v)
			{
				sort($all[$k]);
			}

			$r->set("wiki:tag:list:cate-{$cateid}", $all, 86400);
			$this->assign('all', $all);
		}

		$pageinfo = array();
		$pageinfo['subtitle'] = $category[--$cateid].'类词条';
		$this->setPageInfo($pageinfo);

		//统计代码
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_tag_list');
		$this->assign('custom_id', $cateid);

		$this->display('index2');
	}*/
}