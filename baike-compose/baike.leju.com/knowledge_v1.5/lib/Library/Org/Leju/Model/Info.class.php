<?php
/**
 * 数据源模型
 */
namespace Org\Leju\Model;

class Info {
	protected $autoCheckFields = false;

	protected $config = null;
	protected $where = array();

	protected $page = 1;
	protected $pagesize = 100;
	protected $_cost = 0.0;

	public function __construct () {
	}

	public function setConfig( &$config ) {
		if ( $config && is_array($config) ) {
			$this->config =& $config;
			$this->resetWhere();
		}
	}

	// 测试数据源连通性
	public function ping() { return true; }


	/**
	 * 向新闻池推送数据
	 */
	public function pushData($data) {
		$this->setData($data);
  		return $this->doRequest('push');
	}


	/**
	 * 按条件查询数据
	 */
	public function query( $where=array(), $page=1, $pagesize=100 ) {
		$this->resetWhere();
		$this->setWhere($where, $page, $pagesize);
		// var_dump($this->config, $this->where);
		// echo '[DEBUG]InfoModel::Query(', print_r($where, true), ')', PHP_EOL;
		return $this->doRequest();
	}

	/**
	 * 执行 http request 查询
	 */
	protected function doRequest ($apiname='api') {
		if ( !array_key_exists($apiname, $this->config) ) {
			$apiname = 'api'; // 默认只允许查询
		}
		$this->_cost = microtime(true);
		$result = curl_post($this->config[$apiname], $this->where);
		$this->_cost = microtime(true) - $this->_cost;
		$info = ( $result['status']==true ) ? json_decode($result['result'], true) : array();
		return $info;
	}

	protected function checkConfig() {
		if ( !is_array($this->config) ) {
			die('InfoModel 的 config 未设置。');
		}
		$must_have = array('api'=>'', 'type'=>'', 'appid'=>'', 'key'=>'');
		$check = array_intersect_key($must_have, $this->config);
		if ( count($check)!=count($must_have) ) {
			die('InfoModel 的 config 不完整。');
		}
		return true;
	}

	/**
	 * 重置查询条件
	 */
	protected function resetWhere() {
		$this->where = array();
	}

	/**
	 * 设置查询条件
	 */
	protected function setWhere($where=array(), $page, $pagesize) {
		$this->checkConfig();
		if ( empty($this->where) ) {
			$this->where = array(
				'type' => $this->config['type'],
				'appid' => $this->config['appid'],
				'ver' => '2.0',
				'count' => 1,
				'field' => '',
				'page' => $page, 
				'pcount' => $pagesize,
			);
		}

		// 查询条件处理
		if ( isset($where['filters']) && is_array($where['filters']) ) {
			$filters = $where['filters'];
			unset($where['filters']);
		} else {
			$filters = array();
		}
		foreach ( $where as $k => $opt ) {
			if ( substr($k, 0, 6)=='filter' ) {
				$filters[] = $opt;
				unset($where[$k]);
			}
		}
		if ( !empty($filters) ) {
			$filters = array_unique($filters);
			$inx = 1;
			foreach ( $filters as $i => $filter ) {
				$this->where['filter'.$inx] = $filter;
				$inx ++ ;
			}
		}
		unset($filters);
		unset($this->where['sign']);
		$this->where = array_merge($this->where, $where);
		$this->where['sign'] = $this->_genSign();
		return true;
	}

	/**
	 * 推送数据时的条件设置
	 */
	protected function setData($data) {
		$this->checkConfig();
		$this->resetWhere();
		if ( empty($this->where) ) {
			$this->where = array(
				'type' => $this->config['type'],
				'appid' => $this->config['appid'],
				'ver' => '2.0',
				'count' => 1,
			);
		}

		$this->where = array_merge($this->where, $data);
		$this->where['sign'] = $this->_genSign($this->where['data']);
		return true;
	}

	/**
	 * 生成新闻池数据验证码
	 */
	protected function _genSign($data=false) {
		$string = '';

		$data = false!==$data ? $data : $this->where;
		if(is_array($data)) {
			foreach ($data as $v) {
				$string .= $v;
			}
		} else {
			$string = $data;
		}
		$md5 = md5($string.$this->config['key']);
		return $md5;
	}



}