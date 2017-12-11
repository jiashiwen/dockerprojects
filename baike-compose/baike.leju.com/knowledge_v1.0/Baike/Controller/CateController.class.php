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
		$list = array();

		$lCate = D('Cate', 'Logic', 'Common');

		// 取当前指定栏目 id 的信息和子集
		$current = $lCate->getCateInfo($id);
		if ( !$current || $current['level']>2 || $current['level']<1 ) {
			$this->error('分类ID错误');
		}


		if ( $current['level']==1 ) {
			if ( count($current['child'])>0 ) {
				$ids = array_keys($current['child']);
				$active = intval($ids[0]);
			} else {
				$active = false;
			}
			$level1 = &$current;
			$level2 = $active ? $lCate->getCateInfo($active) : array();
		}
		if ( $current['level']==2 ) {
			$level1 = $lCate->getCateInfo($current['pid']);
			$level2 = &$current;
			$active = $id;
		}

		$lSearch = D('Search','Logic','Common');
		$order = array('_docupdatetime', 'desc');
		$fields = array('_id','_title','_version','_origin');
		$prefix = array();

		$num = 2;
		foreach ($level2['child'] as $k => $item) {
			$path = $item['path'];
			// $prefix = array(array("{$path}", '_multi.catepath'));
			$opts = array(array('false', '_deleted'),array("{$this->_city['cn']},全国",'_scope'),array("{$path}", '_multi.catepath'));
			$result = $lSearch->select(1, $num,'',$opts,$prefix, $order, $fields);
			// var_dump($path, $k, $item, $opts);
			if ($result['pager']['total'] > 0)
			{
				foreach ($result['list'] as $kk => $ii) 
				{
					$list[$k]['name'] = $item['name'];
					$list[$k]['cateid'] = $k;
					// $list[$k]['cate_url'] = C('FRONT_URL.list'). $ii['_origin']['cateid'];
					$list[$k]['list'][$kk]['title'] = $ii['_title'];
					$list[$k]['list'][$kk]['id'] = $ii['_origin']['id'];
					$list[$k]['list'][$kk]['cover'] = $ii['_origin']['cover'];
					$list[$k]['list'][$kk]['tags'] = explode(' ',$ii['_origin']['tags']);
					$list[$k]['list'][$kk]['ctime'] = date('Y-m-d H:i:s',$ii['_origin']['ctime']);
				}
			}
		}
		$binds['cate'] = $level1['name'];
		$binds['cid'] = $active;
		// $binds['cid'] = in_array($id, $lv3['ids']) ? $id : $lv3['ids']['0'];
		$binds['catelist'] = $level1;
		$this->assign('list', $list);

		$pageinfo = array(
			'city_en' => cookie('city_en'),
			'city' =>  $this->_city['cn'],
			// 'cateid' => $cate,
		);
		$binds['register'] = 0;
		// var_dump(
		// 	array(
		// 		array('level1'=>$level1),
		// 		array('level2'=>$level2),
		// 		array('active'=>$active),
		// 		array('binds'=>$binds),
		// 		array('pageinfo'=>$pageinfo),
		// 		array('list'=>$list),
		// 	)
		// ); exit;
		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'kd_cates_list');
		$this->assign('custom_id', $id);

		$this->setPageInfo($pageinfo);
		$this->assign('binds',$binds);
		$this->assign('jsflag','kb_cate');
		$this->display();
	}

	private function getCate($id)
	{
		$mCategories = D('Categories', 'Model', 'Common');
		$cate = $mCategories->find($id);
		if ($cate)
		{
			if ($cate['level'] == 3)
			{
				return false;
			}
			if ($cate['level'] == 2)
			{
				return $cate;
			}
			if ($cate['level'] == 1)
			{
				$list = $mCategories->where(array('parent'=>$cate['id']))->order('iorder asc')->find();
				if ($list)
				{
					return $list;
				}
				else
				{
					return false;
				}
			}
		}
		return false;
	}

}