<?php
class mdl_kefu_lists extends mdl_common
{

    /**
     * 对话列表最大长度
     *
     * @var int
     */
    const QUE_MAX_LENGTH = 172800;  // 即只处理48小时内发起的对话

    /**
     * 服务器时间
     *
     * @var int
     */
    protected $serverTime;

    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_kefu_lists';
		$this->setServerTime();
	}

	protected function setServerTime()
    {
        $this->serverTime = time();
    }

    protected function getQueStartTime()
    {
        return $this->serverTime - self::QUE_MAX_LENGTH;
    }

    /**
     * 获取对话列表
     *
     * @param $only
     * @param null $state
     * @return array
     */
    public function getLists($only, $state = null){
        $map = array('only' => $only, 'time' => array('>', $this->getQueStartTime()));
        if (is_numeric($state)) {
            $map['state'] = $state;
        }
        $this->setUpdateCache($only);
        $this->autoAddSessionUser($only);
        $return = array();
        $return['data'] = $this->getQueListReturnData($this->getQueList($map));
        // 统计条数
        $where = array('only'=>$only,'time'=>array('>', $this->getQueStartTime()));
        $return['count']['chat'] = $this->count($where + array('state' => 1));
        $return['count']['queuing'] = $this->count($where + array('state' => 0));
        $return['count']['wcat'] = $this->count($where + array('state' => -1));
        return $return;
    }

    /**
     * 返回对话列表数据
     *
     * @param $list
     * @return array
     */
    private function getQueListReturnData($list)
    {
        try {
            $_arr = array();
            if (empty($list)) {
                throw new Exception(null);
            }
            $openidModel = self::loadModel('openid');
            foreach ($list as $value) {
                $value['info'] 		= $openidModel->getWechatUserByOpenid($value['openid']);
                $value['unreadDot'] = 0;
                $value['outtime']	= $value['time'];
                $value['time'] 		= date('h:i', $value['time']);
                $value['chat']		= $value['state'] == 1 ? 'true' : 'false';
                $_arr[] = $value;
            }
            return $_arr;
        } catch (Exception $e) {
            return $_arr;
        }
    }

    /**
     * @param $only
     */
    protected function setUpdateCache($only)
    {
        self::$cacheHandle->setCache('update/' . $only, time());
    }

    /**
     * 获取会话用户数
     *
     * @param $only
     * @return mixed
     */
    protected function getQueSessionUserNum($only)
    {
        return $this->count(array('only' => $only, 'time' => array('>', $this->getQueStartTime()), 'state' => 1));
    }

    /**
     * 获取会话用户列表
     *
     * @param $only
     * @param $num
     * @return mixed
     */
    protected function getQueSessionUserList($only, $num = null)
    {
        $data = $this->doSelect(null, array('only' => $only, 'time' => array('>', $this->getQueStartTime()), 'state' => 1), $num, 'time ASC');
        return $data;
    }

    /**
     * 获取等待用户列表
     *
     * @param $only
     * @param $num
     * @return mixed
     */
    protected function getQueWaitUserList($only, $num = null)
    {
        $data = $this->doSelect(null, array('only' => $only, 'time' => array('>', $this->getQueStartTime()), 'state' => 0), $num, 'time ASC');
        return $data;
    }

    /**
     * 更新等待用户为会话用户
     *
     * @param $listIds
     * @return mixed
     */
    protected function upQueWaitForSessionByIds($listIds)
    {
        return $this->save(array('id' => array('IN', $listIds), 'state' => 0), array('state' => 1));
    }

    /**
     * 获取对话列表
     *
     * @param $map
     * @return mixed
     */
    protected function getQueList($map)
    {
        return $this->doSelect(null, $map, null, 'time ASC');
    }

    /**
     * 自动增加会话用户
     *
     * @param $only 客服标识
     */
    protected function autoAddSessionUser($only)
    {
        // 获取客服信息
        $customerInfo = self::loadModel('customer')->getByOnly($only, 'reception_num');
        // 获取当前会话数
        $onlinenum = $this->getQueSessionUserNum($only);
        // 获取空闲会话数
        $idleNumber = intval($customerInfo['reception_num'] - $onlinenum);
        if ($idleNumber <= 0) {
            return false;
        }
        $waitList = $this->getQueWaitUserList($only, $idleNumber);
        if (empty($waitList)) {
            return false;
        }
        $listIds = array();
        $appM = self::loadModel('app');
        $wxApiM = self::loadModel('wx_api');
        foreach ($waitList as $val) {
            $options = $appM->getNormalWechatAccount($val['token']);
            $wxApiM->sendCustomMessage([$options], [$val['openid'], '已经为您接入！']);
            array_push($listIds, $val['id']);
        }
        $this->upQueWaitForSessionByIds($listIds);
    }

    /**
     * 更新客服列表
     *
     * @param $token
     * @param $openid
     * @param $only
     * @param string $state
     * @param $chatid
     * @return mixed
     */
    public function upQueList($token, $openid, $only, $state = '1', $chatid)
    {
        $data = array(
            'chatid'    => $chatid,
            'token'     => $token,
            'openid'    => $openid,
            'only'      => $only,
            'state'     => $state,
            'time'      => time()
        );
        $info = $this->find(array('token' => $token, 'openid' => $openid, 'only' => $only), 'id');
        if (!empty($info)) {
            return $this->save(array('id' => $info['id']), $data);
        }
        $data['id'] = GenerationId();
        return $this->add($data);
    }

}
