<?php
namespace Kefu\Lib;
use \Kefu\Lib\LoginObserver;
use \Kefu\Model\StateLog;
class AddStateLog extends LoginObserver
{
    public function doUpdate(Login $login)
    {
        $account = $login->getAccount();
        $stateLogM = new StateLog();
        $stateLogM->insert(array(
            'only' => $account['only'],
            'table' => date('Y-m-d'),
            'start_time' => time(),
            'state' => $login->getStatus(),
        ));
    }
}

