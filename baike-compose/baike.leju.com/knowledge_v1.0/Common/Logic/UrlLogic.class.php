<?php
/**
 * 生成和处理 url 的规则
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class UrlLogic {
	static protected $_instance = null;
	protected $deployed = false;
	// 自动检测部署模式
	protected $detected = false;
	// 部署域名
	protected $deploy = 'baike.leju.com';

	protected $base = array();
	protected $types = array('touch'=>'m', 'pc'=>'');
	protected $modes = array('ld'=>'本地开发', 'dev'=>'集成开发', 'test'=>'集成测试', ''=>'正式环境');
	protected $mods = array();		// 应用模块
	protected $type = 'touch';		// 默认入口为移动端
	protected $mode = null;			// 默认为正式环境
	protected $module = 'baike';	// 默认值即为百科知识模块
	protected $host = '';

	/** ---- URL 路由表设置 ---- **/
	/* Touch 知识 版本路由表 */
		// Baike Index 页面
		public function touchBaikeIndex() {
			$url = $this->getPath();
			return $url;
		}

		// Baike Index 页面
		// http://ld.m.baike.leju.com/map.html
		public function touchBaikeMap() {
			$path = $this->deployed ?
						'map.html'
						:
						'map';
			return $this->getPath() . $path;
		}

		// Baike 分类列表页 页面
		// http://ld.m.baike.leju.com/cate-city-id.html
		public function touchBaikeCate($id, $city='bj') {
			$path = $this->deployed ?
						"cate-{$city}-{$id}.html"
						:
						"cate?id={$id}";
			return $this->getPath() . $path;
		}

		// Baike 信息列表页 页面
		// http://ld.m.baike.leju.com/list-city-id.html
		public function touchBaikeList($id, $city='bj') {
			$path = $this->deployed ?
						"list-{$city}-{$id}.html"
						:
						"list?id={$id}";
			return $this->getPath() . $path;
		}
		// Baike 信息列表页 分页加载 接口
		// http://ld.m.baike.leju.com/listmore-id-page.html
		public function touchBaikeListmore($id, $page=2) {
			$path = $this->deployed ?
						"listmore-{$id}-{$page}.html"
						:
						"list-loading?id={$id}&page={$page}";
			return $this->getPath() . $path;
		}

		// Baike 标签聚合列表页 页面
		// http://ld.m.baike.leju.com/agg-tag.html
		public function touchBaikeAgg($tag='') {
			$path = $this->deployed ?
						"agg-{$tag}.html"
						:
						"agg?tag={$tag}";
			return $this->getPath() . $path;
		}
		// Baike 标签聚合列表页 分页加载 接口
		// http://ld.m.baike.leju.com/aggmore-id-page.html
		public function touchBaikeAggmore($tag='', $page=2) {
			$path = $this->deployed ?
						"aggmore-{$tag}-{$page}.html"
						:
						"agg-loading?id={$tag}&page={$page}";
			return $this->getPath() . $path;
		}

		// Baike 信息列表页 页面
		// http://ld.m.baike.leju.com/show-95.html
		public function touchBaikeShow($id) {
			$path = $this->deployed ?
						"show-{$id}.html"
						:
						"show?id={$tag}";
			return $this->getPath() . $path;
		}

		// Baike 信息列表页 页面
		// http://ld.m.baike.leju.com/search-keyword.html
		public function touchBaikeSearch() {
			$path = $this->deployed ?
						"search.html"
						:
						"search";
			return $this->getPath() . $path;
		}
		// Baike 标签聚合列表页 分页加载 接口
		// http://ld.m.baike.leju.com/aggmore-id-page.html
		public function touchBaikeResult($keyword='', $page=2) {
			$path = $this->deployed ?
						"result-{$keyword}-{$page}.html"
						:
						"search-loading?keyword={$keyword}&page={$page}";
			return $this->getPath() . $path;
		}

	// // // // // // // // // // // // // // // // // // // // // //
		// Wiki Index 页面
		public function touchWikiIndex() {
			$url = $this->getPath();
			return $url;
		}

		// Wiki 列表 页面 - 所有词条
		public function touchWikiListall() {
			$path = $this->deployed ?
						"listall.html"
						:
						"list/all";
			return $this->getPath(). $path;
		}

		// Wiki 列表 页面 - 指定分类的词条
		public function touchWikiList($cateid=0) {
			$path = $this->deployed ?
						"cate/{$cateid}.html"
						:
						"list/?cateid={$cateid}";
			return $this->getPath(). $path;
		}

		// Wiki 详情 页面
		public function touchWikiShow($word) {
			$path = $this->deployed ?
						"word/{$word}.html"
						:
						"show/?id={$word}";
			return $this->getPath(). $path;
		}

		// Wiki 搜索结果 页面
		public function touchWikiSearch($keyword) {
			$path = $this->deployed ?
						"search.html/?word={$keyword}"
						// "search-{$keyword}.html"
						:
						"search/?word={$keyword}";
			return $this->getPath(). $path;
		}

		// Wiki 搜索结果 - 联想词 接口
		public function touchWikiSuggest($keyword) {
			$path = $this->deployed ?
						"suggest.html"
						:
						"search/suggest";
			return $this->getPath(). $path;
		}

	/* Touch 版本路由表 */
	/** ---- URL 路由表设置 ---- **/


	public function __construct($detected=false) {
		$this->host = strtolower($_SERVER['HTTP_HOST']);
		$this->base = C('DOMAINS');
		$this->mods = array(
			'baike'=>'/', 'wiki'=>'/tag/', 'ask'=>'/ask/'
		);
		// $this->deployed = !APP_DEBUG && C('URL_ROUTER_ON');
		$this->deployed = C('URL_ROUTER_ON');
		$this->detected = !!($detected);
	}

	static public function getInstance($detected=false) {
		if ( is_null(self::$_instance) ) {
			self::$_instance = new self($detected);
		}
		return self::$_instance;
	}

	public function setBase($type='', $mod='') {
		if ( array_key_exists($mod, $this->mods) ) {
			$this->module = $mod;
		} else {
			$this->module = 'baike';
		}
		if ( array_key_exists($type, $this->types) ) {
			$this->type = $type;
		} else {
			$this->type = 'touch';
		}
		return true;
	}

	public function getPath() {
		$m = $this->mods[$this->module];
		$result = $this->getDomain($this->type, $this->module) . $m;
		return $result;
	}

	public function getMode($descript=false) {
		if ( is_null($this->mode) ) {
			$this->getDomain();
		}
		return $descript ? $this->modes[$this->mode] : $this->mode;
	}

	public function getDomain($type='', $mod='') {
		// $this->host = 'dev.m.baike.leju.com:8080';	// for test
		$prefix = str_replace($this->deploy, '', $this->host);
		$port = '';
		$prefix = explode(':', $prefix);
		if ( count($prefix) > 1 ) {
			$port = intval($prefix[1]);
			$port = $port > 0 ? $port : '';
		}
		$prefix = explode('.', trim($prefix[0], '.'));
		$len = count($prefix);
		$result = array();
		switch($len) {
			case 2:
				$md = $prefix[0];
				$t = $prefix[1];
				break;
			case 1:
				$md = array_key_exists($prefix[0], $this->modes) ? $prefix[0] : '';
				$t = array_key_exists($prefix[0], $this->types) ? $prefix[0] : '';
				break;
			case 0:
				$md = $t = '';
				break;
			default:
				$result = false;
		}
		if ( $md !== '' ) {
			if ( $this->detected===true ) {
				array_push($result, $md);
			}
			$this->mode = $md;			
		}
		if ( $type != '' && array_key_exists($type, $this->types) ) {
			$t = $this->types[$type];
		}
		if ( $t !== '' ) {
			array_push($result, $t);
		}
		array_push($result, $this->deploy);
		$result = 'http://'. implode('.', $result);
		if ( $port !== '' ) {
			$result = $result . ':' . $port;
		}
		return $result;
	}

}
