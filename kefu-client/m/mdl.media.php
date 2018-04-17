<?php
class mdl_media extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_media';
	}

    public function findByMediaId($media_id)
    {
        return $this->find(array('media_id' => $media_id));
    }

    public function adds($data)
    {
        $data['md5']  = md5_file($data['path']);
        $data['sha1'] = sha1_file($data['path']);
        return $this->add($data);
    }

}
