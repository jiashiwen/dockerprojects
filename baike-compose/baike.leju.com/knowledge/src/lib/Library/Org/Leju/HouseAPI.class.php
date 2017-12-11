<?php
namespace Org\Leju;
/**
 * HouseAPI 工具类
 * 对应的是 曹景孚 负责的楼盘库接口
 * @author Robert <yongliang1@leju.com>
 */
class HouseAPI {
	/**
	 * @var 数据来源接口
	 */
	protected $api = 'http://house.leju.com/api/api.agent.php';
	/**
	 * @var 接口参数
	 */
	protected $opts = array();
	/**
	 * @var 数据来源列表
	 */
	protected $sources = array();

	protected $debug = false;

	public function __construct() {
		$this->opts = array(
			'key' => 'd26dc5ccc606cb76',
			'module' => 'get_updated_house',
			'start' => 0,
			'end' => NOW_TIME,
			'page' => 1,
			'pcount' => 100,
			'return' => 'json',
			'encode' => 'utf-8',
		);
	}

	public function setDebug ( $enabled = false ) {
		$this->debug = !!$enabled;
	}

	/** -=-=-=-= 数据源相关方法 =-=-=-=- **/

	public function getSourceData( $page=1 , $pagesize=100, &$opts=array() ) {
		$data = array_merge($this->opts, $opts);
		$data['page'] = $page;
		$data['pcount'] = $pagesize;

		// 添加验证码
		// $sign = $this->get_check($data, $key);
		// $data['sign'] = $sign;
		// $opts = $data;

		if ( $this->debug ) {
			echo 'REQUEST: ', var_export(array('api'=>$this->api, 'opts'=>$data), true), PHP_EOL;
		}
		$_timer = microtime(true);
		$result = $this->_curl_get($this->api, $data);
		$_timer = microtime(true)-$_timer;
		$info = ( $result['status']==true ) ? json_decode($result['result'], true) : false;
		if ( $info && empty($info['get_updated_house']['info']) ) {
			$info = true;
		} else {
			$info = $info['get_updated_house']['info'];
		}

		// for debug
		if ( $this->debug ) {
			// var_export($result);
			$filters = array('site'=>'', 'hid'=>'', 'name'=>'', 'city_cn'=>'', 'city_code'=>'', 'price_display'=>'', 'house_url'=>'');
			foreach ( $info as $i => $item ) {
				echo str_pad($i, 3, '0', STR_PAD_LEFT), ' : ', implode(', ', array_intersect_key($item, $filters)), PHP_EOL;
			}
		}
		return $info;
	}


	/**
	 * 生成接口动态认证口令验证码
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