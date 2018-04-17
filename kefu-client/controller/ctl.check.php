<?php
class ctl_check extends ctl_common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        $checkM = $this->checkM;
        $customerM = self::loadModel('customer');
        $kefuListsM = self::loadModel('kefu_lists');
        $appM = self::loadModel('app');

        $loginuser = $checkM->doSelect(null, array('endtime' => array('<>', '')));
        $logontuser = array();
        $out = "";
        foreach ($loginuser as $user) {
            if((time() - $user['endtime']) > 60)
            {
                $logontuser[$user['uid']] = $user;
            }
        }
        foreach ($logontuser as $uid => $val) {
            $userinfo = $customerM->find(array('id'=>$uid));
            if ($userinfo['id']) {
                $checkM->del("uid={$userinfo['id']}");
            }
            session_id($val['session_id']);
            session_start();
            $_SESSION['wewin8_home'] = array();
            //------------------------------------------------------------------
            // 获取所有符合条件的队列
            $list = $kefuListsM->doSelect(null, array('only'=>$userinfo['only']));
            $kefuListsM->del(array('only'=>$userinfo['only']));
            foreach ($list as $item) {
                $options = $appM->getNormalWechatAccount($item['token']);
                $this->wxApiM->sendCustomMessage([$options], [$item['openid'], "登陆超时自动断开，请重新接入人工客服！"]);
                self::$cacheHandle->delCache(ChatStatusKefu($item['token'], $item['openid']));
            }
        }
    }

}

