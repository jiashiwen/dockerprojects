<?php
/**
 * 基本常量配置
 */
// Web 根目录
define('WEB_ROOT',		dirname(__DIR__) . DIRECTORY_SEPARATOR);

// ThinkPHP 类库文件地址
if ( isset($_SERVER['PATH_THINKPHP']) ) {
	define('THINK_PATH',	$_SERVER['PATH_THINKPHP'] . DIRECTORY_SEPARATOR);
} else {
	define('THINK_PATH',	WEB_ROOT . 'lib' . DIRECTORY_SEPARATOR);
}

// 缓存文件地址
if ( isset($_SERVER['PATH_RUNTIME']) ) {
	define('RUNTIME_PATH',	$_SERVER['PATH_RUNTIME'] . DIRECTORY_SEPARATOR);
} else {
	define('RUNTIME_PATH',	sys_get_temp_dir() . DIRECTORY_SEPARATOR);
}

// 定义应用文件夹
define('APP_PATH',		WEB_ROOT);

// 匹配部署模式
$devs = ['ld', 'dev', 'test']; // 属于测试环境的子域名
$lds = ['ld', ]; // 本地调试环境
$host = strtolower($_SERVER['HTTP_HOST']);
$_mode = explode('.', $host);

if ( in_array($_mode[0], $lds) ) {
	// 影响本地调试输出，会在页面中生成 forp 的扩展样式
	define('APP_DEBUG',		true);
} else {
	// 不会做本地调试和输出
	define('APP_DEBUG',		false);
}
if ( in_array($_mode[0], $devs) ) {
	// 不会向新闻池推送数据
	define('APP_DEPLOY',	'dev');	// 测试模式
} else {
	// 向新闻池推送数据
	define('APP_DEPLOY',	'prd');	// 产品模式
}


// 配置文件目录
define('COMMON_PATH',	WEB_ROOT . 'Common' . DIRECTORY_SEPARATOR );
define('CONF_PATH',		COMMON_PATH . 'Conf' . DIRECTORY_SEPARATOR );

// 调试参数
error_reporting(E_ALL | E_STRICT);

// 设定入口目录 /*'ask', 'baike', 'tag', 暂时不需要使用独立域名配置 */
$allowed_subdomain = array('api', 'admin', 'ask');
if ( defined('APP_NAME') && 
	 in_array(strtolower(APP_NAME), $allowed_subdomain)
) {
	// echo '<h1>绑定--',APP_NAME,'</h1>', PHP_EOL; 
	define('BIND_MODULE',	APP_NAME);
}
// MVC 入口开始执行
require THINK_PATH . 'ThinkPHP.php';
