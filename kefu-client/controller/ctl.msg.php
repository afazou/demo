<?php
class ctl_msg extends ctl_common
{
    protected $mediaM;
    protected $appM;
    public function __construct()
    {
        parent::__construct();
        $this->mediaM = self::loadModel('media');
        $this->appM = self::loadModel('app');
    }

    public function getmediaid()
    {
        $mediaid = $this->input->get('media',false);
        $token 	= $this->input->get('token');
        $type = $this->input->get('type','.jpg');
        $only	= getsession('only');

        $file = $this->mediaM->findByMediaId($mediaid);
        if($file)
        {
            $this->ajaxReturn(array(
                'data' 	=> array('file'=>$file['path']),
                'info'	=> 'success',
                'status'=> '1',
            ));
            exit();
        }

        if($mediaid !== false)
        {
            $options = $this->appM->getNormalWechatAccount($token);
            $file = $this->wxApiM->DownloadMedia([$options], [$mediaid, $type]);
            if($file){
                $data = array(
                    'path'		=> $file,
                    'media_id'	=> $mediaid
                );
                $this->mediaM->adds($data);
                $arr = array('file'=>$file);
                $this->ajaxReturn(array(
                    'data' 	=> $arr,
                    'info'	=> 'success',
                    'status'=> '1',
                ));
            }

        }

    }



}
