<?php
class mdl_customer_chat extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_customer_chat';
	}

    /**
     * 创建房间
     *
     * @param $token
     * @param $openid
     * @param $only
     * @return mixed
     */
    public function createRoom($token, $openid, $only)
    {
        $data = array(
            'token'		=> $token,
            'openid'	=> $openid,
            'only'		=> $only,
            'time'		=> time()
        );
        $roomId = $this->add(array(
            'table' => date('Y-m-d', $data['time']),
            'time' => date('Y-m-d H:i:s', $data['time']),
            'tokenid' => $data['token'],
            'only' => $only,
            'openid' => $openid,
        ));
        $data['chatid'] = $roomId;
        self::$cacheHandle->setCache(ChatStatusKefu($token, $openid), $data, 172800);
        return $roomId;
    }

    /**
     * 获取会话房间
     *
     * @param $openid
     * @param int $token
     * @return bool
     */
    public function getSessionRoom($openid, $token = 6)
    {
        $cache = self::$cacheHandle->getCache(ChatStatusKefu($token, $openid));
        if ($cache) {
            self::$cacheHandle->setCache(ChatStatusKefu($token,$openid), $cache, 172800);
            return $cache;
        }
        return false;
    }

}
