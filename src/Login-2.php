<?php
/**
 * 该文件对应的测试文件是：LoginTest、LoginTest2、LoginTest3
 */
namespace Kefu\Lib;
use \Kefu\Lib\Exception as KfException;
use \Exception;
use \Kefu\Model\Customer;
use \Kefu\Model\Check;
class Login
{
    public function signIn($userName, $password)
    {
        try {
            // 校验参数是否为空
            $this->checkLogonParams($userName, $password);
            // 获取登录账号信息
            $account = $this->getAccount($userName, $password);
            // 校验账号状态信息
            $this->checkAccountState($account);
            return true;
        } catch (KfException $e) {
            return $e->getMessage();
        } catch (Exception $e) {
            return false;
        }
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
     * 获取登录账号信息
     *
     * @param $userName
     * @param $password
     * @return mixed
     * @throws \Kefu\Lib\Exception
     */
    protected function getAccount($userName, $password)
    {
        $account = $this->getAccountInfoByUserName($userName);
        if (empty($account)) {
            throw new KfException('用户名或密码错误');
        }
        if ($account['password'] != $password) {
            throw new KfException('用户名或密码错误');
        }
        return $account;
    }

    /**
     * 校验账号状态信息
     *
     * @param $account
     * @return bool
     * @throws \Kefu\Lib\Exception
     */
    protected function checkAccountState($account)
    {
        if (!in_array($account['status'], array(1, 2, 3))) {
            throw new KfException('用户状态异常');
        }
        $accountCheck = $this->getAccountCheck($account['id']);
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



    public function signOut($userId)
    {
        try {

        } catch (KfException $e) {

        } catch (Exception $e) {

        }
    }

}

