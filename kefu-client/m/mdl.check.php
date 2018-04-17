<?php
class mdl_check extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_check';
	}

    /**
     * 检查客服是否登陆
     * Enter description here ...
     */
    public function is_login(){
        $user = c_session('user_auth');
        $loginuser = $this->getSessionInfo($user);
        if (empty($user) || empty($loginuser) || $loginuser == false) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * @param $user
     * @return mixed
     */
    protected function getSessionInfo($user)
    {
        return $this->find(sprintf("( `uid` = %s ) AND ( `session_id` = '%s' )", $user['uid'], session_id()));
    }

    /**
     * 根据uid获取登陆状态信息
     *
     * @param $uid
     * @param null $fields
     * @return mixed
     */
    public function getByUid($uid, $fields = null)
    {
        return $this->find(array('uid' => $uid), $fields);
    }


}
