<?php
/**
 * =========================
 * 乐居知识库总 入口
 * -------------------------
 * 主域名模式
 * Domain List : 
 * baike.leju.com (PC前端)
 * m.baike.leju.com (移动前端)
 *
 * @author Robert <yongliang1@leju.com>
 */

// 调试参数
error_reporting(E_ALL | E_STRICT);

// 项目根目录
define('WEB_ROOT',		__DIR__ . DIRECTORY_SEPARATOR);
// define('WEB_ROOT',		__ROOT__ . DIRECTORY_SEPARATOR);
// 缓存文件地址
if ( isset($_SERVER['PATH_RUNTIME']) ) {
	define('RUNTIME_PATH',	$_SERVER['PATH_RUNTIME'] . DIRECTORY_SEPARATOR);
} else {
	define('RUNTIME_PATH',	sys_get_temp_dir() . DIRECTORY_SEPARATOR);
}
// ThinkPHP 框架目录配置
define('THINK_PATH',	WEB_ROOT . 'lib' . DIRECTORY_SEPARATOR);

// 定义应用文件夹
define('APP_PATH',		WEB_ROOT);

// 匹配部署模式
$devs = ['ld', /*'dev',*/ 'test']; // 属于调试模式下的子域名
$host = strtolower($_SERVER['HTTP_HOST']);
$_mode = explode('.', $host);
if ( in_array($_mode[0], $devs) ) {
	define('APP_DEBUG',		true);
	define('APP_DEPLOY',	'dev');	// 开发模式
} else {
	define('APP_DEBUG',		false);
	define('APP_DEPLOY',	'prd');	// 产品模式
}

// 配置文件目录
define('COMMON_PATH',	WEB_ROOT . 'Common' . DIRECTORY_SEPARATOR );
define('CONF_PATH',		COMMON_PATH . 'Conf' . DIRECTORY_SEPARATOR );

// 项目部署的域名
define('DOMAIN_NAME',	$_SERVER['HTTP_HOST']);

// 引入ThinkPHP入口文件
require_once THINK_PATH.'ThinkPHP.php';
