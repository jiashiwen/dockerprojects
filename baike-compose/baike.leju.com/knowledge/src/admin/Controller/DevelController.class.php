<?php
/**
 * 开发者工具模式
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class DevelController extends BaseController {
	/**
	 * 入口
	 */
	public function index() {
	}


	/**
	 * 基础信息
	 */
	public function phpinfo() {
		layout(false);
		phpinfo();
	}

	/**
	 * 异步任务管理
	 */
	public function asynctasks() {

	}

	/**
	 * 定时任务管理
	 */
	public function crontab() {

	}
}