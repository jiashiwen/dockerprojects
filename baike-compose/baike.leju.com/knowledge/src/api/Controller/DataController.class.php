<?php
/**
 * 知识系统数据读取接口
 * @author Robert <yongliang1@leju.com>
 * 使用方 : 新闻池
 */
namespace api\Controller;
use Think\Controller;

class DataController extends Controller {

	protected $_dbg = true;
	protected $opts = array();
	protected $rules = array();
	protected $result = array();

	public function index(){
	}

	public function __construct() {
		$this->result = array(
			'status' => true,
			'_dbg' => array(),
			'list' => array(),
			'_prof' => array(),
			'pager' => array(),
		);

		$debug = I('debug', 0, 'intval');
		$this->_dbg = ($debug===35940);
	}

	/**
	 * 通用方法，用于分析可用参数
	 */
	protected function parseParams() {
		$this->opts = I();
		$this->rules = array(
			// 是否带内容中的html标签一起返回 0不返回 1返回
			'withtags'=>0,
			// 指定要读取的分页
			'page'=>1,
			// 指定每次读取的数据量
			'pagesize'=>500,
			// 排序规则
			'order'=>array(
				'field'=>array('ctime','utime'),
				'rule'=>array('desc','asc')
			),
			// 查询条件
			'where'=>array('ctime','utime','tags'),
		);
		$this->opts = array_intersect_key($this->opts, $this->rules);
		$this->opts['withtags'] = $this->opts['withtags'] == 1;
		$ret = !empty($this->opts);
		// 开启调试模式时，将调试内容返回
		$this->_dbg && $this->result['_dbg']['query'] = $this->opts;
		$this->_dbg && $this->result['_dbg']['parse'] = $ret;
		return $ret;
	}

	protected function _prefixHook() {
		G('api_start');
		if ( $this->_dbg ) {
			$this->result['_dbg']['debug'] = $this->_dbg;
		}
	}
	protected function _appendHook() {
		G('api_end');
		$cost = G('api_start', 'api_end', 3);
		$mem = G('api_start', 'api_end', 'm');

		// 操作结束后，判断是否为调试模式，非调试模式时，将调试信息主键从结果中移除
		if ( !$this->_dbg ) {
			unset($this->result['_dbg']);
		}

		return array('cost'=>$cost, 'mem'=>$mem);
	}

