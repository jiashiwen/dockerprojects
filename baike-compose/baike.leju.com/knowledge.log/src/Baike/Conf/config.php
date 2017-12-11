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
	// 'URL_ROUTER_ON' => true, //开启路由
	'URL_HTML_SUFFIX' => '',
	'URL_PATHINFO_DEPR' => '-', // PATHINFO模式下，各参数之间的分割符号
	'URL_ROUTE_RULES' => array( //定义路由规则
		// 规则中的分隔符必须使用 / 处理，真实路由即 Query_String 中，可以使用 URL_PATHINFO_DEPR 参数中指定的分隔符来进行分隔
		'index/:city\w/:id\d' => 'Index/index',	// pc 版
		'index/:id\d' => 'Index/index',		// pc 版
		'index/:city\w' => 'Index/index',
		'index' => 'Index/index',
		// @ 查看指定分类的词条
		'map' => 'Map/index',
		// @ 查看指定分类
		'cate/:city\w/:id\d/:page\d' => 'Cate/index',
		'cate/:city\w/:id\d' => 'Cate/index',
		// @ 查看指定分类的知识
		'list/:city\w/:id\d' => 'List/index',
		// @ 知识列表页 加载更多
		'listmore/:city\w/:id\d/:page\d' => 'List/loading',
		// @ 标签聚合知识列表页 agg
		'agg/:city\w/:tag\w/:id\d/:page\d' => 'Agg/index', // pc版
		'agg/:city\w/:tag\w' => 'Agg/index',
		// 'agg/:tag\w' => 'Agg/index',
		// @ 聚合页加载更多
		'aggmore/:city\w/:id/:page\d' => 'Agg/loading',
		// @ 查看指定分类的词条
		'search/:keyword' => 'Search/index?keyword=:1',
		'search' => 'Search/index',	// for pc & touch
		// @ 加载更多
		'result/:keyword/:page\d' => 'Search/loading',
		'result' => 'Search/loading',
		// @ 联想词查询
		'suggest' => 'Search/suggest',
		// @ PC版联想词查询 ( 百科 和 知识 共用 )
		'suggests' => 'Search/suggestpc',
		// @ 查看指定知识内容
		'show/:id\d' => 'Show/index',

		// for baidu seo sitemap
		'sitemap/:business/:group' => 'Sitemap/info',
		'sitemap/:business' => 'Sitemap/index',
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
	'ERROR_PAGE' => WEB_ROOT.'/p/err/error.html',
	'TMPL_EXCEPTION_FILE' => WEB_ROOT.'/p/err/exception.html',

    //百科PC首页统计代码分类对应关系
	'FRONT_BAIKE_COUNT_CATE'=>array(
		1=>'house',
		2=>'esf',
		3=>'jiaju',
		4=>'zx',
		),
);
