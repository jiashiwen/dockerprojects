<?php
/**
 * 知识系统接口服务入口
 * @author Robert <yongliang1@leju.com>
 */
namespace api\Controller;
use Think\Controller;

class IndexController extends Controller {
	public function index(){
		echo '<h1>这是 API 首页页面</h1>';
	}
}