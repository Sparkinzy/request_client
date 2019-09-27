<?php
/**
 * Created by PhpStorm.
 * User: mu
 * Date: 2019-09-27
 * Time: 11:37
 */

namespace Mu\Juyuan;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise;

class MultiRequest {
	public static  $gateway  = '';
	public static  $debug    = FALSE;
	public static  $ua       = 'from http request';
	private static $promises = array();
	private static $result;
	/**
	 * 并发请求数
	 * @var int
	 */
	public static $concurrency = 5;
	
	/**
	 * @param array $params
	 *
	 * @throws \Exception
	 */
	private static function check_params($params = array())
	{
		if ( ! isset($params['action']))
		{
			throw new \Exception('acion cannot be empty', '501');
		}
	}
	
	# 新增一个请求
	public static function attach($key, array $params)
	{
		self::$promises[$key] = $params;
	}
	
	/**
	 * 执行
	 * @return mixed
	 */
	public static function result()
	{
		$client = new Client([
			'base_uri'        => self::$gateway,
			'timeout'         => 5,
			'connect_timeout' => 2,
			'allow_redirects' => TRUE,
			'debug'           => self::$debug,
			'headers'         => [
				'User-Agent' => self::$ua
			]
		]);
		
		$requests = function ($total) use ($client) {
			foreach (self::$promises as $key => $promise)
			{
				yield function () use ($client, $promise) {
					$action = $promise['action'];
					unset($promise['action']);
					$request_uri = '/' . str_replace('.', '/', $action);
					return $client->getAsync($request_uri, [
						'query' => $promise
					]);
				};
			}
		};
		
		$pool = new Pool($client, $requests(count(self::$promises)), [
			'concurrency' => self::$concurrency,
			'fulfilled'   => function ($response, $index) {
				$res = $response->getBody()->getContents();
				$keys = array_keys(self::$promises);
//				self::info("请求第 $index 个请求，key：".$keys[$index]." , result 为：" . $res);
				self::$result[$keys[$index]] = json_decode($res);
			},
			'rejected'    => function ($reason, $index) {
				$keys = array_keys(self::$promises);
				self::$result[$keys[$index]] = (object)['code'=>500,'msg'=>'error','data'=>$reason];
//				self::error("rejected");
//				self::error("rejected reason: " . $reason);
			},
		]);
		
		// 开始发送请求
		$promise = $pool->promise();
		$promise->wait();
		return self::$result;
	}
	
	public static function info($msg = '')
	{
		echo '[info][' . date('Y-m-d H:i:s') . ']' . $msg . PHP_EOL;
	}
	
	public static function error($msg = '')
	{
		echo '[error][' . date('Y-m-d H:i:s') . ']' . $msg . PHP_EOL;
		
	}
	
	
}