	/**
	 * 通过接口获取知识数据 : by 搜索服务
	 */
	public function getKnowledges() {
		$result = &$this->result;
		// 开始时的 钩子
		$this->_prefixHook();
		// 入口，从接口参数中获取接口参数
		$ret = $this->parseParams();

		$result['api'] = 'getknowledges';

		// 将通用参数转换成知识接口使用的参数
		$opts = $this->getKnowledgeParams();
		// 组建查询语句
		$src = D('Search', 'Logic', 'Common');
		$fields = array('_origin');
		$ret = $src->select($opts['page'], $opts['pagesize'], '', $opts['where'], $fields, $opts['order']);
		$this->_dbg && $result['_dbg']['result'] = $ret;
		$result['pager'] = $ret['pager'];
		$list = &$ret['list'];
		if ( count($list) > 0 ) {
			foreach ( $list as $i => &$item ) {
				$item = $item['_origin'];
			}
			$result['list'] = &$list;
		}
		// 结束时的 钩子
		$result['_prof'] = $this->_appendHook();

		echo json_encode($result);
	}
	protected function getKnowledgeParams() {
		$opts = &$this->opts;
		$rules = &$this->rules;

		$opts['page'] = intval($opts['page']);
		$page = $opts['page'] <= 0 ? $rules['page'] : $opts['page'];

		$opts['pagesize'] = intval($opts['pagesize']);
		$pagesize = ( $opts['pagesize'] <=0 || $opts['pagesize'] > $rules['pagesize'] )
					?
						$rules['pagesize']
					:
						$opts['pagesize'];

		$opts['order'] = explode('|', strtolower($opts['order']));
		$order = $opts['order'];
		if ( count($order)>2 ) {
			array_splice($order, 2);
		}
		if ( count($order)==2 ) {
			// 排序规则不符合要求时，使用默认排序规则
			if ( !in_array($order[1], $rules['order']['rule']) ) {
				$order[1] = $rules['order']['rule'][0];
			}
			// 如果设置的不是可排序字段，则清理排序设定，并使用默认排序规则
			if ( !in_array($order[0], $rules['order']['field']) ) {
				$order = array();
			}
		}
		if ( count($order)==1 ) {
			// 如果设置的是可排序字段，但未设置排序规则，默认以 desc 倒排进行排序
			if ( in_array($order[0], $rules['order']['field']) ) {
				array_push($order, 'desc');
			}
			// 未设置排序字段时，忽略排序设置，改用默认排序规则
			if ( in_array($order[0], $rules['order']['rule']) ) {
				unset($order[0]);
			}
		}
		if ( count($order)==0 ) {
			$order = array($rules['order']['field'][0], $rules['order']['rule'][0]);
		}
		$order_fields = array('ctime'=>'_doccreatetime', 'utime'=>'_docupdatetime');
		$order[0] = $order_fields[$order[0]];

		$where_fileds = array('ctime'=>'_doccreatetime', 'utime'=>'_docupdatetime', 'tags'=>'tags', 'city'=>'scope');
		$where = $opts['where'];
		$where = explode('|', $where);
		foreach ( $where as $i => &$w ) {
			$w = explode('@', $w);
			$f = &$w[count($w)-1];
			if ( !in_array($f, $rules['where']) ) {
				unset($where[$i]);
				continue;
			}
			$f = $where_fileds[$f];
		}

		$params = array(
			'page' => $page,
			'pagesize' => $pagesize,
			'order' => $order,
			'where' => $where,
		);
		// 调试模式时，将转换后的查询参数追回到返回结果中
		$this->_dbg && $result['_dbg']['params'] = $params;
		return $params;
	}

	/**
	 * 通过接口获取百科数据
	 * 暂时未有调用
	 */
	public function getWikis() {
		$result = &$this->result;
		// $result['api'] = 'getwikis';
		// 开始时的 钩子
		$this->_prefixHook();
		// @TODO : 添加百科查询调用
		// 
		// 结束时的 钩子
		$result['_prof'] = $this->_appendHook();
		echo json_encode($result);
	}


