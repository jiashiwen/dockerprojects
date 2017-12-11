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
		$url = C('DATA_TRANSFER_API_URL') . "api/item?sort=pinyin";
		$result = curl_get($url);
		$list = array();
		if($result['status'] == true)
		{
			$list = json_decode($result['result'], true);
		}
		$pageinfo = array();
		$pageinfo['subtitle'] = '全部词条';
		$this->setPageInfo($pageinfo);

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_list');

		$this->showList($list);
	}

	/**
	 * 分类词条
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
		}

		$pageinfo = array();
		$pageinfo['subtitle'] = $category[$cateid].'类词条';
		$this->setPageInfo($pageinfo);

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_tag_list');
		$this->assign('custom_id', $cateid);

		$this->showList($list);
	}

	/**
	 * 调用显示
	 */
	protected function showList( $list = array() ) {
		$this->assign('list', $list['result'][0]);
		$this->display('index');
		exit;
	}

	/**
	 * 全部词条备份
	 */
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
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_list');

		$pageinfo = array();
		$pageinfo['subtitle'] = '全部词条';
		$this->setPageInfo($pageinfo);

		$this->display('index2');
	}

	/**
	 * 分类词条备份
	 */
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
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_tag_list');
		$this->assign('custom_id', $cateid);

		$this->display('index2');
	}
}