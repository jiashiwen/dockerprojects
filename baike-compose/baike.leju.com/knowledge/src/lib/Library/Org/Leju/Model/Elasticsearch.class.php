<?php
/**
 * 数据源模型
 */
namespace Org\Leju\Model;

class Elasticsearch {
	protected $autoCheckFields = false;

	protected $config = null;
	protected $where = array();

	protected $page = 1;
	protected $pagesize = 100;
	protected $_cost = 0.0;

	protected $client = null;

	protected $logic = null;
	protected $origin = null;

	protected $logic_data = null;
	protected $origin_data = null;

	protected $query = array();

	public function __construct () {
	}

	public function setConfig( &$config ) {
		if ( $config && is_array($config) ) {
			$this->config =& $config;
		}
	}

	/**
	 * 获取 ElasticSearch 客户端实例
	 */
	protected function getES () {
		if ( is_null($this->client) ) {
			spl_autoload_register(
				function($class){
					$base_dir = '/lib/Library/Vendor/Elastica/';
					if ( substr($class,0,8)=='Elastica' ) {
						$class = substr($class, 9);
					}
					$path = str_replace('\\', '/', $class);
					// echo 'Need load the lib of ', $class, PHP_EOL,
					// 	 ':: file path :: ', WEB_ROOT.'|'. $base_dir .'|'. $path . '.php' , PHP_EOL;
					if (file_exists(WEB_ROOT. $base_dir . $path . '.php')) {
						require_once(WEB_ROOT. $base_dir . $path . '.php');
					}
				}
			);

			$cluster = C('ES_CLUSTER');
			$node_inx = array_rand($cluster);
			$this->client = new \Elastica\Client($cluster[$node_inx]);
		}
		return $this->client;
	}

	protected function doGET( $path, $query=false ) {
		if ( $path == '' ) {
			return false;
		}
		if ( $query==false ) {
			$query = $this->query;
		}

		try {
			$response = $this->client->request( $path, \Elastica\Request::GET, $query );	
		} catch ( Exception $e ) {
			echo $e->getMessage();
			die();
		}
		
		return $response->getData();
	}

	protected function doPOST( $path, $query=false ) {
		if ( $path == '' ) {
			return false;
		}
		if ( $query==false ) {
			$query = $this->query;
		}
		try {
			$response = $this->client->request( $path, \Elastica\Request::POST, $query );	
		} catch ( Exception $e ) {
			echo $e->getMessage();
			die();
		}
		
		return $response->getData();
	}

	protected function doDELETE( $path, $query=false ) {
		if ( $path == '' ) {
			return false;
		}
		if ( $query==false ) {
			$query = $this->query;
		}

		try {
			$response = $this->client->request( $path, \Elastica\Request::DELETE, $query );
		} catch ( Exception $e ) {
			echo $e->getMessage();
			die();
		}

		return $response->getData();
	}

	/**
	 * 更新数据
	 */
	public function upsertData( $data=array() ) {
		if ( empty($data) ) {
			return false;
		}

		if ( is_null($this->client) ) {
			$this->getES();
		}
		$type_name = $this->config['type'];

		$logic_index_name = $this->config['logic'];
		$logic_index = $this->client->getIndex($logic_index_name);
		if ( !$logic_index->exists() ) {
			return false;
		}

		$origin_index_name = $this->config['origin'];
		$origin_index = $this->client->getIndex($origin_index_name);
		if ( !$origin_index->exists() ) {
			return false;
		}

		$this->_cost = microtime(true);
		foreach ( $data as $inx => $item ) {
			// 如果是假删除数据，在逻辑索引中真实删除数据
			if ( $item['_deleted']!==false ) {
				$path = '/'.$logic_index_name.'/'.$type_name.'/'.$item['_uniqsign'];
				$ret = $this->doGET($path, array());
				$ret = $this->doDELETE($path, array());
				// echo 'delete logic data : ', $path, var_export($ret), PHP_EOL;
			} else {
				// 当逻辑索引中的数据产生的指纹与原有的不一致时(_title变化)，删除原逻辑索引中的数据
				$path = '/'.$logic_index_name.'/'.$type_name.'/_search';
				$query = array(
					'query' => array(
						'term' => array(
							'_uniqid' => $item['_uniqid'],
						),
					),
				);
				$_ret = $this->doGET($path, $query);
				$query = array();
				if ( $_ret['hits']['total'] > 0 ) {
					foreach ( $_ret['hits']['hits'] as $_inx => $_item ) {
						if ( $_item['_id']!=$item['_uniqsign'] ) {
							$path = '/'.$logic_index_name.'/'.$type_name.'/'.$_item['_id'];
							$ret = $this->doDELETE($path, $query);
							// echo 'delete logic data2 : ', $path, var_export($ret), PHP_EOL;
						}
					}
				}
				$this->logic_data[] = new \Elastica\Document($item['_uniqsign'], $item);
			}

			$this->origin_data[] = new \Elastica\Document($item['_uniqid'], $item);
		}

		if ( count($this->origin_data)>0 ) {
			$this->origin = $origin_index->getType($type_name);
			$this->origin->addDocuments($this->origin_data);
			$this->origin->getIndex()->refresh();
		}
		if ( count($this->logic_data)>0 ) {
			$this->logic = $logic_index->getType($type_name);
			$this->logic->addDocuments($this->logic_data);
			$this->logic->getIndex()->refresh();
		}
		$this->_cost = microtime(true) - $this->_cost;

		return true;
	}


}