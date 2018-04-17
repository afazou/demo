<?php
namespace Kefu\Model;
class StateLog extends Base
{
    public function __construct()
	{
		parent::__construct();
	    $this->table = 'state_log';
	}

}
