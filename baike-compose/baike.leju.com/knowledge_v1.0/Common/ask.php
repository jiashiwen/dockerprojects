<?php
/**
 * =========================
 * 知识库前端配置
 * -------------------------
 * Knowledge 知识子系统
 * - 主域名模式 Domain List : 
 * 		baike.leju.com/ask
 * 		m.baike.leju.com/ask
 * - 子域名模式 Domain List :
 * 		ask.baike.leju.com
 * 		m.ask.baike.leju.com
 * @author Robert <yongliang1@leju.com>
 */

// 项目部署的域名
define('DOMAIN_NAME',	$_SERVER['HTTP_HOST']);
define('APP_NAME',		'ask');
require_once('base_const.php');
