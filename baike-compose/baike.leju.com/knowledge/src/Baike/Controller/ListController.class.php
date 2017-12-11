<?php
/**
 * 知识库 知识列表页面
 */
namespace Baike\Controller;

class ListController extends BaseController {
	/**
	 * 指定三级分类的知识列表页面
	 * 移动端使用
	 */
	public function index() {
		$id = I('id', '', 'intval');
		$page = I('page',1, 'intval');
		$pagesize = I('pagesize',10, 'intval');

		$lCate = D('Cate','Logic','Common');
		$cate = $lCate->getCateName($id);
		if (!$cate) {
			$this->error('分类ID错误');
			exit;
		}

		$lSearch = D('Search','Logic','Common');
		$opts = array(
			array('false', '_deleted'),
			array("{$this->_city['cn']},全国",'_scope'),
			array("$id","_multi.cateid"),
		);
		$order = array('_docupdatetime', 'desc');
		//$prefix = array(array("0-{$id}-", '_multi.catepath'));
		$fields = array('_id','_title','_version','_origin');
		$topKB = $this->getTopKB($lSearch,$this->_city['cn'],$id);

		if ($topKB !== false)
		{
			$pagesize -= 1;
			$exid = $topKB['_id'];
			array_push($opts, array("!{$exid}","_id"));

			//
			$tops['id'] = $topKB['_origin']['id'];
			$tops['title'] = ($topKB['_origin']['top_time'] > 0
				&& !empty($topKB['_origin']['top_title'])) ? $topKB['_origin']['top_title'] : $topKB['_origin']['title'];
			$tops['cover'] = ($topKB['_origin']['top_time'] > 0
				&& !empty($topKB['_origin']['top_cover'])) ? $topKB['_origin']['top_cover'] : $topKB['_origin']['cover'];
			$tops['url'] = url('show', array($topKB['_origin']['id']));
			$tops['tags'] = explode(' ',$topKB['_origin']['tags']);
			// 标签转换为 id 与标签名称
			$tops['tagsinfo'] = $this->lFront->convertTagToTagid($tops['tags']);
			$tops['ctime'] = date('Y-m-d H:i:s',$topKB['_origin']['ctime']);
			$this->assign('topKB',$tops);
		}

		$result = $lSearch->select($page, $pagesize,'',$opts,$prefix, $order, $fields);

		$binds['parent'] = $cate;

		if ($result['pager']['total'] > 0)
		{
			foreach ($result['list'] as $key => $item)
			{
				$list[$key]['id'] = $item['_origin']['id'];
				$list[$key]['title'] = ($item['_origin']['top_time'] > 0
					&& !empty($item['_origin']['top_title'])) ? $item['_origin']['top_title'] : $item['_origin']['title'];
				$list[$key]['cover'] = ($item['_origin']['top_time'] > 0
					&& !empty($item['_origin']['top_cover'])) ? $item['_origin']['top_cover'] : $item['_origin']['cover'];
				$list[$key]['url'] = url('show', array($item['_origin']['id']));
				$list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
				// 标签转换为 id 与标签名称
				$list[$key]['tagsinfo'] = $this->lFront->convertTagToTagid($list[$key]['tags']);
				$list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ctime']);
			}
		}
		$this->assign('binds',$binds);
		$this->assign('list', $list);
		$this->assign('sortId', $id);


		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$path = $lCate->getCatePathByIdForSEO($id);
		$seo = $seoLogic->cate($path[1], $path[2], $path[3]);
		$seo['seo_title'] = &$seo['title'];
		$alt_device = $this->_device=='pc' ? 'touch' : 'pc';
		$seo['alt_url'] = url('list', array('id'=>$path[3]), $alt_device, 'baike');
		// $seo2 = $seoLogic->llist();
		$this->setPageInfo($seo);

		//统计代码
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'kd_cate_list');
		$this->assign('custom_id', $id);
		$this->_city['stat'] = $this->_city['en'];
		$this->assign('city', $this->_city);

		$this->assign('jsflag', 'kb_list');

		$this->display('index');
	}

	private function getTopKB($lSearch,$city,$cateid,$num = 1)
	{
		$opts = array(
			array('false', '_deleted'),
			array("{$city},全国",'_scope'),
			array('!0', '_multi.top_time'),
			array("$cateid","_multi.cateid"),
		);
		$prefix = array();
		$order = array('_multi.top_time', 'desc');
		$fields = array('_id','_title','_version','_origin');
		$result = $lSearch->select(1,$num,'',$opts,$prefix, $order, $fields);

		if ($result['pager']['total'] >= 1)
		{
			return $result['list']['0'];
		}
		return false;
	}


	public function loading()
	{
		$id = I('id', 0, 'intval');
		$page = I('page',2, 'intval');
		$pagesize = I('pagesize',10, 'intval');
		$return = array(
			'status'=>false,
			'list'=>null,
			'msg'=>'',
			'pagesize'=>$pagesize,
			'page'=>$page,
			'total'=>0,
			'id'=>$id,
		);
		if ( $id <= 0 ) {
			$this->ajax_return($return);
		}

		$lSearch = D('Search','Logic','Common');
		$opts = array(
			array('false', '_deleted'),
			array("{$this->_city['cn']},全国",'_scope'),
			array("$id","_multi.cateid"),
		);
		$order = array('_doccreatetime', 'desc');
		//$prefix = array(array("0-{$id}-", '_multi.catepath'));
		$fields = array('_id','_title','_version','_origin');

		$result = $lSearch->select($page, $pagesize,'',$opts,$prefix, $order, $fields);

		if ($result['pager']['total'] > 0)
		{
			foreach ($result['list'] as $key => $item)
			{
				$list[$key]['id'] = $item['_origin']['id'];
				$list[$key]['title'] = $item['_origin']['title'];
				$list[$key]['cover'] = $item['_origin']['cover'];
				$list[$key]['url'] = url('show', array($item['_origin']['id']));
				$list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
				// 标签转换为 id 与标签名称
				$list[$key]['tagsinfo'] = $this->lFront->convertTagToTagid($list[$key]['tags']);
				$list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ctime']);
			}
			$return = array(
				'status'=>true,
				'list'=>$list,
				'msg'=>'',
				'pagesize'=>$pagesize,
				'page'=>$page,
				'total'=>$result['pager']['total'],
				'id'=>$id,
			);
		}

		$this->ajax_return($return);
	}

}