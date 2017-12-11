<?php
$info = array(
	'status' => false,
	'code' => 10000,
	'msg' => strip_tags($e['message']),
);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($info);