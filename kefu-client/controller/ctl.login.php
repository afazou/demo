<?php
class ctl_login extends ctl_common
{
    protected $customerM;
    protected $stateLogM;
    public function __construct()
    {
        parent::__construct();
        $this->customerM = self::loadModel('customer');
        $this->stateLogM = self::loadModel('state_log');
    }

    /**
     * 登录页
     */
    public function index()
    {
        if ($_POST) {
            extract($this->getLoginParams());
            if (!$this->getVerifyInstance()->check($verify, 1)) {
                $this->error('验证码错误！');
            }
            if(empty($token) || $token != c_session('token')) {
                $this->error('非法请求！');
            }
            if (!in_array($userstate, array(1,2,3))) {
                $this->error('客服状态错误');
            }
            $crypt = $this->getAesInstance($token);
            $password= $crypt->decrypt($crypt->decrypt($password));
            $username= $crypt->decrypt($crypt->decrypt($username));
            // 根据登录名获取用户信息
            $userinfo = $this->customerM->find(array('username' => $username));
            if (empty($userinfo)) {
                $this->error('账号不存在！');
            }
            // 检查客服是否超时登陆
            $loginState = $this->checkM->find('`endtime` <> \'""\' AND `uid`='.$userinfo['id']);
            if ($loginState) {
                if ((time() - $loginState['endtime']) < 10) {
                    $this->error('用户登陆状态存在请退出后再重新登陆');
                }
            }
            // 调用客服登陆逻辑处理
            $Customer = $this->customerM->login($userinfo, $username, $password);
            if($Customer){

                // ---------------------------------------------------------------------------------------------



                // 更新客服状态功能
                $ret = $this->customerM->save(array('id' => $userinfo['id']), array('status'=>$userstate));



                $this->setLoginUserSession($Customer, $userstate);

                // ---------------------------------------------------------------------------------------------

                $this->success('sucess', $this->url->getIndex());

            }else{
                $errorInfo = '客服'.$username.'登陆失败，记录IP：'.$this->input->ip_address();
                LogFile($errorInfo);
                $this->error('登陆失败，请检查账号与密码是否正确');
            }

        } else {

            if ($this->checkM->is_login()) {
                redirect($this->url->getIndex());
            }

            $token = $this->create_token(16);
            c_session('token', $token);
            $this->assign('token', $token);
            $this->display('login/index');
        }
    }

    /**
     * 验证码
     */
    public function verify(){
        $verify = $this->getVerifyInstance();
        $verify->entry(1);
    }

    protected function create_token($length = 8){
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $token= '';
        for ( $i = 0; $i < $length; $i++ ) {
            $token.= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }

        return $token;
    }

    protected function getVerifyInstance()
    {
        self::loader('lib', 'Verify');
        return new Verify();
    }

    protected function getAesInstance($token)
    {
        self::loader('lib', 'AESCBC');
        return new AESCBC($token);
    }

    /**
     * 设置登录状态
     */
    public function setstate()
    {
        if($this->checkM->is_login()){
            $state 	= intval($this->input->post('state', 1));
            $only = getsession('only');

            $state_arr = array(1,2,3);
            if(in_array($state,$state_arr)){
                $res = $this->customerM->save(array('only'=>$only), array('status'=>$state));
                $user_auth = c_session('user_auth');
                $user_auth['userstate'] = $state;
                c_session('user_auth',$user_auth);
                $state_data['only'] = $only;
                $state_data['state'] = $state;
                $res = $this->stateLogM->addStateLog($state_data);;
            }
            if($res)
                $return['status'] = '200';
            else
                $return['status'] = '0';
            return $this->ajaxReturn($return);
        }else{
            redirect($this->url->getLogin());
        }
    }


    /**
     * 退出登录
     */
    public function logout()
    {
        if (!$this->checkM->is_login()) {
            redirect($this->url->getLogin());
        }
        $only = getsession('only');
        $userinfo = $this->customerM->getByOnly($only);
        $kefuListsM = self::loadModel('kefu_lists');
        $list = $kefuListsM->doSelect(null, array('only'=>$only));
        $kefuListsM->del(array('only'=>$only));
        $this->checkM->del("uid={$userinfo['id']}");

        if ($list) {
            $appM = self::loadModel('app');
            foreach ($list as $item) {
                $options = $appM->getNormalWechatAccount($item['token']);
                $this->wxApiM->sendCustomMessage([$options], [$item['openid'], CC('EXCE_MSG')]);
                self::$cacheHandle->delCache(ChatStatusKefu($item['token'], $item['openid']));
            }
        }

        //增加退出时间日志
        $this->stateLogM->loginoutStateLog($only);
        // 清空会话信息
        c_session('user_auth', null);
        // 删除缓存
        self::$cacheHandle->delCache('update/' . $only);
        redirect($this->url->getLogin());
    }

    /**
     * 获取登录参数
     *
     * @return array
     */
    protected function getLoginParams()
    {
        $params = array(
            'verify' => '',
            'username' => '',
            'password' => '',
            'userstate' => 1,
            'token' => '',
        );
        foreach ($params as $name => $default) {
            $params[$name] = $this->input->post($name, $default);
        }
        return $params;
    }

    /**
     * 设置登录用户会话信息
     *
     * @param $Customer
     * @param $userstate
     */
    protected function setLoginUserSession($Customer, $userstate)
    {
        $arr = array();
        $arr['username'] = $Customer['username'];
        $arr['only'] = $Customer['only'];
        $arr['key'] = $Customer['key'];
        $arr['uid'] = $Customer['id'];
        $arr['userstate'] = $userstate;
        c_session('user_auth', $arr);
    }

}
