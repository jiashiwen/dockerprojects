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

		$result = $this->lFront->getCatePage($id, $page, $this->_city['code'], $this->_city['cn']);
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


		//SEO
		$lCate = D('Cate','Logic','Common');
		$path = $lCate->getCatePathByIdForSEO($id);
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->cate($path[1], $path[2], $path[3]);
		$seo['seo_title'] = &$seo['title'];
		$level = count( array_diff($path, array('0', '', 0)) );
		// var_dump($path, $level);
		$alt_device = $this->_device=='pc' ? 'touch' : 'pc';
		if ( in_array($level, array(1,2,3)) ) {
			if ( $level==3 ) {
				$seo['alt_url'] = url('list', array('id'=>$path[3]), $alt_device, 'baike');
			} else {
				$seo['alt_url'] = url('cate', array('id'=>$path[$level]), $alt_device, 'baike');
			}
		}
		$this->setPageInfo($seo);

		$this->assign('catepath', $path);

		$extra = array();
		if ( $path[1]==1 ) {
			$extra = D('Extra', 'Logic', 'Common')->getHouse($this->_city);
		}
		if ( $path[1]==2 ) {
			$lExtra = D('Extra', 'Logic', 'Common');
			$extra = $lExtra->getESF($this->_city['code']);
			// var_dump($this->_city, $extra, $path);
			// 二手房 买房
			if ( $path[2]==64 ) {
				$extra = $extra['data']['sale'];
			}
			// 二手房 租房
			if ( $path[2]==66 ) {
				$extra = $extra['data']['rent'];
			}
			// var_dump($extra);
		}
		$this->assign('extra', $extra);

		foreach ( $result['list'] as $i => &$item ) {
			$item['catename'] = $lCate->getCateName($item['cateid'], 'kb');
		}
		$this->assign('list', $result['list']);
		// @TODO: 整理模版显示到一个/组变量里
		$this->assign('binds', $binds);

		//统计代码
		$_lv = 0;
		foreach ( $path as $cid ) {
			if (intval($cid)>0) {
				$_lv++;
			}
		}
		$_lv = 'list'.$_lv;
		//统计代码
		$count_code = C('FRONT_BAIKE_COUNT_CODE');
		$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
		$level2_page = ($this->_device == 'pc') ? $count_code['PC_ALL'][$path[1]] : $count_code['TOUCH_CATE'][$path[1]];
		$level3_page = ($this->_device == 'pc') ? $_lv : '';
		$this->assign('level1_page', $level1_page);
		$this->assign('level2_page', $level2_page);
		$this->assign('level3_page', $level3_page);
		$this->_city['stat'] = $this->_city['en'];
		$this->assign('city', $this->_city);


		$this->assign('custom_id', $id);
		$this->assign('jsflag','kb_cate');
		$this->display();
	}
}