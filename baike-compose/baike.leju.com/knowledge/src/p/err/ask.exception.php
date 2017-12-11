<?php
// 问答系统异常页面处理

// 根据来源域名进行判断设备类型
$subdomains = array('mobile'=>'m', 'pc'=>'');
$device = $subdomain = ( strpos($_SERVER['HTTP_HOST'], 'm.')!==false ) ? 'touch' : 'pc';

// 新添加，判断是否 app 进入，如果app进入，则不显示导航头
$is_app = I('ljmf_s', cookie('isapp'), 'trim,strtolower');
$allowed_apps = array('yd_kdlj');
$is_app = in_array($is_app, $allowed_apps) ? $is_app : 'notapp';
cookie('isapp', $is_app);


$tpl_dir = dirname(__FILE__);
$exception_file = $tpl_dir . '/ask.exception' . $device . '.php';

if ( file($exception_file) ) {
	require($exception_file);
}
