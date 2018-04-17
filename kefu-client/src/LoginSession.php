<?php
namespace Kefu\Lib;
use \Kefu\Lib\LoginObserver;
class LoginSession extends LoginObserver
{
    protected $auth = array();
    public function doUpdate(Login $login)
    {
        $this->setAuth($login);
        c_session('user_auth', $this->auth);
    }

    /**
     * 设置认证会话
     *
     * @param $login
     */
    public function setAuth($login)
    {
        $account = $login->getAccount();
        $this->auth['username'] = $account['username'];
        $this->auth['only'] = $account['only'];
        $this->auth['key'] = $account['key'];
        $this->auth['uid'] = $account['id'];
        $this->auth['userstate'] = $login->getStatus();
    }

    /**
     * 获取认证会话
     *
     * @return array
     */
    public function getAuth()
    {
        return $this->auth;
    }

}

