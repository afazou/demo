<?php
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function.php 2013-11-14 14:05:22Z lilixing $
 */
if ( ! defined('IN_GOME_WEIXIN_SERVICE'))
{
	exit('Access Denied');
}

/**
 * 写日志
 *
 * @param        string $log 日志内容
 * @param        string $file 日志文件
 * @version       1.0
 * @author       <llx>lilixing@yolo24.com
 */
function LogFile($log, $file = 'error')
{
	if (defined('LOG_SWITCH') && LOG_SWITCH == false)
	{
		return;
	}

	$logdir = defined('LOG_PATH') ? LOG_PATH.'/' : './log/';

	if ( ! is_dir($logdir))
	{
		@mkdir($logdir);
	}
	$logdir = $logdir.date('Ymd').'/';
	if ( ! is_dir($logdir))
	{
		@mkdir($logdir);
	}
	$logfile = $logdir.$file.'.php';
	if (@filesize($logfile) > 2048000)
	{
		$logfilebak = $logdir.$file.'_'.date('His').'.php';
        @rename($logfile, $logfilebak);
	}
	if ($fp = @fopen($logfile, 'a'))
	{
		@flock($fp, 2);

		$message = var_export($log, true);
		fwrite($fp, "<?PHP exit;?>\t[".date('Y-m-d H:i:s')."]\t".str_replace(array('<?', '?>'), '', $message)."\n");

		/*
		$log = is_array($log) ? $log : array($log);
		foreach($log as $tmp)
		{
		fwrite($fp, "<?PHP exit;?>\t[".date('Y-m-d H:i:s')."]\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
		}
		*/
		fclose($fp);
	}
}


function strUrl($url,$params){

	$params = http_build_query($params);

	if(strpos($url, "?")){

		return $url."&".$params;

	}else{

		return $url."?".$params;

	}

}

/**
 * CURL发送请求
 *
 * @param string $url
 * @param mixed $data
 * @param string $method
 * @param string $cookieFile
 * @param array $headers
 * @param int $connectTimeout
 * @param int $readTimeout
 */
function curlRequest($url,$data='',$method='POST',$cookieFile='',$headers='',$connectTimeout = 30,$readTimeout = 30)
{
	$method = strtoupper($method);
	if(!function_exists('curl_init')) return socketRequest($url, $data, $method, $cookieFile, $connectTimeout);

	$option = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_CONNECTTIMEOUT => $connectTimeout,
			CURLOPT_TIMEOUT => $readTimeout,
			CURLOPT_HTTPHEADER => array( 'Expect:' ) ,
	);

	if($headers) $option[CURLOPT_HTTPHEADER] = $headers;

	if($cookieFile)
	{
		$option[CURLOPT_COOKIEJAR] = $cookieFile;
		$option[CURLOPT_COOKIEFILE] = $cookieFile;
	}

	if($data && strtolower($method) == 'post')
	{
		$option[CURLOPT_POST] = 1;
		$option[CURLOPT_POSTFIELDS] = $data;
	}

	if(stripos($url, 'https://') !== false)
	{
		$option[CURLOPT_SSL_VERIFYPEER] = false;
		$option[CURLOPT_SSL_VERIFYHOST] = false;
	}

	$ch = curl_init();
	curl_setopt_array($ch,$option);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

/**

* 生成ID值

* Enter description here ...

*/

function GenerationId(){

	return uniqid();

}
/**
 * 检测是否是微信浏览器
 * @author <zxl>[zhangxianglong@yolo24.com]
 * @date   2015-05-15T14:42:10+0800
 * @return boolean                  [description]
 */
function is_weixin()
{
	$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
	return (false === strpos($userAgent, 'micromessenger')) ? false : true;
}

function ChatStatusKefu($token,$openid){
	return $token.$openid;
}
/**
 * 获取当前页面url
 * @author <zxl>[zhangxianglong@yolo24.com]
 * @date   2015-05-15T14:42:46+0800
 * @param  boolean                  $encode [description]
 * @return [type]                           [description]
 */
function get_current_url($encode = true)
{
	$pageUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	return $encode ? urlencode($pageUrl) : $pageUrl;
}

function table_time($time){
	if(is_numeric($time)){
		$time = date('Y-m-d',$time);
	}
	//$time = preg_replace("/-/", "_", $time);
	return $time;
}

function javatime(){
	return time().'000';
}
/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @return mixed
 */
function C($name=null, $value=null) {
	$cache_dao = RedisCache::get_instance(0);//实例化redis
	static $_config = array();
	// 无参数时获取所有
	if (empty($name)) {
		if(!empty($value) && $array = $cache_dao->set('c_'.$value)) {
			$_config = array_merge($_config, array_change_key_case($array));
		}
		return $_config;
	}
	// 优先执行设置获取或赋值
	if (is_string($name)) {
		if (!strpos($name, '.')) {
			$name = strtolower($name);
			if (is_null($value))
				return isset($_config[$name]) ? $_config[$name] : null;
			$_config[$name] = $value;
			return;
		}
		// 二维数组设置和获取支持
		$name = explode('.', $name);
		$name[0]   =  strtolower($name[0]);
		if (is_null($value))
			return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
		$_config[$name[0]][$name[1]] = $value;
		return;
	}
	// 批量设置
	if (is_array($name)){
		$_config = array_merge($_config, array_change_key_case($name));
		if(!empty($value)) {// 保存配置值
			$cache_dao->set('c_'.$value,$_config);
		}
		return;
	}
	return null; // 避免非法参数
}
/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
	//多行URL地址支持
	$url        = str_replace(array("\n", "\r"), '', $url);
	if (empty($msg))
		$msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
	if (!headers_sent()) {
		// redirect
		if (0 === $time) {
			header('Location: ' . $url);
		} else {
			header("refresh:{$time};url={$url}");
			echo($msg);
		}
		exit();
	} else {
		$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
		if ($time != 0)
			$str .= $msg;
		exit($str);
	}
}
?>