<?php
class mdl_weixin_media extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_weixin_media';
	}

    public function is_media($media_id)
    {
        return $this->find(array('media_id'=>$media_id));
    }

}
