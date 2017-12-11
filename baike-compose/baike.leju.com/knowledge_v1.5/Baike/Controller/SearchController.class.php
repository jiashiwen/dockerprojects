<?php
/**
 * 知识库 知识内容搜索结果页面
 */
namespace Baike\Controller;

class SearchController extends BaseController {
	// 每页默认返回的数据数量
	protected $pagesize = 10;
	// 搜索结果的分页信息
	protected $pager = array();

	/**
	 * 搜索查询结果页面
	 *
	 * @param $dbg int 是否开启调试模式 0非调试 1调试 [选填,默认0][scope:action]
	 * @param $k string 用户输入的搜索内容 [必填][scope:action]
	 * @param $f string 指定搜索的业务类型 多个类型使用英文半角","分隔 [选填,默认为""][scope:action]
	 * @param $p int 指定查看搜索结果的分页 从1开始的自然数 [选填,默认为1][scope:action]
	 * @param $ns int 是否强制不使用联想关键词 0使用 1不使用 [选填,默认0][scope:action]
	 * @param $theme string 指定使用的主题皮肤 [选填,默认为"default"][scope:global]
	 */
	public function index() {

		$keyword = I('get.keyword','','trim');
		$page = I('get.page',1,'intval');
		$pagesize = I('get.pagesize',10,'intval');
		$id = I('get.id',0,'intval');

		$Device = ucfirst($this->_device);
		$pageLogic = D( $Device.'Page', 'Logic' );
        if($keyword && !preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\'\"“”‘’，\??？\s,]+$/u",$keyword))
        {
            $this->error('关键字含有非法字符');
        }

		if (!empty($keyword))
		{
			$result = $pageLogic->getSearchPage($page, $keyword, $this->_city['cn'], $this->city['code'], $id);
			$dbg = $result['dbg'];
		}
		else
		{
			$this->error('请输入搜索内容');
		}

		$tpl = ( $dbg!==0 ) ? 'noresult' : 'index';

		if (isset($result['pager']))
		{
			$this->assign('pager',$result['pager']);
			$binds['total'] = $result['maxpage'];
		}
		if (isset($result['nav']))
		{
			$binds['nav'] = $result['nav'];
		}
		$this->assign('cateid',$result['cateid']);
		$binds['register'] = 0;

		$this->assign('cate_all',$result['cate_all']);
		$this->assign('list', $result['list']);
		$this->assign('binds', $binds);
		$this->assign('sortId', $keyword);


		//SEO
		$pageinfo = array(
			'city' => $this->_city['cn'],
			'keyword' => $keyword,
		);
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->search($keyword);
		$pageinfo = array_merge($pageinfo,$seo);
		$this->setPageInfo($pageinfo);


		//统计代码
        $count_cate = C('FRONT_BAIKE_COUNT_CATE');
        $level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
        $level2_page = ($this->_device == 'pc') ? $count_cate[$id] : 'kd_Search';
        $level3_page = ($this->_device == 'pc') ? 'search' : '';
        //
        $this->assign('level1_page', $level1_page);
        $this->assign('level2_page', $level2_page);
        $this->assign('level3_page', $level3_page);

		$this->assign('custom_id', $keyword);
		$this->assign('jsflag', 'kb_search');

		$this->display($tpl);
	}

	// @TODO: 移动端在用，重构至 MobileLogic 中
	private function linkopts($form,$total)
	{
		//封装linkopts
		$linkopts = array();
		$form['keyword'] && array_push($linkopts, "keyword={$form['keyword']}");
		$form['city'] && array_push($linkopts, "city={$form['city']}");
		$form['id'] && array_push($linkopts, "id={$form['id']}");

		$linkstring = !empty($linkopts) ? '/cate/?page=#&'.implode('&',$linkopts) : '/cate/?page=#';

		$opts = array(
			'first' => true, //首页
			'last' => true,	//尾页
			'prev' => true, //上一页
			'next' => true, //下一页
			'number' => 5, //显示页码数
			'linkstring' => $linkstring
		);
		$pager = pager($form['page'], $total, 10, $opts);
		return $pager;
	}

	public function loading()
	{
		$page = I('request.page',2,'intval');
		$keyword = I('request.keyword','','trim');
		$pagesize = I('get.pagesize',10,'intval');
		$keyword = clear_all($keyword);
		$return = array(
			'status'=>false,
			'list'=>null,
			'msg'=>'',
			'pagesize'=>$pagesize,
			'page'=>$page,
			'total'=>$total,
			'keyword'=>$keyword,
		);
		if ( $keyword === '' ) {
			$result['msg'] = '关键词长度不能为空';
			$this->ajax_return($result);
		}
		$lSearch = D('Search','Logic','Common');
		$opts = array(array('false', '_deleted'),array("{$this->_city['cn']},全国",'_scope'));
		$order = array('_docupdatetime', 'desc');
		//$prefix = array(array("0-{$id}-", '_multi.catepath'));
		$fields = array('_id','_title','_version','_origin');
		$total = 0;


		$result = $lSearch->select($page,$pagesize,$keyword,$opts,$prefix, $order, $fields);

		if ($result)
		{
			foreach ($result['list'] as $key => $item)
			{
				$list[$key]['id'] = $item['_origin']['id'];
				$list[$key]['title'] = $item['_origin']['title'];
				$list[$key]['cover'] = $item['_origin']['cover'];
				$list[$key]['content'] = $item['_origin']['content'];
				$list[$key]['url'] = url('show', array($item['_origin']['id']));
				$list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
				$list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ctime']);
			}
			$total = $result['pager']['total'];
			$return = array(
				'status'=>true,
				'list'=>$list,
				'msg'=>'',
				'pagesize'=>$pagesize,
				'page'=>$page,
				'total'=>$total,
				'keyword'=>$keyword,
			);
		}

		$this->ajax_return($return);

	}


	/**
	 * [Ajax] 联想查询接口
	 *
	 */
	public function suggest() {
		$keyword = I('request.keyword','','trim');
		$limit = I('post.pagesize', 10, 'intval');
		$result = array(
			'status' => 'fail',
			'info'=>array(
				'api' => 'suggest',
				'keyword'=>$keyword,
				'pagesize'=>$limit,
				'list' => array(),
			),
		);
		$engine = D('Search', 'Logic', 'Common');

		if ( $keyword === '' ) {
			$result['msg'] = '关键词长度不能为空';
			$this->ajax_return($result);
		}
		$opts = array(
			array('false', '_deleted'),
			array("{$this->_city['cn']},全国",'_scope'),
		);
		$prefix = array(array($keyword, "_multi.title_prefix"));
		$page = 1;
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_scope','_origin.content');
		$search = $engine->select($page, $limit, '', $opts, $prefix, $order, $fields);
		if ($search && $search['pager']['total'] > 0)
		{
			foreach ($search['list'] as $key => $value) {
				$result['info']['list'][$key]['id'] = $value['_id'];
				$result['info']['list'][$key]['scope'] = $value['_scope'];
				$result['info']['list'][$key]['entry'] = $value['_title'];
				$result['info']['list'][$key]['url'] = url('show', array($value['_id']));
			}
			$result['info']['total'] = $search['pager']['total'];
		}
		else
		{
			$search = $engine->select($page, $limit, $keyword, $opts, '', $order, $fields,1);
			foreach ($search['list'] as $key => $value) {
				$result['info']['others'][$key]['id'] = $value['_id'];
				$result['info']['others'][$key]['scope'] = $value['_scope'];
				$result['info']['others'][$key]['entry'] = $value['_title'];
				$result['info']['others'][$key]['url'] = url('show', array($value['_id']));
				$result['info']['others'][$key]['content'] = $this->__cutContentByWord($value['_origin']['content']);
			}
			$result['info']['total'] = $search['pager']['total'];
		}
		$result['status'] = 'succ';
		$this->ajax_return($result);
	}



	/**
	 * 获取搜索结果
	 * @param $city string 城市代码
	 * @param $keyword string 搜索关键词
	 * @param $page int 指定分页
	 * @param $pagesize int 指定每页数据数量
	 * @return array 数据集合
	 */
	protected function fetchResults( $city, $keyword, $page=1, $pagesize=0 ) {
		$pagesize = ( intval($pagesize)<=0 ) ? $this->pagesize : intval($pagesize);
		$page = intval($page)<=0 ? 1 : intval($page);
		$keyword = $keyword;

		// $total 为符合查询条件的结果集合大小
		$total = 1000;
		// 当前搜索的分页信息
		$this->pager = array(
			'city' => $city,
			'keyword' => $keyword,
			'total' => $total,
			'page' => $page,
			'pagesize' => $this->pagesize,
			'pagecount' => ceil($total / $this->pagesize),
		);
		// 调用搜索查询接口获取关键词相关搜索结果
		$result = array();
		return $result;
	}

	/**
	 * PC端通用搜索
	 */
	public function suggestpc()
	{
		$keyword = I('request.keyword', '', 'trim,strip_tags,htmlspecialchars');

		$frontLogic = D('Front', 'Logic','Common');
		$result = $frontLogic->getSuggest($keyword, $this->_city['cn'],$this->_city['code']);
		ajax_succ($result);
	}

}