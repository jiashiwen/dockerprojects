<?php
/**
 * 问答异常处理页面
 *
 */
namespace ask\Controller;
use Think\Controller;
class ErrorController extends BaseController {

	public function __construct() {
		parent::__construct();
	}

	public function index() {
		echo send_http_status(404);
	}

	public function exception() {
		// echo 'exception!';
		$this->display('Public:exception');
	}
}

