<?php
namespace Kefu\Lib;
use \Kefu\Lib\LoginObserved;
interface LoginObserverInterface
{
    public function update(LoginObserved $Observed);
}

