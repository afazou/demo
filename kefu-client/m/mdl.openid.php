<?php
class mdl_openid extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_openid';
	}

    /**
     * 根据Openid获取微信用户信息
     *
     * @param $openid
     * @return mixed
     */
    public function getWechatUserByOpenid($openid)
    {
        return $this->find(array('openid' => $openid));
    }


}
