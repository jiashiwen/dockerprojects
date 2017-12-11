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

		$city = $this->getUserCity();
		$this->assign('city', $city);
		$lCate = D('Cate','Logic','Common');
		$list = $lCate->toTree();
		$this->assign('list', $list);
		$this->assign('jsflag', 'kb_map');

		$this->setPageInfo();

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'kd_nav');
		$this->display('index');
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