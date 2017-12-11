<?php
namespace Org\Leju;
/**
 * DataSource 工具类
 * 对应的是黄路负责的新闻池服务
 * @author Robert <yongliang1@leju.com>
 */
class DataSource {
	/**
	 * @var 数据来源接口
	 */
	protected $api = 'http://info.leju.com/search/default/index';
	/**
	 * @var 数据来源列表
	 */
	protected $sources = array();


	public function __construct() {
		$this->sources = array(
			'house'=>array(
				'desc' => '新房楼盘业务',
				'key' => INFO_HOUSE_APPID,
				'appid' => INFO_HOUSE_ID,
				'type' => INFO_HOUSE_NAME,
			),
			'news'=>array(
				'desc' => '新房新闻业务',
				'key' => INFO_NEWS_APPID,
				'appid' => INFO_NEWS_ID,
				'type' => INFO_NEWS_NAME,
			),
		);
	}

	public function existsSource($type='') {
		return array_key_exists($type, $this->sources) ? true : false;
	}
	public function getSourceConfig($type='') {
		return $this->existsSource($type) ? $this->sources[$type] : $this->sources;
	}

	/** -=-=-=-= 数据源相关方法 =-=-=-=- **/

	public function getSourceData( $type='', $page=1 , $pagesize=1, &$opts=array() ) {
		if ( !empty($type) && !$this->existsSource($type) ) {
			return false;
		}

		$config = $this->getSourceConfig($type);
		// 	print_r($config);
		$key = $config['key'];
		$appid = $config['appid'];
		$type = $config['type'];

		$data = array(
			'type' => $type,
			'appid' => $appid,
			'ver' => '2.0',
			'count' => 1,
			'page' => $page,
			'pcount' => $pagesize,
		);
		if ( isset($opts['field']) ) { $data['field'] = $opts['field']; }
		if ( isset($opts['order']) ) { $data['order'] = $opts['order']; }
		// 查询条件处理
		if ( isset($opts['filters']) && is_array($opts['filters']) ) {
			$filters = $opts['filters'];
			unset($opts['filters']);
		} else {
			$filters = array();
		}
		foreach ( $opts as $k => $opt ) {
			if ( substr($k, 0, 6)=='filter' ) {
				$filters[] = $opt;
				unset($opts[$k]);
			}
		}
		if ( !empty($filters) ) {
			$filters = array_unique($filters);
			$inx = 1;
			foreach ( $filters as $i => $filter ) {
				$data['filter'.$inx] = $filter;
				$inx ++ ;
			}
		}
		unset($filters);

		// 添加验证码
		$sign = $this->get_check($data, $key);
		$data['sign'] = $sign;
		$opts = $data;

		$_timer = microtime(true);
		$result = $this->_curl_post($this->api, $data);
		$_timer = microtime(true)-$_timer;
		$info = ( $result['status']==true ) ? json_decode($result['result'], true) : false;
		return $info;
	}


	/**
	 * 生成新闻池数据验证码
	 */
	function get_check($data, $key) {
		$string = '';

		if(is_array($data)) {
			foreach ($data as $v) {
				$string .= $v;
			}
		} else {
			$string = $data;
		}
		$md5 = md5($string.$key);
		return $md5;
	}

	/**
	 * 提交GET请求，curl方法
	 * @param string  $url       请求url地址
	 * @param mixed   $data      GET数据,数组或类似id=1&k1=v1
	 * @param array   $header    头信息
	 * @param int     $timeout   超时时间
	 * @param int     $port      端口号
	 * @return array             请求结果,
	 *                            如果出错,返回结果为array('error'=>'','result'=>''),
	 *                            未出错，返回结果为array('result'=>''),
	 */
	protected function _curl_get($url, $data = array(), $header = array(), $timeout = 5, $port = 80)
	{
		$ch = curl_init();
		if (!empty($data)) {
			$data = is_array($data)?http_build_query($data): $data;
			$url .= (strpos($url,'?')?  '&': "?") . $data;
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_POST, 0);
		//curl_setopt($ch, CURLOPT_PORT, $port);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$result = array('status'=>true);
		$result['result'] = curl_exec($ch);
		if (0 != curl_errno($ch)) {
			$result['status'] = false;
			$result['error']  = "Error:\n" . curl_error($ch);

		}
		curl_close($ch);
		return $result;
	}


	/**
	 * 提交POST请求，curl方法
	 * @param string  $url       请求url地址
	 * @param mixed   $data      POST数据,数组或类似id=1&k1=v1
	 * @param array   $header    头信息
	 * @param int     $timeout   超时时间
	 * @param int     $port      端口号
	 * @return string            请求结果,
	 *                            如果出错,返回结果为array('error'=>'','result'=>''),
	 *                            未出错，返回结果为array('result'=>''),
	 */
	protected function _curl_post($url, $data = array(), $header = array(), $timeout = 5, $port = 80)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		//curl_setopt($ch, CURLOPT_PORT, $port);
		!empty ($header) && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$result = array('status'=>true);
		$result['result'] = curl_exec($ch);
		
		if (0 != curl_errno($ch)) {
			$result['status'] = false;
			$result['error']  = "Error:\n" . curl_error($ch);
		}
		curl_close($ch);

		return $result;
	}


}//类定义结束