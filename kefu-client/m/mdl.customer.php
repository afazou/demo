<?php
class mdl_customer extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_customer';
	}

    /**
     * 登录校验
     *
     * @param $userinfo
     * @param $username
     * @param $password
     * @return bool
     */
    public function login($userinfo, $username, $password)
    {
        if (
            $userinfo['password'] == $password
            && in_array($userinfo['status'], array(1, 2, 3))
        ) {
            return $userinfo;
        }
        return false;
    }

    /**
     * 根据only获取客服信息
     *
     * @param $only
     * @param $fields
     * @return mixed
     */
    public function getByOnly($only, $fields = null)
    {
        return $this->find(array('only' => $only), $fields);
    }



}
