<?php
class mdl_customer_state extends mdl_common
{
    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_customer_state';
	}


    /**
     * 处理客服状态
     * Enter description here ...
     * @param string $only	客服唯一识别ID
     * @param string $token APP Token
     * @param int $state  客服状态  1:在线，2：忙碌,0：离线;
     * @param int $num		接待人数
     */
    public function update($only,$num,$state=1){
        $map['only'] 	= $only;
        $data = array(
            'only'		=> $only,
            'state'		=> $state,
            'num'		=> $num
        );

        if(!$info = $this->getByOnly($map)){
            $id = $this->add($data);
            if (0 === $id) {
                $id = true;
            }
        }else{
            if($info['num'] != $num){
                $id = $this->saveByOnly($map, $data);
            }else{
                $id = true;
            }
        }
        return $id;
    }

    /**
     * @param $map
     * @return mixed
     */
    protected function getByOnly($map)
    {
        return $this->find(sprintf("`only` = '%s'", $map['only']));
    }

    /**
     * @param $map
     * @param $data
     * @return mixed
     */
    protected function saveByOnly($map, $data)
    {
        return $this->save(sprintf("`only` = '%s'", $map['only']), $data);
    }

    /**
     * 获取当前客服的接待人数
     *
     * @param $only
     * @return bool
     */
    public function getNum($only)
    {
        $map['only'] 	= $only;
        $num = $this->find(array('only' => $only), 'num');
        if($num){
            return $num['num'];
        }else{
            return false;
        }

    }

}
