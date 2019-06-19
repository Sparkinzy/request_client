<?php


$base_path = __DIR__;
require_once $base_path.'/vendor/autoload.php';
use Mu\Juyuan\Request;
$params = array(
	'site' => 'k3',
);
Request::$gateway = 'http://x.data.hotapi.cn';
$rs = Request::Get($params);
var_dump($rs);