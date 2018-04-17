<?php
namespace Kefu\Lib;
use \Kefu\Lib\LoginObserver;
use \Kefu\Model\Customer;
class UpdateCustomerStatus extends LoginObserver
{
    public function doUpdate(Login $login)
    {
        if ($login->getStatus()) {
            $account = $login->getAccount();
            $customerM = new Customer();
            $customerM->update(array('status' => $login->getStatus()), array('id' => $account['id']));
        }
    }
}

