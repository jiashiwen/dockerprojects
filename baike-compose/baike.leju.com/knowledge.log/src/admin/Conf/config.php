<?php
/**
 * 管理 入口
 * @author Robert <cnwangyl@gmail.com>
 */
define('DEFAULT_THEME', 'v1');

return array(
	// 默认主题模版
	'DEFAULT_THEME' => DEFAULT_THEME,
	
	// // 设置默认入口
	// 'DEFAULT_MODULE' => APP_NAME,
	// 'DEFAULT_CONTROLLER' => 'index',
	// 'DEFAULT_ACTION' => 'index',

	// 数据表前缀
	'DB_PREFIX' => '',

	// 默认主题模版
	// 'DEFAULT_THEME' => 'default',
	// 'DEFAULT_C_LAYER' => 'Controller',
	// 路由设置
	// 'URL_MODEL' => 2,	// REWRITE
	// 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
	// 'URL_ROUTER_ON' => true, //开启路由
	// 'URL_ROUTE_RULES' => array( //定义路由规则
	// 	'search.html?:key\s' => 'search/',
	// ),
	// 'URL_HTML_SUFFIX' => '',
	// 默认输入过滤器
	'DEFAULT_FILTER' => 'strip_tags,stripslashes',

	// 开启部局模版
	'LAYOUT_ON' => true,
	'LAYOUT_NAME' => 'layout',

	// 模版字符串处理
	'TMPL_PARSE_STRING' => array(
		'__DEPLOY_DOMAIN_NAME__' => DOMAIN_NAME,
		'__DEPLOY_HOSTNAME__' => 'http://'.DOMAIN_NAME,
	),

	'ADMIN_URL' => array(
		'CDN_IMG_URL'=>'//cdn.leju.com/encyclopedia/',
	),


	'CACHER_KEYS' => array(
		'ADMIN_ROLE_LIST' => 'ADMIN:ROLE:LIST',	// 管理员角色列表
	),

);
