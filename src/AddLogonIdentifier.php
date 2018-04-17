<?php
namespace Kefu\Lib;
use \Kefu\Lib\LoginObserver;
use \Kefu\Model\Check;
class AddLogonIdentifier extends LoginObserver
{
    public function doUpdate(Login $login)
    {
        $account = $login->getAccount();
        $checkM = new Check();
        $checkM->insert(array(
            'uid' => $account['id'],
            'session_id' => session_id(),
            'endtime' => time(),
        ));
    }
}

