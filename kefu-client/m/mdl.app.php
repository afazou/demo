<?php
class mdl_app extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_app';
	}

    /**
     * 获取（正常的）微信公众号
     *
     * @param $id
     * @return mixed
     */
    public function getNormalWechatAccount($id)
    {
        return $this->find(array('id' => $id, 'status' => 1), 'token,appid,appsecret');
    }

}