	/**
	 * 通用方法
	 * 用于后台生成的数据接口逻辑
	 * @param GET id int 接口设定编号
	 * @param GET token string 接口认证令牌
	 * @param GET city string 中文城市名
	 * @param GET device string {pc/touch}
	 * @return JSON
	 */
	public function api () {
		$result = &$this->result;
		// 开始时的 钩子
		$this->_prefixHook();
		// 参数处理
		$id = I('get.id', 0, 'intval');
		$token = I('get.token', '', 'trim,strtolower');
		$city = I('get.city', '', 'trim');
		$device = I('get.device', '', 'trim,strtolower');
		$devices = array('pc', 'touch');
		if ( !in_array($device, $devices) ) {
			$device = 'pc';
		}
		// @TODO 通过数据模型 读取规则
		$cfgs = array(
			1 => array(
				'id' => 1,
				'name' => '房首接口调用',
				'num' => 4,
				'catepath' => '0-',	// 指定一级或二级栏目
				'cateid' => 0,	// 指定栏目
				'tags' => '',	// 标签列表
				// token 生成规则 名称 + 创建时间Ymd + 返回数据量 + 标签列表字符串
				'token' => 'c5606494c4b7cd98246a2a21c5d9450b',
			),
		);
		if ( array_key_exists($id, $cfgs) ) {
			$cities = C('CITIES.ALL');
			if ( array_key_exists($city, $cities) ) {
				$city = $cities[$city]['cn'];
				$citys = ( $city=='北京' ) ? $city . ',全国' : $city;
			} else {
				$citys = '全国';
			}
			$cfg = & $cfgs[$id];
			if ( $cfg['token']==$token ) {
				// 组建接口调用
				$src = D('Search', 'Logic', 'Common');
				$num = &$cfg['num'];
				$total = $num + 10;
				$opts = array();
				$prefix = array();
				$cates = array();
				$opts['scope'] = array($citys, '_scope');
				$time_start = ( intval(strtotime('today')) + 86399 ); // 24 * 3600 - 1
				$time_end = strtotime('-30 days', $time_start+1) * 1000;
				$time_start = $time_start * 1000;
				// $opts['range'] = array("[{$time_end},{$time_start}]", '_docupdatetime');
				// if ( $cfg['tags']!='' ) { $opts['tags'] = array($cfg['tags'], '_tags'); }
				if ( $cfg['cateid']!=0 ) {
					$opts['cateid'] = array($cfg['cateid'], '_multi.cateid');
				} else {
					$prefix['catepath'] = array($cfg['catepath'], '_multi.catepath');
				}
				// $ret = $src->getRecommendBaike( $total, $prefix, $opts );
				$page = 1;
				$keyword ='';
				$order = ['_docupdatetime', 'desc'];
				$fields = ['_id','_tags','_title','_multi.catepath','_origin.scope', '_origin.cover', '_docupdatetime', '_scope'];
				$ds = 0;
				$business = 'knowledge';
				$ret = $src->select($page, $total, $keyword, $opts, $prefix, $order, $fields, $ds, $business);
				$this->_dbg && $result['_dbg']['result'] = $ret;
				$this->_dbg && $result['_dbg']['opts'] = $opts;
				$this->_dbg && $result['_dbg']['prefix'] = $prefix;
				$result['home'] = url('index', array(), $device, 'baike');
				$result['tags'] = array();
				if ( $ret['list'] ) {
					$result['list'] = array();
					foreach ( $ret['list'] as $i => $_item ) {
						if ( $cfg['cateid']==0 && empty($cates) && intval($cates[1])==0 ) {
							$cates = explode('-', $_item['_multi']['catepath']);
						}
						array_push($result['list'], array(
							'title' => $_item['_title'],
							'url' => url('show', array('id'=>$_item['_id']), $device, 'baike'),
						));
						$tags = explode(' ', $_item['_tags']);
						$_tag_filters = array('', '删除');
						foreach ( $tags as $k => &$tag ) {
							$tag = explode(' ', trim($tag));
							foreach ( $tag as $_k => $_tag ) {
								if ( in_array(trim($_tag), $_tag_filters) ) {
									continue;
								}
								$md5 = md5($_tag);
								$result['tags'][$md5] = $_tag;
							}
						}
					}
					// 默认显示的栏目分类
					$cateid = ( $cfg['cateid']==0 ) ? $cates[1] : $cfg['cateid'];
					// 将 Tag 字符串转换为 Tag.id 再拼 url
					$ids = '';
					if (!empty($result['tags'])) {
						$taglist = D('Tags', 'Logic', 'Common')->getTagnamesByTags(implode(',', $result['tags']));
						$result['tags'] = array();
						foreach ( $taglist as $i => $info ) {
							$tag = trim($info['name']);
							$md5 = md5($tag);
							$result['tags'][$md5] = array(
								'tag' => $tag,
								'url' => url('agg', array('tag'=>$info['id'], 'id'=>$cateid), $device, 'baike'),
							);
						}
					}
				}
				$result['list'] = array_slice($result['list'], 0, $num);
				$result['tags'] = array_values($result['tags']);
				$result['tags'] = array_slice($result['tags'], 0, 6);
				$result['total'] = $ret['pager']['total'];
			} else {
				$result['status'] = false;
				$result['msg'] = '没有权限调用此接口!';
			}
		} else {
			$result['status'] = false;
			$result['msg'] = '没有指定的接口设定!';
		}
		unset($result['pager']);
		// 结束时的 钩子
		$result['_prof'] = $this->_appendHook();
		echo json_encode($result);
	}

