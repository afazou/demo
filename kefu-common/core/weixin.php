<?php
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *       desc:微信接口封装实现的基类
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: weixin.php 2015-05-14 17:00:00 wangwanfei $
 */
class weixin extends Wechat {
	private $cache_dao = '';
	public function __construct($options)
	{
		parent::__construct($options);
		$this->cache_dao = RedisCache::get_instance(0);
	}
	/**
	 * 重载设置缓存
	 * @param string $appId
	 * @param mixed $token
	 * @param int $expired
	 * @return trrue|false
	 */
	public function setCache($appId, $token, $expired = 6600)
	{
		 $cacherId  = WX_API_TOKEN.$appId;
		$set_redis = $this->cache_dao->set($cacherId, $token, $expired);
		if ( ! $set_redis)
		{
			$msg = '添加redis失败，cacheid:'.$cacherId.';token:'.$token;
			$log_file = 'token_add_redis';
			Log2File($msg, $log_file);
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 重载获取缓存
	 * @param string $appId
	 * @return mixed
	 */
	public function getCache($appId)
	{
		$cacherId  = WX_API_TOKEN.$appId;
		$get_redis = $this->cache_dao->get($cacherId);
		return $get_redis;
	}

	/**
	 * 重载清除缓存
	 * @param string $appId
	 * @return boolean
	 */
	public function removeCache($appId)
	{
		$cacherId = WX_API_TOKEN.$appId;
		$res = $this->cache_dao->delete($cacherId);
		return $res;
	}

}
