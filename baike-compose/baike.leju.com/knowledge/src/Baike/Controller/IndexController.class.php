<?php
/**
 * 知识库系统 - 知识子系统主控制器
 */
namespace Baike\Controller;

class IndexController extends BaseController {
	/**
	 * 首页
	 */
	public function index() {

		$cateid = I('get.id',0,'intval');

		$lCate = D('Cate','Logic','Common');
		$cateid = $cateid<=0 ? $lCate->getFirstTopCateid() : $cateid;
		$list = $this->lFront->initIndexPage($this->_city['cn'], $cateid, $this->_city['code']);
		isset($list['cate_all']) && $this->assign('cate_all',$list['cate_all']);
		$cateid && $this->assign('cateid', $cateid);
		$this->assign('D', $list);

		//SEO
		$seoLogic = D('Seo','Logic','Common');
		if ( $this->_device=='pc' ) {
			$path = $lCate->getCatePathByIdForSEO($cateid);
			$seo = $seoLogic->cate($path[1], $path[2], $path[3]);
			$seo['seo_title'] = &$seo['title'];
		} else {
			$seo = $seoLogic->index();
		}
		$alt_device = $this->_device=='pc' ? 'touch' : 'pc';
		$seo['alt_url'] = url('index', array(), $alt_device, 'baike');
		$this->setPageInfo($seo);

        //统计代码
        $count_code = C('FRONT_BAIKE_COUNT_CODE');
        $level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
        $level2_page = ($this->_device == 'pc') ? $count_code['PC_ALL'][$cateid] : 'kd_index';
        $level3_page = ($this->_device == 'pc') ? 'index' : '';
        $this->assign('level1_page', $level1_page);
        $this->assign('level2_page', $level2_page);
        $this->assign('level3_page', $level3_page);
		$this->_city['stat'] = $this->_city['en'];
		$this->assign('city', $this->_city);


		$this->assign('jsflag', 'kb_index');	// 移动端 触屏模版使用
		$this->assign('index_flag', 0);
		$this->assign('kd_index_pic', '#ln=kd_index_pic');
		$this->assign('kd_index_wdmore', '#ln=kd_index_wdmore');

		$this->display();
	}

}