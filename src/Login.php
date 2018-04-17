<?php
namespace Kefu\Lib;
use \Kefu\Lib\Exception as KfException;
use \Exception;
use \Kefu\Model\Customer;
use \Kefu\Model\Check;
use \Kefu\Lib\LoginObserved;
use \Kefu\Lib\LoginObserverInterface;
class Login implements LoginObserved
{
    protected $status;
    protected $account;
    protected $observer = array();

    public function attach(LoginObserverInterface $Observer)
    {
        $this->observer[] = $Observer;
    }
    public function notify()
    {
        foreach ($this->observer as $item) {
            $item->update($this);
        }
    }

    public function signIn($userName, $password, $status = null)
    {
        try {
            // 校验参数是否为空
            $this->checkLogonParams($userName, $password);
            // 设置登录账号信息
            $this->setAccount($userName, $password);
            // 校验账号状态信息
            $this->checkAccountState();
            // 设置登录后的状态
            $this->setStatus($status);
            // 登录验证通过后通知后续操作
            $this->notify();
            return true;
        } catch (KfException $e) {
            return $e->getMessage();
        } catch (Exception $e) {
            appLog(__FUNCTION__, $e->getMessage());
            return false;
        }
    }

    /**
     * 设置登录后的状态
     *
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * 获取登录后设置的状态
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 校验登录参数
     *
     * @param $userName
     * @param $password
     * @return bool
     * @throws \Kefu\Lib\Exception
     */
    public function checkLogonParams($userName, $password)
    {
        if (empty($userName)) {
            throw new KfException('用户名不能为空');
        }
        if (empty($password)) {
            throw new KfException('密码不能为空');
        }
        return true;
    }

    /**
     * 根据用户名获取账号信息
     *
     * @param $userName
     * @return mixed
     */
    public function getAccountInfoByUserName($userName)
    {
        $customerM = new Customer();
        $data = $customerM->get('*', array('username' => $userName));
        return $data;
    }

    /**
     * 设置登录账号信息
     *
     * @param $userName
     * @param $password
     * @throws \Kefu\Lib\Exception
     */
    protected function setAccount($userName, $password)
    {
        $account = $this->getAccountInfoByUserName($userName);
        if (empty($account)) {
            throw new KfException('用户名或密码错误');
        }
        if ($account['password'] != $password) {
            throw new KfException('用户名或密码错误');
        }
        $this->account = $account;
    }

    /**
     * 获取登录账号信息
     *
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * 校验账号状态信息
     *
     * @return bool
     * @throws \Kefu\Lib\Exception
     */
    protected function checkAccountState()
    {
        if (!in_array($this->account['status'], array(1, 2, 3))) {
            throw new KfException('用户状态异常');
        }
        $accountCheck = $this->getAccountCheck($this->account['id']);
        if ($accountCheck) {
            throw new KfException('用户登陆状态存在请退出后再重新登陆');
        }
        return true;
    }

    protected function getAccountCheck($uid)
    {
        $checkM = new Check();
        $data = $checkM->get('*', array('uid' => $uid));
        return $data;
    }

    /**
     * 获取登录Session
     *
     * @return mixed
     */
    public function getSession()
    {
        return c_session('user_auth');
    }




}

