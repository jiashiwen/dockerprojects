<?php
/**
 * 搜索工具集合 入口配置
 */

// 项目部署的域名
// define('DOMAIN_NAME',	'search.leju.com');
$_SERVER['HTTP_HOST'] = 'utils.search.leju.com';
define('DOMAIN_NAME',	$_SERVER['HTTP_HOST']);

set_time_limit(0);

define('APP_MODE',		'cli');
define('APP_NAME',		'utils');

require_once('base_const.php');
