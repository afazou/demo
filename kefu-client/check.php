<?php
if ('CLI' != strtoupper(PHP_SAPI)) {
    exit();
}
define('ROOT_PATH', dirname(__FILE__));
define('APPLICATION_PATH', ROOT_PATH);
// 开发环境
define('APP_ENV', empty($_SERVER['KEFU_CLIENT_ENV']) ? 'LIVE' : $_SERVER['KEFU_CLIENT_ENV']);
// 是否调试模式
define('DEBUG', false);
// 初始化配置
require ROOT_PATH . '/common/function.php';
CC(require ROOT_PATH . '/config/convention_'.strtolower(APP_ENV).'.php');
// 载入核心文件
require ROOT_PATH. '/../kefu-common/core/route.php';
require ROOT_PATH . '/m/mdl.common.php';
require ROOT_PATH . '/controller/ctl.common.php';

function loadModel($model) {
    loader('m', $model);
    $words = explode('/', $model);
    $model = end($words);
    $action = 'mdl_' . $model;
    return new $action();
}

function loader($type = 'm', $name) {
    $dir = ROOT_PATH . '/' . $type;
    if ($type == 'm') {
        $words = explode('/', $name);
        if (count($words) == 1) {
            $file_path = sprintf($dir . '/%s.php', 'mdl.' . $name);
        } else {
            $file_path = sprintf($dir . '/%s/%s.php', $words[0], 'mdl.' . $words[1]);
        }
    } elseif ($type == 'lib') {
        $file_path = sprintf($dir . '/%s.class.php', $name);
    } else {
        $file_path = sprintf($dir . '/%s.php', $name);
    }
    include_once $file_path;
}

function run()
{
    $checkM = loadModel('check');
    $customerM = loadModel('customer');
    $kefuListsM = loadModel('kefu_lists');
    $appM = loadModel('app');
    $wxApiM = loadModel('wx_api');
    $cacheM = loadModel('cache');

    while (true) {
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
                $wxApiM->sendCustomMessage([$options], [$item['openid'], "登陆超时自动断开，请重新接入人工客服！"]);
                $cacheM->delCache(ChatStatusKefu($item['token'], $item['openid']));
            }
        }
        sleep(1);
    }

}

run();

