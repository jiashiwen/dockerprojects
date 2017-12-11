<?php
/**
 * 百科词条 前台 配置参数
 * @author Robert <cnwangyl@gmail.com>
 * URL : http://[m.]baike.leju.com/tag/
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
	'URL_PATHINFO_DEPR' => '/', // PATHINFO模式下，各参数之间的分割符号
	'URL_ROUTE_RULES' => array( //定义路由规则
		// 规则中的分隔符必须使用 / 处理，真实路由即 Query_String 中，可以使用 URL_PATHINFO_DEPR 参数中指定的分隔符来进行分隔
		'word/:id' => 'Show/index',		// 查看指定词条
		'cate/:cateid\d' => 'List/index',		// 查看指定分类的词条
		'listall' => 'List/all',				// 查看所有词条
 		'search/:word' => 'Search/index',	// 查看指定分类的词条
		'suggest' => 'Search/suggest',	// 联想词搜索接口
		// 'suggest/:word/:pagesize\d' => 'Search/suggest?word=:1&pagesize=:2',	// 联想词搜索接口
		// 'result/:word/:page\d' => 'Search/result?word=:1&page=:2',	// 查看指定分类的词条
		// 'monidata/:k' => 'Search/monidata?k=:1',	// 用于搜索词条假数据用，测试完毕之后删除
	),

	// 开启部局模版
	'LAYOUT_ON' => true,
	'LAYOUT_NAME' => 'layout',

	// 模版字符串处理
	'TMPL_PARSE_STRING' => array(
		'__DEPLOY_DOMAIN_NAME__' => DOMAIN_NAME,
		'__DEPLOY_HOSTNAME__' => 'http://'.DOMAIN_NAME,
	),
	// 'DB_DEBUG' => APP_DEBUG,

	// 引用码
	// 'JUMP_REF' => array(
	// 	'_default' => '?ref=tag_leju_v2'
	// ),
	// 异常页面定制
	// 'ERROR_PAGE' => WEB_ROOT.'/p/err/error.html',
	// 'TMPL_EXCEPTION_FILE' => WEB_ROOT.'/p/err/exception.html',
);
