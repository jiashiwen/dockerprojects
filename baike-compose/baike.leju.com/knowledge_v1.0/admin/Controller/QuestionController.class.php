<?php
/**
 * 问答控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class QuestionController extends BaseController {
	/**
	 * 问答管理列表及搜索页面
	 */
	public function index(){
		// auth_id : knowledge/list
		// echo '<h1>这是 问答管理列表及搜索页面 页面</h1>';
		$this->display('index');
	}
}