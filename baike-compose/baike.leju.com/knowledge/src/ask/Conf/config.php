<?php
/**
 * 问答系统配置参数
 * @author Robert <cnwangyl@gmail.com>
 * URL : http://[m.]ask.leju.com
 */

// 默认主题外观
define('DEFAULT_THEME', 'v1');
return array(
	// 默认主题模版
	'DEFAULT_THEME' => DEFAULT_THEME,

	/*
		// 伪静态配置 通过 nginx rewrite 规则执行
	*/
	// 默认输入过滤器
	'DEFAULT_FILTER' => 'strip_tags,stripslashes',

	// 开启部局模版
	'LAYOUT_ON' => true,
	'LAYOUT_NAME' => 'layout',

	// // 模版字符串处理
	// 'TMPL_PARSE_STRING' => array(
	// 	'__DEPLOY_DOMAIN_NAME__' => DOMAIN_NAME,
	// 	'__DEPLOY_HOSTNAME__' => 'http://'.DOMAIN_NAME,
	// ),
	'DB_DEBUG' => APP_DEBUG,

	// 是否开启敏感词过滤
	'NEED_VERIFY' => false,

	'SHOW_ERROR_MSG' => true,
	// 异常处理页面
	//// 'ERROR_PAGE' => WEB_ROOT.'/ask/View/'.DEFAULT_THEME.'error.html',
	//// 'TMPL_EXCEPTION_FILE' => WEB_ROOT.'/p/err/exception.html',
	// 'ERROR_PAGE' => 'http://ld.ask.leju.com/error',
	// 'EXCEPTION_PAGE' => url('exception', array(), 'pc', 'ask'),
	//// 'TMPL_EXCEPTION_FILE' => 'http://ld.ask.leju.com/error', //url('error', array(), 'pc', 'ask'),
);
