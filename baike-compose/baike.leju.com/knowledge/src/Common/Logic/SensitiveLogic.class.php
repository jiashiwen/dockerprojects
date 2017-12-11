<?php
/**
 * 推荐信息获取逻辑
 * 数据取自新闻池
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class SensitiveLogic {
	protected $_debug = false;
	public function debug( $mode=false ) {
		$this->_debug = $mode;
		return $this;
	}
	public function detect( $content='', $isstrict=0, $id='' ) {
		$content = strip_tags(trim($content));
		if ( !$content ) {
			return false;
		}
		$Engine = D('Search', 'Logic', 'Common');
		$token = $Engine->getToken();
		$config = C('SENSITIVE');
		$api = $config['DETECT'];
		$headers = array(
			'Authorization: '.$token,
		);
		$params = array(
			'k' => $content,
			'isstrict' => $isstrict,
		);
		if ( $id!=='' ) {
			$params['id'] = $id;
		}
		$ret = curl_post($api, $params, $headers);
		// var_dump($api, $params, $headers, $ret);
		/*
		"{"userquery":"","hlquery":"这是接口提交的测试回复2","formatquery":"","cost":0.013,"status":true,"category":"未知","score":1.0,"iscontainedsensitive":false,"sensitivewords":[]}"
		*/
		$result = false;
		if ( $ret['status'] ) {
			$ret = json_decode($ret['result'], true);
			$types = array();
			$words = array();
			if ( $ret['status'] && $ret['sensitivewords'] ) {
				foreach ( $ret['sensitivewords'] as $i => $sw ) {
					// $type = $sw['reason'];
					// if ( !array_key_exists($sw['reason'], $types) ) {
					// 	$types[$type] = $sw['score'];
					// } else {
					// 	$types[$type]+=$sw['score'];
					// }
					array_push($words, $sw['word']);
				}
				// unset($types['未知']);
				// arsort($types);
				// $type = key($types);
				$result = array(
					'status' => true,
					// 'score' => $types[$type],
					// 'type' => $type,
					'score' => $ret['score'],
					'type' => $ret['category'],
					'words' => $words,
				);
				if ( $this->_debug ) {
					$result['debug'] = $ret;
				}
			}
		} else {
			debug('detect 接口异常', $ret, false, true);
		}
		return $result;
	}

	// public function parse() {}
}