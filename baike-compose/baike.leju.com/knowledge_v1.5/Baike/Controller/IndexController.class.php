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

		$Device = ucfirst($this->_device);
		$pageLogic = D( $Device.'Page', 'Logic' );

		$cateid = I('get.id',0,'intval');

		$lCate = D('Cate','Logic','Common');
		$cateid = $cateid<=0 ? $lCate->getFirstTopCateid() : $cateid;

		$list = $pageLogic->initIndexPage($this->_city['cn'], $cateid,$this->_city['code']);
		isset($list['cate_all']) && $this->assign('cate_all',$list['cate_all']);
		$cateid && $this->assign('cateid', $cateid);
		$this->assign('D', $list);

		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->index();
		$this->setPageInfo($seo);

        //统计代码
        $count_cate = C('FRONT_BAIKE_COUNT_CATE');
        $level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
        $level2_page = ($this->_device == 'pc') ? $count_cate[$cateid] : 'kd_index';
        $level3_page = ($this->_device == 'pc') ? 'index' : '';
        //
        $this->assign('level1_page', $level1_page);
        $this->assign('level2_page', $level2_page);
        $this->assign('level3_page', $level3_page);

		$this->assign('kd_index_pic', '#ln=kd_index_pic');
		$this->assign('kd_index_wdmore', '#ln=kd_index_wdmore');
		$this->assign('index_flag', 0);
		$this->assign('jsflag','kb_index');

		$this->display();
	}

}