<?php
/**
 * 知识库 百科词条搜索结果列表页面
 * @author Robert <yongliang1@leju.com>
 */
namespace Tag\Controller;

class SearchController extends BaseController {
	// 每页默认返回的数据数量
	protected $pagesize = 10;
	// 搜索结果的分页信息
	protected $pager = array();

	/**
	 * 词条列表展现
	 */
	public function index() {
		$keyword = I('get.word', '', 'trim,strip_tags,htmlspecialchars');
		filterInput($keyword);

		if(empty($keyword))
		{
			$tpl = 'noresult';
		}
		else
		{
			$tpl = 'index';
			// 第一期不分页全部展示
			$list = $this->fetchResults($keyword);
			if(empty($list['result']) || $list['result'][0]['hightlight'] != '')
			{
				$tpl = 'noresult';
			}

			$pageinfo = array();
			$pageinfo['subtitle'] = '"'.$keyword.'"相关的词条';
			$this->setPageInfo($pageinfo);

			$this->assign('keyword', $keyword);
			$this->assign('list', $list['result']);
		}

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_search');
		$this->assign('custom_id', $keyword);

		$this->display($tpl);
	}

	/**
	 * [Ajax] 异步获取搜索结果的操作
	 */
	public function result() {
		$page = I('page', 2, 'intval');
		$keyword = I('word', '', 'trim,strip_tags,htmlspecialchars');

		$result = array(
			//'status' => false,
			'api' => 'search',
		);

		if ( $keyword==='' ) {
			$result['msg'] = '关键词不能为空';
			ajax_error($result);
		}

		// 使用搜索业务逻辑搜索指定关键字的相关百科词条的结果列表
		$result['list'] = $this->fetchResults($keyword, $page);
		$result['pager'] = $this->pager;

		ajax_succ($result);
		//$this->ajax_return($result);
	}

	/**
	 * [Ajax] 异步获取联想词结果
	 */
	public function suggest() {
		// 指定每次返回的相关前缀查询返回的数据集合中的词条数量，默认返回 10 条
		$pagesize = I('post.pagesize', 10, 'intval');
		if($pagesize < 1) $pagesize = 10;
		$keyword = I('request.keyword', '', 'trim,strip_tags,htmlspecialchars');
		filterInput($keyword);
		$result = array(
			//'status' => false,
			'api' => 'suggest',
		);

		// 验证关键词是否为空，没有指定关键词则不进行接口调用
		if ( $keyword == '' ) {
			$result['msg'] = '关键词不能正确';
			ajax_error($result);
			//$this->ajax_return($result);
		}
		$result['keyword'] =$keyword;
		//$result['status'] = true;

		// @TODO : 调用 @董寰宇 提供的联想词接口获取联想词数据
 		$result['list'] = array();
		$result['others'] = array();
		$result['total'] = 0;
		$suggest_url = C('DATA_TRANSFER_API_URL') . "api/item/suggest?k={$keyword}&n={$pagesize}";
		$suggest_result = curl_get($suggest_url);

		$data_result = json_decode($suggest_result['result'], true);
		//var_dump($pagesize);exit;
		if($suggest_result['status'] == true && !empty($data_result['result']))
		{
			$result['total'] =$data_result['total'];
			$result['list'] = $this->cycleData($data_result['result']);
		}
//		else//无联想词匹配内容中含有此联想词的数据
//		{
//			$others = $this->fetchResults($keyword, 1,$pagesize,'suggest');
//			if(!empty($others['result']) && $others['result']['0']['hightlight'])
//			{
//				$result['total'] =$others['total'];
//				$result['others'] = $this->cycleData($others['result']);
//			}
//		}
		ajax_succ($result);
	}
	/**
	 * 获取搜索结果
	 * @param $keyword string 搜索关键词
	 * @param $page int 指定分页
	 * @param $pagesize int 指定每页数据数量
	 * @return array 数据集合
	 */
	protected function fetchResults( $keyword, $page=1, $pagesize=0, $from='' ) {
		$pagesize = ( intval($pagesize)<=0 ) ? $this->pagesize : intval($pagesize);
		$page = intval($page)<=0 ? 1 : intval($page);
		$keyword = $keyword;

		// 调用 @董寰宇 提供的搜索查询接口获取关键词相关搜索结果
 		$url = C('DATA_TRANSFER_API_URL') . "api/item";
		$data['entry'] = $keyword;
		$data['search'] = 1;
		$data['sort'] = 'pinyin';
		if($from == 'suggest')
		{
			$data['page'] = $page;
			$data['pagesize'] = $pagesize;
		}

		$search_result = curl_get($url, $data);

		$result = array();
		if($search_result['status'] == true)
		{
			$result = json_decode($search_result['result'], true);
		}

		return $result;
	}

