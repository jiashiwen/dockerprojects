<?php
/**
 * 移动端 App 接口
 * @author Robert <yongliang1@leju.com>
 * 使用方 : 口袋乐居
 */
namespace api\Controller;
use Think\Controller;

class AppController extends BaseController {
	protected $city = array();
	protected $cache = false;
	protected $cache_expire = 60;
	protected $_keys = array(
		'focus' => 'API:APP:{city}:FOCUS',
		'cates' => 'API:APP:{city}:CATES',
		'list' => 'API:APP:{city}:LISTS',
	);
	protected $index_list_size = 4;
	protected $list_total = 14;
	protected $list_pagesize = 5;

	public function __construct() {
		$cities = C('CITIES.ALL');
		$city_code = I('get.city', '', 'trim,strip_tags,htmlspecialchars,strtolower');
		$city_code = $city_code == '' ? cookie('B_CITY') : $city_code;
		// 未传或传错 city ，则使用 bj 做为默认城市；城市代码使用 city_en 标准
		if ( !array_key_exists($city_code, $cities) ) {
			$city_code = 'bj';
		}
		$this->city = $cities[$city_code];
		$this->city['code'] = $city_code;

		if ( $this->cache!==false ) {
			$this->cache = S(C('REDIS'));

			foreach ( $this->_keys as $part => &$key ) {
				$key = str_replace('{city}', strtoupper($this->city['en']), $key);
			}

			$flush = date('Ymd', I('get.flush', 0, 'intval'));
			$current = date('Ymd', NOW_TIME);
			if ( $flush === $current ) {
				$this->cache->del($this->_keys);
			}
		}

	}

	/**
	 * 手机 App 读取指定城市的首页数据
	 * @param $city string 城市代码，使用 city_en 标准，如果不传或传值不属于乐居 city_en 集合，则使用 bj 作为默认值
	 * @param $flush int 是否强制更新缓存 传值为时间戳，如果时间戳为当天，则清除缓存
	 * @return json 返回 json 数据结果
	 */
	// @Interface
	public function index(){
		// 获取焦点内容 1 条最新的焦点
		// 从接口获取数据，返回指定城市和全国的已经发布的数据，按发布时间倒排
		$result = $this->getIndex();
		$result['status'] = true;
		$result['city'] = $this->city;
		$this->showResult($result);
	}

	// 获取更多
	/* 不用了
	public function more () {
		// 指定要返回的 页码
		$page = I('get.page', 2, 'intval');

		if ( $page > 3 ) {
			$result = array('status'=>true, 'list'=>'');
			$this->showResult($result);
		}

		// 获取数据时，获取15条，会导致只能查询2，3两页数据
		// 要么就按 5条每页 的方式获取

		// 从接口获取数据，返回指定城市和全国的已经发布的数据，按发布时间倒排
		// total 为指定城市和全国的所有已发布知识的总数
		$result = array(
			'status' => true,
			'city' => $this->city,
			'pager' => array(
				'page' => $page,
				'pagesize' => $this->list_pagesize,
			),
			'list' => $this->getList($page),
		);
		$this->showResult($result);
	}
	*/


