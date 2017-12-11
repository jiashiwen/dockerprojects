<?php
/**
 * 知识系统数据读取接口
 * @author Robert <yongliang1@leju.com>
 * 使用方: 新闻池
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
	 * 通过接口获取百科数据 : by 寰宇接口
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
		$device = I('get.device', '', 'time,strtolower');
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
				$opts['scope'] = array(implode(',', $citys), '_scope');
				$time_start = ( intval(strtotime('today')) + 86399 ); // 24 * 3600 - 1
				$time_end = strtotime('-30 days', $time_start+1) * 1000;
				$time_start = $time_start * 1000;
				$opts['range'] = array("[{$time_end},{$time_start}]", '_docupdatetime');
				if ( $cfg['tags']!='' ) { $opts['tags'] = array($cfg['tags'], '_tags'); }
				if ( $cfg['cateid']!=0 ) {
					$opts['cateid'] = array($cfg['cateid'], '_multi.cateid');
				} else {
					$prefix['catepath'] = array($cfg['catepath'], '_multi.catepath');
				}
				$ret = $src->getRecommendBaike( $total, $prefix, $opts );
				$this->_dbg && $result['_dbg']['result'] = $ret;
				$this->_dbg && $result['_dbg']['opts'] = $opts;
				$this->_dbg && $result['_dbg']['prefix'] = $prefix;
				$result['home'] = url('index', array(), $device, 'baike');
				$result['tags'] = array();
				if ( $ret['result'] ) {
					$result['list'] = array();
					foreach ( $ret['result'] as $i => $_item ) {
						if ( empty($cates) ) {
							$cates = explode('-', $_item['_multi']['catepath']);
						}
						array_push($result['list'], array(
							'title' => $_item['_title'],
							// 'tags' => $_item['_tags'],
							'url' => url('show', array('id'=>$_item['_id']), $device, 'baike'),
						));
						$tags = explode(' ', $_item['_tags']);
						foreach ( $tags as $k => &$tag ) {
							$tag = trim($tag);
							$md5 = md5($tag);
							$result['tags'][$md5] = array(
								'tag' => $tag,
								'url' => url('agg', array('tag'=>$tag, 'city'=>$city, 'id'=>$cates[1]), $device, 'baike'),
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

}