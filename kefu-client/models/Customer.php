<?php
namespace Kefu\Model;
class Customer extends Base
{
    public function __construct()
	{
		parent::__construct();
	    $this->table = 'customer';
	}

}
