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

}