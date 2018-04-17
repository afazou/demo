<?php
class mdl_state_log extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_state_log';
	}

    /**
     * 客服登录后或设置客服状态时的操作
     *
     * @param $data
     * @return bool|mixed
     */
    public function addStateLog($data){
        $map = array();
        $map['only'] = $data['only'];
        $map['table'] = date('Y-m-d');
        $info = $this->getByStartTimeDesc($map);
        if(!$info || $info['end_time']){
            $data['table'] = $map['table'];
            $data['start_time'] = time();
        }else{
            if($data['state'] == $info['state']){
                return true;
            }
            $map['id'] = $info['id'];
            $ret = $this->save($map, array('end_time' => time()));
            if($ret){
                $data['table'] = $info['table'];
                $data['start_time'] = time();
            }else{
                return false;
            }
        }
        $res = $this->add($data);
        return $res;
    }

    /**
     * @param $map
     * @return mixed
     */
    protected function getByStartTimeDesc($map)
    {
        return $this->find(sprintf("( `only` = '%s' ) AND ( `table` = '%s' )", $map['only'], $map['table']), '*', 'start_time desc');
    }

    /**
     * 客服退出登录相关操作
     *
     * @param $only
     * @return mixed
     */
    public function loginoutStateLog($only){
        $data = array(
            'only' => $only,
            'state' => 4,
            'start_time' => time(),
            'table' => date('Y-m-d'),
        );
        $info = $this->getByStartTimeDesc(array('only' => $only, 'table' => date('Y-m-d')));
        if ($info) {
            $ret = $this->save(array('id' => $info['id']), array('end_time' => time()));
        }
        $res = $this->add($data);
        return $res;
    }


}
