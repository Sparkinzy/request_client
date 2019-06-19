<?php


$base_path = __DIR__;
require_once $base_path.'/vendor/autoload.php';
use Mu\Juyuan\Request;
$params = array(
	'site' => 'pika9',
);
Request::$gateway = 'http://api.pika9.com';
$rs = Request::Get($params);
var_dump($rs);