	/**
	 * 循环处理结果
	 * @param $data array 数据集合
	 * @return array 数据集合
	 */
	protected function cycleData($data)
	{
		if(!empty($data))
		{
			foreach ($data as $k => $v)
			{
				$data[$k]['url'] = 'http://'.DOMAIN_NAME . '/tag/show?id=' . $data[$k]['id'];
				if($data[$k]['hightlight'])
				{
					$data[$k]['hightlight'] = trim(strip_tags($data[$k]['hightlight']));
				}
			}
		}
		return $data;
	}

	/**
	 * 词条列表展现-备份
	 */
	public function index2() {
		$keyword = I('get.word', '', 'trim,strip_tags,htmlspecialchars');
		filterInput($keyword);

		if(empty($keyword))
		{
			$tpl = 'noresult';
		}
		else
		{
			$tpl = 'index2';

			//搜索标题
			$lSearch = D('Search','Logic','Common');
			$opts = array(array('false', '_deleted'));
			$order = array('_multi.title_pinyin', 'asc');
			$fields = array('_id','_multi.title_pinyin');
			$prefix = array(array($keyword,'_multi.title_prefix'));
			$ttt = $lSearch->select(1, 5000, $keyword, $opts, $prefix, $order, $fields, 0, 'wiki');

			if($ttt['pager']['total'] > 0)
			{
				$all = array();
				foreach($ttt['list'] as $k=>$v)
				{
					$pinyin = strtoupper($v['_multi']['title_pinyin']);
					$all[$pinyin[0]][] = $v;
				}

				ksort($all);
				foreach($all as $k=>$v)
				{
					sort($all[$k]);
				}

				$this->assign('all',$all);
			}
//			else
//			{
//				$tpl = 'noresult';
//
//				//无联想词匹配内容中含有此联想词的数据
//				$opts = array(array('false', '_deleted'));
//				$fields = array('_id','_origin.content');
//				$ccc = $lSearch->select(1, 10, $keyword, $opts, array(), array(), $fields, 1, 'wiki');
//
//				if($ccc['pager']['total'] > 0)
//				{
//					$list = array();
//					$result['total'] = $ccc['pager']['total'];
//					foreach($ccc['list'] as $k=>$v)
//					{
//						$list[$k]['id'] = $list[$k]['entry'] = $v['_id'];
//						$list[$k]['hightlight'] = trim($v['_origin']['content']);
//					}
//					$this->assign('list',$list);
//				}
//			}

			$pageinfo = array();
			$pageinfo['subtitle'] = '"'.$keyword.'"相关的词条';
			$this->setPageInfo($pageinfo);

			$this->assign('keyword', $keyword);
		}

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_search');
		$this->assign('custom_id', $keyword);

		$this->display($tpl);
	}

	/**
	 * [Ajax] 异步获取联想词结果-备份
	 */
	public function suggest2() {
		// 指定每次返回的相关前缀查询返回的数据集合中的词条数量，默认返回 10 条
		$pagesize = I('request.pagesize', 10, 'intval');
		if($pagesize < 1) $pagesize = 10;
		$keyword = I('request.keyword', '', 'trim,strip_tags,htmlspecialchars');
		filterInput($keyword);
		$result = array(
			'api' => 'suggest',
		);

		// 验证关键词是否为空，没有指定关键词则不进行接口调用
		if ( $keyword == '' ) {
			$result['msg'] = '关键词不能正确';
			ajax_error($result);
		}
		$result['keyword'] = $keyword;
		$result['list'] = array();
		$result['others'] = array();
		$result['total'] = 0;

		//搜索标题
		$lSearch = D('Search','Logic','Common');
		$opts = array(array('false', '_deleted'));
		$order = array('_multi.title', 'asc');
		$fields = array('_id');
		$prefix = array(array($keyword,'_multi.title_prefix'));
		$ttt = $lSearch->select(1, $pagesize, $keyword, $opts, $prefix, $order, $fields, 0, 'wiki');

		if($ttt['pager']['total'] > 0)
		{
			$result['total'] = $ttt['pager']['total'];
			foreach($ttt['list'] as $k=>$v)
			{
				$result['list'][$k]['id'] = $result['list'][$k]['entry'] = $v['_id'];
				$result['list'][$k]['url'] = 'http://'.DOMAIN_NAME . '/tag/show-index2?id=' . $v['_id'];
			}
		}
//		else
//		{
//			//无联想词匹配内容中含有此联想词的数据
//			$opts = array(array('false', '_deleted'));
//			$fields = array('_id','_origin.content');
//			$ccc = $lSearch->select(1, $pagesize, $keyword, $opts, array(), array(), $fields, 1, 'wiki');
//
//			if($ccc['pager']['total'] > 0)
//			{
//				$result['total'] = $ccc['pager']['total'];
//				foreach($ccc['list'] as $k=>$v)
//				{
//					$result['others'][$k]['id'] = $result['others'][$k]['entry'] = $v['_id'];
//					$result['others'][$k]['url'] = 'http://'.DOMAIN_NAME . '/tag/show-index2?id=' . $v['_id'];
//					$result['others'][$k]['hightlight'] = trim($v['_origin']['content']);
//				}
//			}
//		}
		ajax_succ($result);
	}
}