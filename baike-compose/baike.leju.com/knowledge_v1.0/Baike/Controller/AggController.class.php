<?php
/**
 * 知识库 问题分类查看页面
 */
namespace Baike\Controller;

class AggController extends BaseController {

	/**
	 */
	public function index() {
		$tag = I('tag', 0, 'trim,addslashes');
		$tag = clear_all($tag);
		$page = I('page', 1, 'intval');

		$pagesize = 10;	// 默认以标签聚合显示的知识列表，每页显示 10 条知识信息
		$list = array();

		if (empty($tag))
		{
			$this->error('标签不存在');
			exit;
		}

		$lSearch = D('Search','Logic','Common');
		$opts = array(array("$tag","_tags"),array('false', '_deleted'),array("{$this->_city['cn']},全国",'_scope'));
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_version','_origin');

		$result = $lSearch->select($page, $pagesize, '', $opts, $prefix, $order, $fields);

		if ($result['pager']['total'] > 0)
		{
			foreach ($result['list'] as $key => $item) 
			{
				$list[$key]['id'] = $item['_origin']['id'];
				$list[$key]['title'] = $item['_origin']['title'];
				$list[$key]['cover'] = $item['_origin']['cover'];
				$list[$key]['url'] = url('show', array($item['_origin']['id']));
				$list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
				$list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ctime']);
			}
		}
		$binds['parent'] = $tag;
		$this->assign('binds',$binds);	
		$this->assign('list', $list);
		$this->assign('sortId', $tag);
		$this->assign('jsflag','kb_agg');

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'kd_agg');
		$this->assign('custom_id', $tag);

		$this->setPageInfo();
		$this->display('List/index');
	}

	public function loading() {
		$tag = I('id', 0, 'trim,addslashes');
		$page = I('page',2, 'intval');
		$pagesize = I('pagesize',10, 'intval');
		$list = array();

		$return = array(
			'status'=>false,
			'list'=>null,
			'msg'=>'',
			'pagesize'=>$pagesize,
			'page'=>$page,
			'total'=>0,
			'tag'=>$tag,
		);
		if (!empty($tag))
		{
			$lSearch = D('Search','Logic','Common');
			$opts = array(array("$tag","_tags"),array('false', '_deleted'),array("{$this->_city['cn']},全国",'_scope'));
			$order = array('_doccreatetime', 'desc');
			$fields = array('_id','_title','_version','_origin');

			$result = $lSearch->select($page, $pagesize,'',$opts,$prefix, $order, $fields);

			if ($result['pager']['total'] > 0)
			{
				$total = $result['pager']['total'];
				foreach ($result['list'] as $key => $item) 
				{
					$list[$key]['id'] = $item['_origin']['id'];
					$list[$key]['title'] = $item['_origin']['title'];
					$list[$key]['cover'] = $item['_origin']['cover'];
					$list[$key]['url'] = url('show', array($item['_origin']['id']));
					$list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
					$list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ctime']);
				}
			}
			$return = array(
				'status'=>true,
				'list'=>$list,
				'msg'=>'',
				'pagesize'=>$pagesize,
				'page'=>$page,
				'total'=>$total,
				'tag'=>$tag,
			);
		}
		$this->ajax_return($return);
	}
}