	/**
	 * 为微信小程序提供数据接口
	 * mantis#87788 @20170207
	 */
	public function wxapp () {
		$result = &$this->result;
		// 开始时的 钩子
		$this->_prefixHook();

		// 参数处理
		$id = I('get.id', 0, 'intval');
		$token = I('get.token', '', 'trim,strtolower');
		$city = I('get.city', '', 'trim');
		$device = I('get.device', 'touch', 'trim,strtolower');
		$devices = array('pc', 'touch');
		if ( !in_array($device, $devices) ) {
			$device = 'pc';
		}

		// @TODO 通过数据模型 读取规则
		$cfgs = array(
			2 => array(
				'id' => 2,
				'name' => '房贷计算器',
				'num' => 4,
				'catepath' => array( 	// 指定一级或二级栏目
					'0-1-20-',			// 新房知识 - 贷款还款
					'0-2-64-71',		// 二手房 - 买房 - 贷款流程 / 全款
				),
				'cateid' => '',	// 指定栏目
				'tags' => '贷款',	// 标签列表
				'keyword' => '', // 搜索关键词
				'createtime' => 1486459493, // 创建时间
				'order' => 'random',	// 排序规则 : 随机
				'scope' => '',			// 指定城市范围
				'range' => '',			// 指定数据范围
				'extra' => url('search', array('keyword'=>'贷款'), 'touch', 'baike'), // 扩展链接 打开贷款的搜索页面
				// token 生成规则 名称 + 创建时间Ymd + 返回数据量 + 标签列表字符串
				'token' => 'e1a8a85f31a2d601c0cf5b45d7af0558',
			),
			3 => array(
				'id' => 3,
				'name' => '税费计算器',
				'num' => 4,
				'catepath' => array( 	// 指定一级或二级栏目
					'0-',
				),
				'cateid' => '',	// 指定栏目
				'tags' => '',	// 标签列表
				'keyword' => '税', // 搜索关键词
				'createtime' => 1486459493, // 创建时间
				'order' => 'random',	// 排序规则 : 随机
				'scope' => '',			// 指定城市范围
				'range' => '',			// 指定数据范围
				'extra' => url('search', array('keyword'=>'税'), 'touch', 'baike'), // 扩展链接 打开税的搜索页面
				// token 生成规则 名称 + 创建时间Ymd + 返回数据量 + 标签列表字符串
				'token' => '8471dfdfec9998ed7d567fd3df245ae5',
			),
			4 => array(
				'id' => 4,
				'name' => '家居计算器',
				'num' => 4,
				'catepath' => array( 	// 指定一级或二级栏目
					'0-3-',
				),
				'cateid' => '',	// 指定栏目
				'tags' => '',	// 标签列表
				'keyword' => '', // 搜索关键词
				'createtime' => 1486459493, // 创建时间
				'order' => 'random',	// 排序规则 : 随机
				'scope' => '',			// 指定城市范围
				'range' => '',			// 指定数据范围
				'extra' => url('cate', array('id'=>3), 'touch', 'baike'), // 扩展链接 打开家居分类页
				// token 生成规则 名称 + 创建时间Ymd + 返回数据量 + 标签列表字符串
				'token' => 'dcb046834f49ef09185c81faeaa2f640',
			),
			5 => array(
				'id' => 5,
				'name' => '装修计算器',
				'num' => 4,
				'catepath' => array( 	// 指定一级或二级栏目
					'0-4-',
				),
				'cateid' => '',	// 指定栏目
				'tags' => '',	// 标签列表
				'keyword' => '', // 搜索关键词
				'createtime' => 1486459493, // 创建时间
				'order' => 'random',	// 排序规则 : 随机
				'scope' => '',			// 指定城市范围 空为所有
				'range' => '',			// 指定数据范围 空为所有
				'extra' => url('cate', array('id'=>4), 'touch', 'baike'), // 扩展链接 打开装修分类页
				// token 生成规则 名称 + 创建时间Ymd + 返回数据量 + 标签列表字符串
				'token' => '3ed90b0e3e60796160505b38d0bf8a54',
			),
		);

		if ( array_key_exists($id, $cfgs) ) {
			$cities = C('CITIES.ALL');
			$citys = array('全国');
			if ( array_key_exists($city, $cities) ) {
				array_unshift($citys, $cities[$city]['cn']);
			} else {
				$city = '';
			}
			$cfg = & $cfgs[$id];
			if ( $cfg['token']==$token ) {
				// 组建接口调用
				$src = D('Search', 'Logic', 'Common');
				$num = &$cfg['num'];
				$total = $num + 10;
				$opts = array();
				$prefix = array();
				$cates = array();
				// 如果设置了关键词过滤，则添加标题中包含的关键词
				$keyword = false;
				if ( $cfg['keyword']!=='' ) {
					$keyword = $cfg['keyword'];
				}
				// 数据的城市属性
				if ( $cfg['scope']!=='' ) {
					$opts['scope'] = array(implode(',', $citys), '_scope');
				}
				// 数据的时间范围属性
				if ( $cfg['range']!=='' ) {
					$time_start = ( intval(strtotime('today')) + 86399 ); // 24 * 3600 - 1
					$time_end = strtotime($cfg['range'], $time_start+1) * 1000;
					$time_start = $time_start * 1000;
					$opts['range'] = array("[{$time_end},{$time_start}]", '_docupdatetime');
				}
				if ( $cfg['tags']!=='' ) { $opts['tags'] = array($cfg['tags'], '_tags'); }
				// 分类属性为开关，如果指定一个明确的分类，则使用分类筛选，否则通过前匹配多个分类
				if ( $cfg['cateid']!=='' ) {
					$opts['cateid'] = array($cfg['cateid'], '_multi.cateid');
				} else {
					$prefix['catepath'] = array(implode(',', $cfg['catepath']), '_multi.catepath');
				}
				$ret = $src->getRecommendBaike( $total, $prefix, $opts, $keyword );
				$this->_dbg && $result['_dbg']['result'] = $ret;
				$this->_dbg && $result['_dbg']['opts'] = $opts;
				$this->_dbg && $result['_dbg']['prefix'] = $prefix;
				$this->_dbg && $result['_dbg']['keyword'] = $keyword;
				$this->_dbg && $result['_dbg']['config'] = $cfg;
				if ( $ret['result'] ) {
					$result['list'] = array();
					foreach ( $ret['result'] as $i => $_item ) {
						if ( empty($cates) ) {
							$cates = explode('-', $_item['_multi']['catepath']);
						}
						array_push($result['list'], array(
							'title' => $_item['_title'],
							'url' => url('show', array('id'=>$_item['_id']), $device, 'baike'),
						));
					}
				}
				$result['list'] = array_slice($result['list'], 0, $num);
				$result['total'] = $ret['pager']['total'];
				// 扩展属性
				$result['home'] = url('index', array(), $device, 'baike');
				$result['extra'] = $cfg['extra'];
			} else {
				$result['status'] = false;
				$result['msg'] = '没有权限调用此接口!';
			}
		} else {
			$result['status'] = false;
			$result['msg'] = '没有指定的接口设定!';
		}
		unset($result['pager']);
		// 结束时的 钩子
		$result['_prof'] = $this->_appendHook();
		echo json_encode($result);
	}

