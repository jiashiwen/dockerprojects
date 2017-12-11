<?php
/**
 * 知识库系统 - 知识子系统主控制器
 */
namespace Baike\Controller;

class IndexController extends BaseController {
	/**
	 * 首页
	 */
	public function index(){

		$Device = ucfirst($this->_device);
		$pageLogic = D( $Device.'Page', 'Logic' );
		// D: {'forces':[],'cates':[],'latest':[],'hotwords':[]}
		$list = $pageLogic->initIndexPage($this->_city['cn']);

		$more = C('FRONT_URL.cate').$id;
		$this->assign('D', $list);
		$this->assign('binds', $binds);
		$this->assign('index_flag',0);
		$this->assign('jsflag','kb_index');
		
		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'kd_index');
		$this->assign('kd_index_pic', '#ln=kd_index_pic');
		$this->assign('kd_index_wdmore', '#ln=kd_index_wdmore');
		$this->setPageInfo();
		$this->display();
	}


	public function demo() {
		$this->display();
	}

}