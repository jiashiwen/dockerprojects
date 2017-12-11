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
		$keyword = I('get.word', '', 'trim');
		if ( empty($keyword) ) {
			$this->error('请输入搜索内容');
		}
		if ( $keyword && !preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\'\"“”‘’，\??？\s,]+$/u",$keyword) ) {
			$this->error('关键字含有非法字符');
		}
		filterInput($keyword);

		$tpl = 'index';
		if($this->_device == 'touch') {
			if(empty($keyword)) {
				$tpl = 'noresult';
			} else {
				// 第一期不分页全部展示
				$list = $this->fetchResults($keyword);
				if ( empty($list) ) {
					$tpl = 'noresult';
				}
			}
		} else {
			$page = I('get.page', 1, 'intval');
			$pagesize = I('get.pagesize', 16, 'intval');
			$PageLogic = D($this->_device.'Page', 'Logic' );
			$list = $PageLogic->getSearchList($keyword, $page, $pagesize);

			$total = $list['total'];
			$list = $list['result'];
			// 标准分页逻辑处理
			if ( $total ) {
				$linkopts = array();
				$keyword && array_push($linkopts, "word={$keyword}");
				$linkstring = !empty($linkopts) ? '/tag/search.html/?page=#&'.implode('&',$linkopts) : '/tag/listall/?page=#';

				$opts = array(
						'first' => true, //首页
						'last' => true,	//尾页
						'prev' => true, //上一页
						'next' => true, //下一页
						'number' => 5, //显示页码数
						'linkstring' => $linkstring
				);
				$pager = pager($page, $total, $pagesize, $opts);
				$this->assign('pager', $pager);
			}

			//热门词条api
			$hot = $PageLogic->initIndexHot($this->_cache_keys['hot']);
			$hot = array_slice($hot, 0, 6);
			$this->assign('hot', $hot);

			//获取热门百科知识
			$knowledge = D('Front', 'Logic','Common');
			$hot_know = $knowledge->getHotSearchList($this->_city['code'],$this->_city['cn']);
			$hot_know = array_slice($hot_know, 0, 8);
			$this->assign('hot_know', $hot_know);

		}
		$pageinfo = array();
		$pageinfo['subtitle'] = '"'.$keyword.'"相关的词条';

		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->search($keyword);
		$alt_device = $this->_device=='pc' ? 'touch' : 'pc';
		$seo['alt_url'] = url('search', array(), $alt_device, 'wiki');
		$pageinfo = array_merge($pageinfo,$seo);

		$this->setPageInfo($pageinfo);

		$this->assign('keyword', $keyword);
		$this->assign('list', $list);

		//统计代码
		$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
		$level2_page = ($this->_device == 'pc') ? 'pc_ct_search' : 'wd_search';
		$this->assign('level1_page', $level1_page);
		$this->assign('level2_page', $level2_page);

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
	 * - 百科首页 顶部搜索时的联想词查询
	 * - 所有词条列表页 顶部搜索时的联想词查询
	 * - 词条分类列表页 顶部搜索时的联想词查询
	 * - 词条搜索列表页 顶部搜索时的联想词查询
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
			$result['msg'] = '关键词不正确';
			ajax_error($result);
			//$this->ajax_return($result);
		}
		$result['keyword'] =$keyword;
		//$result['status'] = true;

		$opts = array(
			'title' => array('like', $keyword.'%'),
		);
		$order = 'ptime DESC';
		$page = 1;
		$fields = array('id', 'title', 'cateid');
		$list = $this->lPage->_getList($opts, $order, $page, $pagesize, $fields);
		if( $list ) {
			$result['total'] = intval($list['pager']['total']);
			$result['others'] = array();
			$result['list'] = $this->cycleData($list['list']);
		}
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
		$pagesize = intval($pagesize)<=0 ? 10000 : $pagesize;
		$PageLogic = D($this->_device.'Page', 'Logic' );
		$ret = $PageLogic->getSearchList($keyword, $page, $pagesize);
		$result = array();
		foreach ( $ret['result'] as $i => $item ) {
			$head = strtoupper($item['firstletter']);
			if ( !array_key_exists($head, $result) ) {
				$result[$head] = array();
			} 
			array_push($result[$head], $item);
		}
		if ( !empty($result) ) {
			ksort($result);
		}
		return $result;
	}

	/**
	 * 循环处理结果
	 * @param $data array 数据集合
	 * @return array 数据集合
	 */
	protected function cycleData($list) {
		if(!empty($list)) {
			foreach ($list as $k => $v) {
				$list[$k]['url'] = url('show', array($list[$k]['id'], $list[$k]['cateid']), $this->_device, 'wiki');
				$list[$k]['entry'] = $list[$k]['title'];
				if($list[$k]['hightlight']) {
					$list[$k]['hightlight'] = trim(strip_tags($list[$k]['hightlight']));
				}
			}
		}
		return $list;
	}


}