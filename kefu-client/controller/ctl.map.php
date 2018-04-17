<?php
class ctl_map extends ctl_common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $x 			= $this->input->get('x');
        $y 			= $this->input->get('y');
        $scale		= $this->input->get('scale');
        $this->assign('x',$x);
        $this->assign('y',$y);
        $this->assign('scale',$scale);
        $this->display('map/index');
    }



}
