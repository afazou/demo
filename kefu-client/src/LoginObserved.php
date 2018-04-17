<?php
namespace Kefu\Lib;
use \Kefu\Lib\LoginObserverInterface;
interface LoginObserved
{
    public function attach(LoginObserverInterface $Observer);
    public function notify();
}