	/**
	 * 提供触屏业务接口
	 * @city => 城市约束，指定 city_en 参数
	 */
	public function touch () {
		$result = &$this->result;
		// 开始时的 钩子
		$this->_prefixHook();
		// 参数处理
		$id = I('get.id', 0, 'intval');
		$token = I('get.token', '', 'trim,strtolower');
		$city = I('get.city', 'bj', 'trim,strtolower');
		$num = I('get.num', 3, 'intval');

		$cities = C('CITIES.ALL');
		if ( !array_key_exists($city, $cities) ) {
			$this->api_error('没有指定的城市数据');
		}

		// @TODO 通过数据模型 读取规则
		$cfgs = array(
			1 => array(
				'id' => 1,
				'name' => '触屏新版房首接口调用',
				'num' => 3,
				'catepath' => '0-',	// 指定一级或二级栏目
				'cateid' => 0,	// 指定栏目
				'tags' => '',	// 标签列表
				// token 生成规则 名称 + 创建时间Ymd + 返回数据量 + 标签列表字符串
				'token' => 'c23a9b3cb42344d2b43efd7be3e9cb2c',
			),
		);

		if ( !array_key_exists($id, $cfgs) ) {
			$this->api_error('没有指定的接口设定!');
		}

		$cfg = & $cfgs[$id];
		if ( $cfg['token']!=$token ) {
			$this->api_error('没有权限调用此接口!');
		}

		/*
		$citys = array('全国');
		if ( array_key_exists($city, $cities) ) {
			array_unshift($citys, $cities[$city]['cn']);
		} else {
			$city = '';
		}*/

		// 推荐栏目列表及链接
		$result['cates'] = array(
			array('name'=>'购房资格', 'id'=> 6, 'url'=>'http://m.baike.leju.com/list-6.html',),
			array('name'=>'选房技巧', 'id'=>11, 'url'=>'http://m.baike.leju.com/list-11.html',),
			array('name'=>'面积户型', 'id'=>13, 'url'=>'http://m.baike.leju.com/list-13.html',),
			array('name'=>'楼层地段', 'id'=>14, 'url'=>'http://m.baike.leju.com/list-14.html',),
			array('name'=>'认购要求', 'id'=>16, 'url'=>'http://m.baike.leju.com/list-16.html',),
			array('name'=>'认购流程', 'id'=>17, 'url'=>'http://m.baike.leju.com/list-17.html',),
			array('name'=>'新房签约', 'id'=>19, 'url'=>'http://m.baike.leju.com/list-19.html',),
		);

		// @TODO: 读取王学良的接口
		$Conf = C('STAT');
		$api = &$Conf['GETRANK']['api'];
		$ids = implode(',', array(5,10,15,18));
		$params = array(
			'app_key' => $Conf['key'],
			// 'plat_key' => 'touch', 暂时按全站访问统计取数据
			'time_type' => 'month',
			'limit' => $num * 5,
			'city_en' => $city, 
			'level2' => $ids,
		);
		$list = array();
		do {
			$ret = curl_get($api, $params);
		} while ( !$ret['status'] );
		// var_dump($params, $ret);
		$ret = json_decode($ret['result'], true);
		if ( $ret['result']['status'] ) {
			// $kb_ids = array();
			$sorted = array();
			$filter = array_flip(array('unique_id', 'month_stat', 'title', 'city_en', 'cate_id'));
			foreach ( $ret['data'] as $i => $item ) {
				// $kb_ids[] = $item['unique_id'];
				$list[$item['unique_id']] = array(
					'id' => $item['unique_id'],
					'title' => $item['title'],
					'scope' => $item['city_en'],
					'cateid' => $item['cate_id'],
					'hits' => $item['month_stat'],
					'url' => url('show', array($item['unique_id']), 'touch', 'baike'),
				);
				$sorted[$item['unique_id']] = $item['month_stat'];
			}

			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$where = array(
				'id' => array( 'in', array_keys($sorted) ),
				'status' => 9,
			);
			$fields = array('id', /* 'title', 'scope', 'cateid',*/ 'tags');
			$ret = $mKnowledge->field($fields)->where($where)->select();
			// var_dump('db', $ret, $mKnowledge->getLastSql());
			if ( !empty($ret) ) {
				foreach ( $ret as $i => $item ) {
					if ( !array_key_exists($item['id'], $list) ) {
						unset($list[$i]);
					} else {
						$list[$item['id']]['tags'] = $item['tags'];
					}
				}
				$list = array_slice($list, 0, $num);
			} else {
				$list = array();
			}
		}
		// var_dump('result', $list);exit;
		$result['list'] = $list;

		unset($result['pager']);
		// 结束时的 钩子
		$result['_prof'] = $this->_appendHook();
		$this->api_success($result);
	}

	public function api_error ( $msg='' ) {
		set_ajax_output(true);
		$result = array(
			'status' => false,
			'msg' => $msg,
		);
		echo json_encode($result);
	}

	public function api_success ( $result ) {
		set_ajax_output(true);
		echo json_encode($result);
	}
}