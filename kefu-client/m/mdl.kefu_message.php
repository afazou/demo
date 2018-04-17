<?php
class mdl_kefu_message extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_kefu_message';
	}

    public function getMapCount($map){
        $count = $this->count($map);
        return $count? $count : 0;
    }

    /**
     * 获取聊天窗口消息列表
     *
     * @param $openid
     * @param $only
     * @param $time
     * @param $type
     * @param null $table
     * @return array
     */
    public function getLists($openid, $only, $time, $type, $table = null)
    {
        $map = array();
        $map['table'] = $table ? table_time($table) : date("Y-m-d", time());
        $map['openid'] 	= array('=',$openid);
        $map['only'] 	= array('=',$only);
        if($time != 'today'){
            $map['read'] 	= array('=','0');
        }
        if($type == 'up'){
            $map['author'] 	= array('=','you');
            $map['read'] 	= array('=','0');
        }
        $list = $this->doSelect(null, $map, null, 'time ASC');
        $return = array();
        if ($list) {
            $openidModel = self::loadModel('openid');
            foreach ($list as $key => $value) {
                $value['time'] 	= date('h:i',$value['time']);
                if($value['author'] == 'me'){
                    $value['avatar'] = '/Public/Client/images/guomei.jpg';
                }else{
                    $uinfo = $openidModel->getWechatUserByOpenid($value['openid']);
                    $value['avatar'] = empty($uinfo['headimgurl'])? '/Public/Client/images/webwxgeticon.jpg':$uinfo['headimgurl'];
                }
                $this->save(array('id' => $value['id']), array('read' => 1, 'read_time' => time()));
                $return[] = $value;
            }
        }
        return $return;
    }

    public function Addinfo($data)
    {
        $id = $this->add($data);
        if(!$id){
            return false;
        }else{
            return true;
        }
    }

    public function getitem($where){
        $list = $this->doSelect(null, $where, null, 'time ASC');
        return $list;
    }


}
