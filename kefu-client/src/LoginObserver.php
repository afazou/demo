<?php
namespace Kefu\Lib;
use \Kefu\Lib\Login;
use \Kefu\Lib\LoginObserverInterface;
use \Kefu\Lib\LoginObserved;
abstract class LoginObserver implements LoginObserverInterface
{
    protected $login;
    public function __construct($login)
    {
        $this->login = $login;
        $this->login->attach($this);
    }

    public function update(LoginObserved $Observed)
    {
        if ($Observed === $this->login) {
            $this->doUpdate($Observed);
        }
    }
    abstract public function doUpdate(Login $login);
}