	/**
	 * 首页数据接口逻辑
	 */
	protected function getIndex() {
		$_result = $this->cache!==false ? $this->cache->mGet($this->_keys) : false;

		$result = array();
		if ( !$_result || !$_result[0] || !$_result[1] || !$_result[2] ) {
			$result['cached'] = false;
			// 获取所有一级栏目
			$lCate = D('Cate', 'Logic', 'Common');
			$_cates = $lCate->getCateListById(0);
			$result['cates'] = array();
			foreach ( $_cates as $cateid => $name ) {
				array_push($result['cates'], array(
					'name' => $name,
					'url' => url('cate', array('id'=>$cateid, 'city'=>$this->city['en']), 'touch', 'baike'),
				));
			}

			// 获取 focus
			$focusid = 0;
			$page = 1;
			$pagesize = 1;
			$lSearch = D('Search', 'Logic', 'Common');
			$order = array('_multi.rcmd_time', 'desc');
			$fields = array('_id', '_origin.title','_origin.cover','_origin.catepath','_origin.tags');
			$opts = array(
				array('false', '_deleted'),
				array("!0","_multi.rcmd_time"),
				array("{$this->city['cn']},全国",'_scope'),
			);
			$ret = $lSearch->select($page, $pagesize, '', $opts, array(), $order, $fields);
			if ( !empty($ret['list']) ) {
				$catepath = explode('-', trim($ret['list'][0]['_origin']['catepath'], '-'));
				$cateid = $catepath[2];
				
				$_tags = explode(' ', trim($ret['list'][0]['_origin']['tags'], ' '));
				$_tags = array_shift(array_chunk($_tags, 3));
				$tags = array();
				foreach ( $_tags as $i => $tag ) {
					array_push($tags, array(
						'tag' => $tag,
						'link' => url('agg', array('tag'=>$tag, 'city'=>$this->city['en']), 'touch', 'baike'),
					));
				}

				$focusid = $ret['list'][0]['_id'];
				$result['focus'] = array(
					'title' => $ret['list'][0]['_origin']['title'],
					'cover' => changeImageSize($ret['list'][0]['_origin']['cover'], 690, 264),
					'cate' => array(
						'name' => $lCate->getCateName($cateid),
						'url' => url('cate', array('id'=>$cateid, 'city'=>$this->city['en']), 'touch', 'baike'),
					),
					'tags' => $tags,
					'url' => url('show', array('id'=>$focusid), 'touch', 'baike'),
				);
				unset($catepath, $cateid, $_tags, $tags, $ret);
			}

			// 获取列表数据
			$page = 1;
			$pagesize = 14;
			$order = array('_docupdatetime', 'desc');
			$fields = array('_id', '_origin.title','_origin.cover','_origin.catepath','_origin.tags');
			$opts = array(
				array('false', '_deleted'),
				array("!{$focusid}","_id"),
				array("{$this->city['cn']},全国",'_scope'),
			);
			$ret = $lSearch->select($page, $pagesize, '', $opts, array(), $order, $fields);
			if ( !empty($ret['list']) ) {
				$result['list'] = array();

				foreach ( $ret['list'] as $_i => $item ) { 
					$catepath = explode('-', trim($item['_origin']['catepath'], '-'));
					$cateid = $catepath[2];
					
					$_tags = explode(' ', trim($item['_origin']['tags'], ' '));
					$_tags = array_shift(array_chunk($_tags, 3));
					$tags = array();
					foreach ( $_tags as $i => $tag ) {
						array_push($tags, array(
							'tag' => $tag,
							'link' => url('agg', array('tag'=>$tag, 'city'=>$this->city['en']), 'touch', 'baike'),
						));
					}

					array_push($result['list'], array(
						'title' => $item['_origin']['title'],
						'cover' => changeImageSize($item['_origin']['cover'], 690, 264),
						'cate' => array(
							'name' => $lCate->getCateName($cateid),
							'url' => url('cate', array('id'=>$cateid, 'city'=>$this->city['en']), 'touch', 'baike'),
						),
						'tags' => $tags,
						'url' => url('show', array('id'=>$item['_id']), 'touch', 'baike'),
					));
					unset($catepath, $cateid, $_tags, $tags);
				}
				unset($ret);
			}

			// 首页加载 4 条，列表加载 2 页，第 2, 3 页，每页 5 条数据
			$result['list'] = $this->_getKnowledgeList($this->list_total);

			// 如果启用缓存，则进行数据缓存
			if ( $this->cache!==false ) {
				$this->cache->SetEx($this->_keys['focus'], $this->cache_expire, json_encode($result['focus']));
				$this->cache->SetEx($this->_keys['cates'], $this->cache_expire, json_encode($result['cates']));
				$this->cache->SetEx($this->_keys['list'], $this->cache_expire, json_encode($result['list']));
			}
		} else {
			$result['cached'] = true;
			$keys = array_keys($this->_keys);
			foreach ( $_result as $i => $val ) {
				$result[$keys[$i]] = json_decode($val, true);
			}
			unset($_result);
		}
		/*
		$result['list'] = array_shift(array_chunk($result['list'], $this->index_list_size));
		*/
		return $result;
	}

	/**
	 * 更多知识列表接口逻辑
	 */
	protected function getList($page=2) {
		$result = $this->cache!==false ? $this->cache->Get($this->_keys['list']) : false;
		if ( !$result ) {
			// 首页加载 4 条，列表加载 2 页，第 2, 3 页，每页 5 条数据
			$result = $this->_getKnowledgeList($this->list_total);
		} else {
			$result = is_array($result) ? $result : json_decode($result, true);
		}
		$count = count($result);
		$result = array_slice($result, $this->index_list_size);
		$result = array_chunk($result, $this->list_pagesize);
		if ( array_key_exists($page-1, $result) ) {
			$result = $result[$page-1];
		} else {
			$result = array();
		}
		return $result;
	}

	// 获取知识列表
	private function _getKnowledgeList() {
		$result = array();
		// 获取列表数据
		$page = 1;
		$pagesize = 14;
		$order = array('_docupdatetime', 'desc');
		$fields = array('_id', '_origin.title','_origin.cover','_origin.catepath','_origin.tags');
		$opts = array(
			array('false', '_deleted'),
			array("!{$focusid}","_id"),
			array("{$this->city['cn']},全国",'_scope'),
		);
		$lCate = D('Cate', 'Logic', 'Common');
		$lSearch = D('Search', 'Logic', 'Common');
		$ret = $lSearch->select($page, $pagesize, '', $opts, array(), $order, $fields);
		if ( !empty($ret['list']) ) {

			foreach ( $ret['list'] as $_i => $item ) { 
				$catepath = explode('-', trim($item['_origin']['catepath'], '-'));
				$cateid = $catepath[2];
				
				$_tags = explode(' ', trim($item['_origin']['tags'], ' '));
				$_tags = array_shift(array_chunk($_tags, 3));
				$tags = array();
				foreach ( $_tags as $i => $tag ) {
					array_push($tags, array(
						'tag' => $tag,
						'link' => url('agg', array('tag'=>$tag, 'city'=>$this->city['en']), 'touch', 'baike'),
					));
				}

				array_push($result, array(
					'title' => $item['_origin']['title'],
					'cover' => changeImageSize($item['_origin']['cover'], 222, 166),
					'cate' => array(
						'name' => $lCate->getCateName($cateid),
						'url' => url('cate', array('id'=>$cateid, 'city'=>$this->city['en']), 'touch', 'baike'),
					),
					'tags' => $tags,
					'url' => url('show', array('id'=>$item['_id']), 'touch', 'baike'),
				));
				unset($catepath, $cateid, $_tags, $tags);
			}
			unset($ret);
		}
		return $result;
	}

}