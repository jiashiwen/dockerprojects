<?php
/**
 * =========================
 * 知识库前端配置
 * -------------------------
 * Knowledge 知识子系统
 * - 主域名模式 Domain List : 
 * 		baike.leju.com/tag
 * 		m.baike.leju.com/tag
 * - 子域名模式 Domain List :
 * 		tag.baike.leju.com
 * 		m.tag.baike.leju.com
 * @author Robert <yongliang1@leju.com>
 */

// 项目部署的域名
define('DOMAIN_NAME',	$_SERVER['HTTP_HOST']);
define('APP_NAME',		'tag');
require_once('base_const.php');
