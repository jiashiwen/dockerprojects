<?php
/**
 * 知识库 知识导航页面
 */
namespace Baike\Controller;

class MapController extends BaseController {

	/**
	 * 显示所有分类及子分类
	 */
	public function index() {
		// pc 端没有此页面
		if ( $this->_device == 'pc' ) {
			send_http_status(404);
			exit;
		}

		$lCate = D('Cate','Logic','Common');
		$flush = I('get.flush',0,'intval');
		// $list = $lCate->toTree($flush);
		// $this->assign('list', $list);
		$cate_all = $lCate->getIndexTopCategories();
		$this->assign('cate_all', $cate_all);

		// var_export($list);
		// var_export($cate_all);
		// @TODO: SEO优化漏了
		$this->setPageInfo();

		// 统计代码
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'kd_nav');
		$this->_city['stat'] = $this->_city['en'];
		$this->assign('city', $this->_city);

		$this->assign('jsflag', 'kb_map');

		$this->display();
	}

	protected function formatTree($data, $pid = 0){
		$list = array();
		$tem = array();
		foreach ($data as $item)
		{
			if ($item['parent'] == $pid)
			{
				$tem = $this->formatTree($data, $item['id']);
				//判断是否存在子数组
				$tem && $item['son'] = $tem;
				$list[] = $item;
			}
		}
	 	return $list;
	}

}