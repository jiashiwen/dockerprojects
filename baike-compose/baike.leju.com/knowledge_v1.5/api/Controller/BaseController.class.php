<?php
/**
 * 知识系统接口服务基础类
 * @author Robert <yongliang1@leju.com>
 */
namespace api\Controller;
use Think\Controller\RestController;

class BaseController extends RestController {


	/* ============================================================ */
	protected function simpleVaild() {
		$code = I('get.simple', '', 'trim,strtoupper');
		
	}
	protected function systemHalt() {
		header("HTTP/1.1 404 Not Found"); 
		header("HTTP/1.0 404 Not Found");
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
	}
	/**
	 * 显示调试信息
	 */
	protected function showDebug( $title='DEBUG INFO', $msg=array() ) {
		if ( $this->debug === 1 ) {
			echo '== ', $title, ' ==', PHP_EOL,
				 var_export($msg, true), PHP_EOL,
				 '@', date('Y-m-d H:i:s'), PHP_EOL,
				 PHP_EOL;
			exit;
		}
	}

	/**
	 * 转换错误代码到错误描述信息
	 */
	protected function getError( $errno=0 ) {
		$error_codes = C('ERR_CODE');
		if ( !array_key_exists($errno, $error_codes) ) {
			return array('无['.$errno.']此错误代码');
		}
		return $error_codes[$errno];
	}

	/**
	 * 操作失败, 显示错误信息
	 */
	protected function showError( $errno, $debug=array() ) {
		$result = $this->getError($errno);
		$error_info = array(
			'status' => false,
			'code' => intval($errno),
			'msg' => $result,
		);
		if ( !empty($debug) ) {
			$error_info['debug'] = $debug;
		}
		$this->response($error_info, 'json');
	}

	/**
	 * 操作成功, 显示接口数据
	 */
	protected function showResult( $result=array(), $type='json' ) {
		$types = array(
			'json'=>'text/json',
			'html'=>'text/html',
			'xml'=>'application/xml',
			'jsonp'=>'application/json',
		);
		if ( !array_key_exists($type, $types) ) {
			$type = 'json';
		}
		header('Content-type: '.$types[$type].';charset=utf-8');

		$result['status'] = true;
		// 判断是否为 jsonp 请求
		$callback = I('callback', '', 'trim');
		if ( $callback!=='' ) {
			// jsonp 按 jsonp 规范返回
			echo $callback,'(',json_encode($result),')';
		} else {
			// 正常 json 返回结果
			echo json_encode($result);
			// $this->response($result, $type);
		}
		exit;
	}

}