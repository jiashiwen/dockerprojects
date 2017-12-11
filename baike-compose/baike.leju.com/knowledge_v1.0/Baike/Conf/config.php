<?php
/**
 * 知识系统配置参数
 * @author Robert <cnwangyl@gmail.com>
 * URL : http://[m.]baike.leju.com
 */

// 默认主题外观
define('DEFAULT_THEME', 'v1');
return array(
	// 默认主题模版
	'DEFAULT_THEME' => DEFAULT_THEME,

	// 伪静态配置
	'URL_MODEL' => 1,	// REWRITE
	// 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
	'URL_ROUTER_ON' => true, //开启路由
	'URL_HTML_SUFFIX' => 'html',
	'URL_PATHINFO_DEPR' => '-', // PATHINFO模式下，各参数之间的分割符号
	'URL_ROUTE_RULES' => array( //定义路由规则
		// 规则中的分隔符必须使用 / 处理，真实路由即 Query_String 中，可以使用 URL_PATHINFO_DEPR 参数中指定的分隔符来进行分隔
		// @ 查看指定分类的词条
		'map' => 'Map/index',
		// @ 查看指定分类
		'cate/:city\w/:id\d' => 'Cate/index',
		// @ 查看指定分类的知识
		'list/:city\w/:id\d' => 'List/index',
		// @ 知识列表页 加载更多
		'listmore/:id\d/:page\d' => 'List/loading',
		// @ 标签聚合知识列表页 agg
		'agg/:tag\w' => 'Agg/index',
		// @ 聚合页加载更多
		'aggmore/:id/:page\d' => 'Agg/loading',
		// @ 查看指定分类的词条
		'search/:city\w/:keyword' => 'Search/index?city=:1&keyword=:2',
		// @ 加载更多
		'result/:keyword/:page\d' => 'Search/loading',
		// // @ 查看指定分类的词条
		// 'suggest/:city\w/:keyword/:pagesize\d' => 'Search/suggest?city:1&keyword=:2&pagesize=:3',
		// @ 查看指定知识内容
		'show/:id\d' => 'Show/index',
	),

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
	'DB_DEBUG' => APP_DEBUG,

	// 引用码
	// 'JUMP_REF' => array(
	// 	'_default' => '?ref=baike_leju_v2'
	// ),
	// 异常处理页面
	// 'ERROR_PAGE' => WEB_ROOT.'/p/err/error.html',
	// 'TMPL_EXCEPTION_FILE' => WEB_ROOT.'/p/err/exception.html',

	'FRONT_URL'=>array(
		'base'=>'/',
		'show'=>'/show?id=',
		'map'=>'/map',
		'cate'=>'/cate?id=',
		'list'=>'/list?id=',
		),
);
