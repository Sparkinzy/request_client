<?php

namespace Mu\Juyuan;

require_once __DIR__ . '/../../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Request {
	public static $gateway = '';
	public static $debug   = FALSE;
	public static $ua      = 'from http request';
	
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
	
	/**
	 * @param array $params
	 *
	 * @return mixed|object
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public static function get($params = array())
	{
		try{
			self::check_params($params);
		}catch (\Exception $e){
			return (object) ['code'=>500,'msg'=>$e->getMessage()];
		}
		$action = $params['action'];
		unset($params['action']);
		$request_uri = '/' . str_replace('.', '/', $action);
		return self::http($request_uri, $params, "GET");
		
	}
	
	/**
	 * @param array $params
	 *
	 * @return mixed|object
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public static function post($params = array())
	{
		try{
			self::check_params($params);
		}catch (\Exception $e){
			return (object) ['code'=>500,'msg'=>$e->getMessage()];
		}
		$action = $params['action'];
		unset($params['action']);
		$request_uri = '/' . str_replace('.', '/', $action);
		return self::http($request_uri, $params, "POST");
	}
	
	/**
	 * @param string $request_uri
	 * @param array  $params
	 * @param string $method
	 *
	 * @return mixed|object
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public static function http($request_uri = '', $params = array(), $method = 'GET')
	{
		
		self::$gateway = rtrim(self::$gateway, '/');
		$request_uri   = '/' . ltrim($request_uri, '/');
		
		$client = new Client([
			'base_uri'        => self::$gateway,
			'timeout'         => 60,
			'connect_timeout' => 2,
			'debug'           => self::$debug,
			'headers'         => [
				'User-Agent' => self::$ua
			]
		]);
		
		$method = $method ?: 'GET';
		$method = strtoupper($method);
		try
		{
			if ($method === 'GET')
			{
				$response = $client->request($method, $request_uri, [
					'query' => $params
				]);
			} else
			{
				$response = $client->request($method, $request_uri, [
					'form_params' => $params
				]);
			}
			
			if ($response->getStatusCode() === 200)
			{
				# 请求成功
				return \GuzzleHttp\json_decode($response->getBody()->getContents());
			} else
			{
				return (object)['code' => $response->getStatusCode(), 'msg' => 'server error'];
			}
		} catch (RequestException $e)
		{
			$msg_r = $e->getRequest();
			return (object)['code' => 666, 'msg' => $e->getMessage(), 'err' => $msg_r];
		}
	}
	
	
}