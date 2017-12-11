<?php
/**
 * 知识库 问题分类查看页面
 */
namespace Baike\Controller;

class CateController extends BaseController {

	/**
	 * #至少指定一个一级分类
	 * 至少指定一个二级分类
	 * 显示与期同父级下面的所有二级分类列表及三级分类内容摘要
	 */
	public function index() {
		$id = I('id', 0, 'intval');
		$page = I('page', 1, 'intval');
		$pagesize = I('pagesize', 10, 'intval');

		$Device = ucfirst($this->_device);
		$pageLogic = D( $Device.'Page', 'Logic' );

		$result = $pageLogic->getCatePage($id, $page, $this->_city['code'], $this->_city['cn']);
		$binds['cate'] = $result['cate'];
		$binds['cid'] = $result['cid'];
		$binds['catelist'] = $result['catelist'];
		$binds['total'] = $result['total'];
		$binds['nav'] = $result['nav'];
		$result['bread'] && $binds['bread'] = $result['bread'];

		$binds['register'] = 0;
		$this->assign('cateid',$result['cateid']);
		$this->assign('cate_all',$result['cate_all']);
		$this->assign('pager', $result['pager']);
		$this->assign('list', $result['list']);
		// @TODO: 整理模版显示到一个/组变量里
		$this->assign('binds', $binds);

		//SEO
		$lCate = D('Cate','Logic','Common');
		$path = $lCate->getCatePathByIdForSEO($id);
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->cate($path[1], $path[2], $path[3]);
		$seo['seo_title'] = &$seo['title'];
		$this->setPageInfo($seo);

		//统计代码
		$lv = count($path);
		if ($lv == 3)
			$_lv = 'list2';
		if ($lv == 4)
			$_lv = 'list3';
		$count_cate = C('FRONT_BAIKE_COUNT_CATE');
		$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
		$level2_page = ($this->_device == 'pc') ? $count_cate[$result['cateid']] : 'kd_cates_list';
		$level3_page = ($this->_device == 'pc') ? $_lv : '';
		//
		$this->assign('level1_page', $level1_page);
		$this->assign('level2_page', $level2_page);
		$this->assign('level3_page', $level3_page);

		$this->assign('custom_id', $id);
		$this->assign('jsflag','kb_cate');
		$this->display();
	}
}