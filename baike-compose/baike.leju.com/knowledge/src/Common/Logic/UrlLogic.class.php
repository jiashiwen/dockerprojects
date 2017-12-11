<?php
/**
 * 生成和处理 url 的规则
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class UrlLogic {
	static protected $_instance = null;
	protected $deployed = false;	// 为真时，为使用自定义路由机制
	// 自动检测部署模式
	protected $detected = false;
	// 部署域名
	protected $deploy = 'baike.leju.com';
	protected $deploy_ask = 'ask.leju.com';
	protected $deploy_baike = 'baike.leju.com';
	protected $host_ask = '';
	protected $host_baike = '';

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
			if ( $this->deployed ) {
				if ( $city == '' ) {
					$path = '';
					// $path = 'index.html';
				} else {
					$path = "index.html";
				}
			} else {
				$path = '';
			}
			return $this->getPath() . $path;
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
		// http://ld.m.baike.leju.com/cate-id.html
		public function touchBaikeCate($id) {
			$path = $this->deployed ?
						"cate-{$id}.html"
						:
						"cate?id={$id}";
			return $this->getPath() . $path;
		}

		// Baike 信息列表页 页面
		// http://ld.m.baike.leju.com/list-id.html
		public function touchBaikeList($id) {
			$path = $this->deployed ?
						"list-{$id}.html"
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
		// http://ld.m.baike.leju.com/search.html?keyword=keyword
		public function touchBaikeSearch($keyword='') {
			$path = $this->deployed ?
						"search.html"
						:
						"search";
			if ( $keyword != '' ) {
				$path = $path . '?keyword='.urlencode($keyword);
			}
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
	// // // // // // // // // // // Knowledge PC // // // // // // // // // // //
		// Baike Index 页面
		public function pcBaikeIndex($cid=0, $city='') {
			if ( $this->deployed ) {
				if ( $city == '' ) {
					if ( $cid==0 ) {
						$path = '';
					} else {
						$path = 'index' . (intval($cid) > 0 ? "-{$cid}" : '') . '.html';
					}
				} else {
					$path = "index" . (intval($cid) > 0 ? "-{$cid}" : '') . '.html';
				}
			} else {
				$path = "?id={$cid}";
			}
			return $this->getPath() . $path;
		}

		// Baike 分类列表页 页面
		// http://ld.baike.leju.com/cate-id-page.html
		// /cate/?id=1&page=1
		public function pcBaikeCate($id, $page=1) {
			$page = $page!='#' ? intval($page) : '#';
			if ( $this->deployed ) {
				$path = $page > 1 || $page=='#' ? "cate-{$id}-{$page}.html" : "cate-{$id}.html";
			} else {
				$path = "cate?id={$id}&page={$page}";
			}
			return $this->getPath() . $path;
		}

		// Baike 标签聚合列表页 页面
		// http://ld.baike.leju.com/agg-tag.html
		// /agg/?page=1&tag=房产&city=bj&id=1
		public function pcBaikeAgg($tag='', $id='', $page=1) {
			$page = $page!='#' ? intval($page) : '#';
			if ( $this->deployed ) {
				$path = $page > 1 || $page=='#' ? "agg-{$tag}-{$id}-{$page}.html" : "agg-{$tag}-{$id}-1.html";
			} else {
				$path = "agg?tag={$tag}&id={$id}&page={$page}";
			}
			return $this->getPath() . $path;
		}

		// Baike 信息列表页 页面
		// http://ld.baike.leju.com/show-95.html
		public function pcBaikeShow($id) {
			$path = $this->deployed ?
						"show-{$id}.html"
						:
						"show?id={$tag}";
			return $this->getPath() . $path;
		}

		// Baike 信息列表页 页面
		// http://ld.baike.leju.com/search-keyword.html
		public function pcBaikeSearch($keyword) {
			$path = $this->deployed ?
						"search.html?keyword=".urlencode($keyword)
						:
						"search?keyword=".urlencode($keyword);
			return $this->getPath() . $path;
		}

		// Baike / Tag 联想词 接口
		// http://ld.baike.leju.com/suggests.html
		public function pcBaikeSuggests($keyword) {
			$path = $this->deployed ?
						"suggests.html?keyword={$keyword}"
						:
						"suggestpc?keyword={$keyword}";
			return $this->getPath() . $path;
		}

		// Baike 站点地图
		// http://ld.baike.leju.com/sitemap.xml
		public function pcBaikeSitemap($business, $group='') {
			return $this->sitemap($business, $group);
		}
		public function touchBaikeSitemap($business, $group='') {
			return $this->sitemap($business, $group);
		}
		public function pcWikiSitemap($business, $group='') {
			return $this->sitemap($business, $group);
		}
		public function touchWikiSitemap($business, $group='') {
			return $this->sitemap($business, $group);
		}
		protected function sitemap($business, $group='') {
			$path = $group == '' ?
						"sitemap-{$business}.xml"
						:
						"sitemap-{$business}-{$group}.xml";
			return $this->getPath() . $path;
		}
	// // // // // // // // // // // Wiki Touch // // // // // // // // // // //
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
						"cate-{$cateid}.html"
						:
						"list?cateid={$cateid}";
			return $this->getPath(). $path;
		}

		// Wiki 详情 页面
		public function touchWikiShow($word, $cateid=0) {
			if ( $this->deployed ) {
				$cateid = intval($cateid);
				if ( $cateid==1 ) {
					$result = "http://test.m.news.leju.com/company/{$word}.html";
				}
				if ( $cateid==2 ) {
					$result = "http://test.m.news.leju.com/person/{$word}.html";
				}
				if ( !in_array($cateid, [1,2]) ) {
					$result = $this->getPath() . "word-{$word}.html";
				}
			} else {
				$result = $this->getPath() . "show?id={$word}";
			}
			return $result;
		}

		// Wiki 搜索结果 页面
		public function touchWikiSearch($keyword) {
			$path = $this->deployed ?
						"search.html?word={$keyword}"
						// "search-{$keyword}.html"
						:
						"search?word={$keyword}";
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

	// // // // // // // // // // Wiki PC // // // // // // // // // // // //
		// Wiki Index 页面
		public function pcWikiIndex() {
			$url = $this->getPath();
			return $url;
		}

		// Wiki 列表 页面 - 所有词条
		public function pcWikiListall($page='') {
			$page = $page!='#' ? intval($page) : '#';
			if ( $this->deployed ) {
				$path = $page > 1 || $page=='#' ? "listall-{$page}.html" : "listall.html";
			} else {
				$path = $page >= 1 ? "list/all?page={$page}" : "list/all";
			}
			return $this->getPath(). $path;
		}

		// Wiki 列表 页面 - 所有词条
		public function pcWikiAgg($tag='', $page=1) {
			$page = $page!='#' ? intval($page) : '#';
			if ( $this->deployed ) {
				$path = $page > 1 || $page=='#' ? "agg-{$tag}-{$page}.html" : "agg-{$tag}-1.html";
			} else {
				$path = $page >= 1 ? "agg?tag={$tag}&page={$page}" : "agg";
			}
			return $this->getPath(). $path;
		}

		// Wiki 详情 页面
		public function pcWikiShow($word, $cateid=0) {
			if ( $this->deployed ) {
				$cateid = intval($cateid);
				// if ( $cateid==1 ) {
				// 	$result = "http://test.m.news.leju.com/company/{$word}.html";
				// }
				// if ( $cateid==2 ) {
				// 	$result = "http://test.m.news.leju.com/person/{$word}.html";
				// }
				// if ( !in_array($cateid, [1,2]) ) {
					$result = $this->getPath() . "word-{$word}.html";
				// }
			} else {
				$result = $this->getPath() . "show?id={$word}";
			}
			return $result;
		}

		// Wiki 搜索结果 页面
		public function pcWikiSearch($keyword) {
			$path = $this->deployed ?
						"search.html"
						:
						"search/?word={$keyword}&page={$page}";
			return $this->getPath(). $path;
		}

		// Wiki 搜索结果 - 联想词 接口
		public function pcWikiSuggest($keyword) {
			$path = $this->deployed ?
						"suggest.html"
						:
						"search/suggest";
			return $this->getPath(). $path;
		}

	/* Touch 版本路由表 */
	/** ---- URL 路由表设置 ---- **/

	// // // // // // // // // // // Ask PC // // // // // // // // // // //
		// Ask Index 页面
		public function pcAskIndex() {
			$path = '';
			if ( $this->deployed ) {
				$path = '';
			}
			return $this->getPath() . $path;
		}
		// Ask 提问 页面
		public function pcAskAsk() {
			$path = 'profile/ask/';
			return $this->getPath() . $path;
		}
		// Ask List 页面
		public function pcAskList($cateid, $page=1, $order='') {
			$path = 'list'.$cateid;
			$path .= $order=='zdhf' ? '-zdhf' : '';
			if ( $page=='#' ) {
				$path .= '-#/';
			} else {
				$path .= ( $page==1 ? '' : '-'.$page ) . '/';
			}
			return $this->getPath() . $path;
		}
		// Ask 标签聚合 页面
		public function pcAskAgg($tagid, $page=1, $order='') {
			$path = 'tag'.$tagid;
			$path .= $order=='zdhf' ? '-zdhf' : '';
			// $path .= ( $page==1 ? '' : '-'.$page ) . '/'; 
			if ( $page=='#' ) {
				$path .= '-#/';
			} else {
				$path .= ( $page==1 ? '' : '-'.$page ) . '/';
			}
			return $this->getPath() . $path;
		}
		// Ask 标签搜索结果 页面
		public function pcAskSearch($keyword, $page=1, $order='') {
			// $path = 'search/'.$keyword;
			// $path .= ( $page==1 ? '' : '-'.$page ) .'/'; 
			$path = 'search/';
			$opts = array('k'=>$keyword);
			if ( $page>1 ) {
				$opts['page'] = $page;
			}
			$path .= '?' . http_build_query($opts);
			if ( $order=='zdhf' ) {
				$path .= '&zdhf';
			}
			if ( $page=='#' ) {
				$path .= '&page=#';
			}
			return $this->getPath() . $path;
		}
		// Ask 详情 页面
		public function pcAskShow($qid, $page=1) {
			$path = $qid;
			$path .= ( $page==1 ? '' : '-'.$page ) . '.html'; 
			return $this->getPath() . $path;
		}
		// Ask 用户中心
		public function pcAskProfile($type, $page=1) {
			$types = array('recommends', 'questions', 'answers', 'attentions', 'todo', 'index');
			// index == recommends
			if ( !in_array($type, $types) ) {
				$type = $types[0];
			}
			$path = 'profile'.( $type!='' ? '/'.$type : '').'';
			$path .= ( $page==1 ? '' : '-'.$page ) . '/'; 
			return $this->getPath() . $path;
		}
		// 问答异常页面
		public function pcAskError() {
			return $this->getPath() . 'error';
		}
		public function pcAskException() {
			return $this->getPath() . 'error/exception';
		}
	// // // // // // // // // // // Ask Touch // // // // // // // // // // //

		// Ask Index 页面
		public function touchAskIndex() {
			$path = '';
			if ( $this->deployed ) {
				$path = '';
			}
			return $this->getPath() . $path;
		}
		// Ask 提问 页面
		public function touchAskAsk() {
			$path = 'profile/ask/';
			return $this->getPath() . $path;
		}
		// Ask List 页面
		public function touchAskList($cateid, $page=1, $order='') {
			$path = 'list'.$cateid;
			$path .= $order=='zdhf' ? '-zdhf' : '';
			if ( $page=='#' ) {
				$path .= '-#/';
			} else {
				$path .= ( $page==1 ? '' : '-'.$page ) . '/';
			}
			return $this->getPath() . $path;
		}
		// Ask 标签聚合 页面
		public function touchAskAgg($tagid, $page=1, $order='') {
			$path = 'tag'.$tagid;
			$path .= $order=='zdhf' ? '-zdhf' : '';
			// $path .= ( $page==1 ? '' : '-'.$page ) . '/'; 
			if ( $page=='#' ) {
				$path .= '-#/';
			} else {
				$path .= ( $page==1 ? '' : '-'.$page ) . '/';
			}
			return $this->getPath() . $path;
		}
		// Ask 标签搜索结果 页面
		public function touchAskSearch($keyword, $page=1, $order='') {
			// $path = 'search/'.$keyword;
			// $path .= ( $page==1 ? '' : '-'.$page ) .'/'; 
			$path = 'search/';
			$opts = array('k'=>$keyword);
			if ( $page>1 ) {
				$opts['page'] = $page;
			}
			$path .= '?' . http_build_query($opts);
			if ( $order=='zdhf' ) {
				$path .= '&zdhf';
			}
			if ( $page=='#' ) {
				$path .= '&page=#';
			}
			return $this->getPath() . $path;
		}
		// Ask 详情 页面
		public function touchAskShow($qid, $page=1) {
			$path = $qid;
			$path .= ( $page==1 ? '' : '-'.$page ) . '.html'; 
			return $this->getPath() . $path;
		}
		// Ask 用户中心
		public function touchAskProfile($type, $page=1) {
			$types = array('recommends', 'questions', 'answers', 'attentions', 'todo', 'index');
			// index == recommends
			if ( !in_array($type, $types) ) {
				$type = $types[0];
			}
			$path = 'profile'.( $type!='' ? '/'.$type : '').'';
			$path .= ( $page==1 ? '' : '-'.$page ) . '/'; 
			return $this->getPath() . $path;
		}
		// 问答异常页面
		public function touchAskError() {
			return $this->getPath() . 'error';
		}
		public function touchAskException() {
			return $this->getPath() . 'error/exception';
		}
	// // // // // // // // // // // Ask Touch 乐道 // // // // // // // // // // //

		// Ask 乐道公司问题聚合页
		public function touchAskLDCompany( $company_id ) {
			$path = 'caijing/'.$company_id.'/';
			return $this->getPath() . $path;
		}
		// Ask 乐道 提问 页面
		public function touchAskLDAsk( $company_id ) {
			$path = 'ask/company/?id='.$company_id;
			return $this->getPath() . $path;
		}
		// Ask 乐道 问题详情页 页面
		public function touchAskLDQuestion($question_id) {
			$path = 'caijing/'.$question_id.'.html';
			return $this->getPath() . $path;
		}
		// Ask 乐道 问题详情页 页面
		public function touchAskLDAnswer($question_id, $answer_id) {
			$path = 'caijing/'.$question_id.'/'.$answer_id.'.html';
			return $this->getPath() . $path;
		}
		// Ask 乐道 搜索结果 页面
		public function touchAskLDSearch($keyword) {
			$path = 'search/company/?k='.$keyword;
			return $this->getPath() . $path;
		}

	// // // // // // // // // // // Ask Touch 人物 // // // // // // // // // // //

		// Ask 乐道公司问题聚合页
		public function touchAskPNPerson( $person_id ) {
			$path = 'person/'.$person_id.'/';
			return $this->getPath() . $path;
		}
		// Ask 乐道 提问 页面
		public function touchAskPNAsk( $person_id ) {
			$path = 'ask/person/?id='.$person_id;
			return $this->getPath() . $path;
		}
		// Ask 乐道 问题详情页 页面
		public function touchAskPNQuestion($question_id) {
			$path = 'person/'.$question_id.'.html';
			return $this->getPath() . $path;
		}
		// Ask 乐道 问题详情页 页面
		public function touchAskPNAnswer($question_id, $answer_id) {
			$path = 'person/'.$question_id.'/'.$answer_id.'.html';
			return $this->getPath() . $path;
		}
		// Ask 乐道 搜索结果 页面
		public function touchAskPNSearch($keyword) {
			$path = 'search/person/?k='.$keyword;
			return $this->getPath() . $path;
		}
	// // // // // // // // // // // Common Function // // // // // // // // // // //


	public function __construct($detected=false) {
		$this->host = strtolower($_SERVER['HTTP_HOST']);
		$this->base = C('DOMAINS');
		$this->mods = array(
			'baike'=>'/', 'wiki'=>'/tag/', 'ask'=>'/'
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
		if ( $mod=='ask' ) {
			$this->deploy = $this->deploy_ask;
		} else {
			$this->deploy = $this->deploy_baike;
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
		if ( $mod!='ask' ) {
			$mod = 'baike';
			$omod = 'ask';
		} else {
			$mod = 'ask';
			$omod = 'baike';
		}
		// var_dump($mod, $omod);
		$deploy = 'deploy_'.$mod;
		$this->deploy = $this->$deploy;
		// var_dump($deploy, $this->deploy);
		$prefix = str_replace($this->deploy, '', $this->host);
		if ( $prefix == $this->host ) {
		// var_dump('other');
			$deploy = 'deploy_'.$omod;
			$this->deploy = $this->$deploy;
			$prefix = str_replace($this->deploy, '', $this->host);
		}
		// var_dump($prefix, $this->deploy, $this->host);exit;
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
		if ( $md!=='' ) {
			if ( $this->detected===true ) {
				array_push($result, $md);
			}
		}
		$this->mode = $md;

		if ( $type != '' && array_key_exists($type, $this->types) ) {
			$t = $this->types[$type];
		}
		if ( $t !== '' ) {
			array_push($result, $t);
		}
		$deploy = 'deploy_'.$mod;
		$this->deploy = $this->$deploy;
		array_push($result, $this->deploy);
		$result = 'http://'. implode('.', $result);
		if ( $port !== '' ) {
			$result = $result . ':' . $port;
		}
		return $result;
	}

}
