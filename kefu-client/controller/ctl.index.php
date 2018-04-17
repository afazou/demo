<?php
class ctl_index extends ctl_common
{
    protected $kefuListsM;
    protected $kefuMessageM;
    protected $customerM;
    public function __construct()
    {
        parent::__construct();
        if (!$this->checkM->is_login()) {
            redirect($this->url->getLogin());
        }
        $this->kefuListsM = self::loadModel('kefu_lists');
        $this->kefuMessageM = self::loadModel('kefu_message');
        $this->customerM = self::loadModel('customer');
    }

    public function index()
    {
        c_session('time', time());
        $na2 = sha1(md5(getsession('only').c_session('time')));
        $_catnames = CC('CATMSG');
        $catname = array();
        foreach ($_catnames as $key => $val) {
            $catname[] = array('id'=>$key,'name'=>$val);
        }
        $this->assign('catnames', $catname);
        $this->assign('na2', $na2);
        $this->assign('logoutUrl', $this->url->getLoginOut());
        $this->display('index/index');
    }

    /**
     * 获取用户列表
     */
    public function ajaxRequest()
    {
        $only 	= $this->input->get('only');
        $type 	= $this->input->get('type');
        // 用户列表
        $queList = $this->kefuListsM->getLists(getsession('only'), $type);
        // 缓存用户列表
        // $this->setQueListCache($queList);
        // 设置客服消息数
        $this->setKefuMsgNum($queList);
        $this->ajaxReturn($queList);
    }

    /**
     * 缓存用户列表
     *
     * @param $queList
     * @return mixed
     */
    protected function setQueListCache($queList)
    {
        return self::$cacheHandle->setCache('cachelist/' . getsession('only'), $queList);
    }

    /**
     * 设置客服消息数
     *
     * @param $queList
     */
    protected function setKefuMsgNum(&$queList)
    {
        try {
            if (empty($queList['data'])) {
                throw new Exception(null);
            }
            foreach ($queList['data'] as $key => &$val) {
                $cache = self::$cacheHandle->getCache(ChatStatusKefu($val['token'], $val['openid']));
                if($cache){
                    $map = array(
                        'token' => $val['token'],
                        'openid'=> $val['openid'],
                        'only'  => $val['only'],
                        'table'  => table_time($cache['time']),
                        'read'  => '0',
                        'author'=> 'you'
                    );
                    $val['count'] = $this->kefuMessageM->getMapCount($map);
                }else{
                    $val['count'] = 0;
                }
            }
        } catch (Exception $e) {

        }
    }

    /**
     * 每10秒更新一下时间超过30秒则自动退出，并通知用户
     */
    public function uplogin()
    {
        $only = $this->input->get('only');
        $userinfo = $this->customerM->getByOnly($only);
        $_stats = $this->checkM->getByUid($userinfo['id']);
        if(empty($_stats))
        {
            $this->checkM->add(array('uid'=>$userinfo['id'], 'endtime'=>time()));
        }else{
            $this->checkM->save("uid={$userinfo['id']}", array('endtime'=>time()));
        }
    }

    /**
     * 置为聊天状态
     */
    public function setChat()
    {
        $token 	= $this->input->get('token');
        $only 	= $this->input->get('only');
        $openid = $this->input->get('un');
        //+--------------------------------------------------------
        //|排队状态客户自动接入
        //+--------------------------------------------------------
        $where = array('only'=>$only, 'openid'=>$openid);
        $info = $this->kefuListsM->find($where);
        if($info['state'] != 1 || $info['state'] != -1){
            if($this->kefuListsM->save($where, array('state' => 1))){
                $options = self::loadModel('app')->getNormalWechatAccount($token);
                $cache = self::$cacheHandle->getCache(ChatStatusKefu($token, $openid));
                if($cache){
                    $this->wxApiM->sendCustomMessage([$options], [$openid, '已经为您接入！']);
                    $this->ajaxReturn('success');
                }else{

                }
            }
        }

    }

    /**
     * 获取聊天窗口消息列表
     */
    public function Messagelists()
    {
        $only 	= $this->input->get('only');
        $token 	= $this->input->get('token');
        $openid = $this->input->get('un');
        $time	= $this->input->get('time');
        $type	= $this->input->get('type');
        $cache = self::$cacheHandle->getCache(ChatStatusKefu($token, $openid));
        $table = date("Y-m-d", time());
        if($cache){
            $table = $cache['time'];
        }
        $list = $this->kefuMessageM->getLists($openid, getsession('only'), $time, $type, $table);
        $this->ajaxReturn($list);
    }

    /**
     * 发送消息
     */
    public function PostMessage()
    {
        $token 	= $this->input->post('token');
        $openid = $this->input->post('un');
        $text	= $this->input->post('text');
        $only	= getsession('only');

        $info = $this->kefuListsM->find(array(
            'token' => array('=', $token),
            'openid' => array('=',$openid),
            'only' => array('=', $only)
        ));

        //获取APP信息；
        $options = self::loadModel('app')->getNormalWechatAccount($info['token']);
        $cache = self::$cacheHandle->getCache(ChatStatusKefu($token,$openid));

        if($cache){
            $r = $this->wxApiM->sendCustomMessage([$options], [$openid, $text, 'text', true]);
            if(isset($r) && $r['errno'] == 0){
                $data = array(
                    'chatid'		=> $cache['chatid'],
                    'author'		=> 'me',
                    'token'			=> $token,
                    'openid'		=> $openid,
                    'only'			=> $only,
                    'message'		=> $text,
                    'type'			=> 'text',
                    'time'			=> time(),
                    'table'			=> table_time($cache['time']),
                );
                $this->kefuMessageM->Addinfo($data);
                $this->ajaxReturn('success');
            }else{
                if($r['errno'] == 500)
                {
                    if($options['appid'] == 'wx9b797be85ded9dc8'){
                        $get_tokenurl = CC('GET_PLUS_TOKEN_URL');
                    }else{
                        $get_tokenurl = CC('GET_TOKEN_URL');
                    }
                    $this->curl_request($get_tokenurl);
                }

                $this->ajaxReturn('error:'.$r['msg']);
            }
        }else{
            $this->ajaxReturn('error config');
        }
    }

    /**
     * 回复快捷键设置
     */
    public function setkey()
    {
        $val = $this->input->post('val', null);
        if($val == null){
            $this->ajaxReturn('error');
        }else{
            $data = array('key'=>$val);
            $s = $this->customerM->save(array('only'=>getsession('only')), $data);
            if($s){
                $this->ajaxReturn('success');
            }else{
                $this->ajaxReturn('error');
            }
        }
    }

    /**
     * 拉黑用户
     */
    public function setblack()
    {
        $token = $this->input->get('token');
        $openid = $this->input->get('openid');
        $only = $this->input->get('only');
        $openid = $this->input->get('openid',false);
        if($openid == false) $this->error("参数为空请刷新浏览器重新测试！");
        if(0 === self::loadModel('black')->add(array('openid'=>$openid,'time'=>time())))
        {
            $info = $this->kefuListsM->find(array('openid'=>$openid));
            $this->kefuListsM->del(array('openid'=>$openid));
            $options = self::loadModel('app')->getNormalWechatAccount($info['token']);
            $this->wxApiM->sendCustomMessage([$options], [$openid, "客服主动断开人工客服！"]);
            self::$cacheHandle->delCache(ChatStatusKefu($token,$openid));
            $this->success('成功添加到黑名单');
        }else{
            $this->error('添加失败！');
        }
    }

    /**
     * 检查是否设置分类
     */
    public function iscat()
    {
        $openid = $this->input->get('openid');
        $only = $this->input->get('only');
        $tokenid = $this->input->get('token');
        $cache = self::$cacheHandle->getCache(ChatStatusKefu($tokenid,$openid));
        $chat = self::loadModel('customer_chat')->find(array('id'=>$cache['chatid']));
        if(empty($chat['catid']))
        {
            $this->ajaxReturn('error');
        }else{
            $this->ajaxReturn('success');
        }
    }

    /**
     * 设置分类
     */
    public function setcat()
    {
        $openid = $this->input->get('openid');
        $only = $this->input->get('only');
        $tokenid = $this->input->get('tokenid');
        $catid = $this->input->get('catid');
        $isdel = $this->input->get('isdel');
        if($isdel == 'null'){
            $info = $this->kefuListsM->find(array('token' => $tokenid, 'only' => $only, 'openid' => $openid));
            if(self::loadModel('customer_chat')->save(array('id'=>$info['chatid']), array('catid'=>$catid,'catname'=>$this->getCatName($catid))))
            {
                $this->kefuListsM->del(array('id' => $info['id']));
                $this->ajaxReturn('success');
            }else{
                $this->ajaxReturn('error');
            }
        }
        $cache = self::$cacheHandle->getCache(ChatStatusKefu($tokenid,$openid));
        if (
            $cache &&
            self::loadModel('customer_chat')->save(array('id'=>$cache['chatid']), array('catid'=>$catid,'catname'=>$this->getCatName($catid)))
        ) {
            // true  设置分类后踢出队列
            // false 只设置分类不进行踢出操作
            if($isdel == 'true')
            {
                $info = $this->kefuListsM->find(array('token' => $tokenid, 'openid' => $openid, 'only' => $only));
                $this->kefuListsM->del(array('id' => $info['id']));
                $options = self::loadModel('app')->getNormalWechatAccount($info['token']);
                $this->wxApiM->sendCustomMessage([$options], [$openid, CC('CLOSE_MSG')]);
                $this->wxApiM->sendCustomMessage([$options], [$openid, CC('INTELLIGENCE_SUCC')]);
                $chat_cache = self::$cacheHandle->getCache(ChatStatusKefu($tokenid,$openid));
                self::$cacheHandle->delCache(ChatStatusKefu($tokenid,$openid));
                $cache = array(
                    'token'		=> $tokenid,
                    'openid'	=> $openid,
                    'only'		=> $only,
                    'chatid'	=> $chat_cache['chatid'],
                );
                self::$cacheHandle->setCache($openid.'score', $cache, 3600);
                $data[0] = array(
                    'title'	=> '客服MM诚邀您对本次服务进行评价',
                    'description'	=> '点击进入【服务评价】',
                    'url'	=> CC('HOST') . 'Api/Index/score'.'/id/'.$tokenid.'/key/'.$openid,
                    'picurl'	=> 'http://kf.wx.gome.com.cn/img/evaluate.png',
                );

                $this->wxApiM->sendCustomMessage([$options], [$openid, $data, 'news']);

            }
            $this->ajaxReturn('success');
        }else{
            $this->ajaxReturn('error');
        }
    }

    /**
     * 获取分类名称
     *
     * @param $id
     * @return mixed
     */
    private function getCatName($id)
    {
        $_catnames = CC('CATMSG');
        return $_catnames[$id];
    }

    /**
     * 获取历史（对话）记录
     */
    public function getchattime()
    {
        $token = $this->input->get('token');
        $openid = $this->input->get('openid');
        $only = $this->input->get('only');
        $times = self::loadModel('customer_chat')->doSelect(null, array('openid'=>$openid,'table'=>array('<',date('Y-m-d',time()))), null, 'id DESC');
        if(count($times) > 0){
            $this->ajaxReturn(array('list'=>$times));
        }else{
            $this->ajaxReturn('null');
        }
    }

    /**
     * 获取历史（对话）记录聊天信息
     */
    public function getchatmsg()
    {
        $openid = $this->input->get('openid');
        $table = $this->input->get('table');
        $chatid = $this->input->get('id');
        $list = $this->kefuMessageM->getitem(array('chatid'=>$chatid, 'table' => $table));
        if(count($list) > 0){
            $data = array();
            $openidModel = self::loadModel('openid');
            foreach ($list as $value) {
                $value['time'] 	= date('h:i',$value['time']);
                if($value['author'] == 'me'){
                    $value['avatar'] = '/Public/Client/images/guomei.jpg';
                }else{
                    $uinfo = $openidModel->getWechatUserByOpenid($value['openid']);
                    $value['avatar'] = empty($uinfo['headimgurl'])? '/Public/Client/images/webwxgeticon.jpg':$uinfo['headimgurl'];
                }
                $data[] = $value;
            }
            $this->ajaxReturn(array('list'=>$data));
        }else{
            $this->ajaxReturn('null');
        }
    }

    /**
     * 根据Media_id获取附件地址
     */
    public function getmediaid()
    {
        $token 	= $this->input->get('token');
        $media_id = $this->input->get('media_id');
        $file = array();
        $weixinMediaM = self::loadModel('weixin_media');
        if($media_id){
            $file = $weixinMediaM->is_media($media_id);
        }
        if(empty($file)){
            $options = self::loadModel('app')->getNormalWechatAccount($token);
            $file = $this->wxApiM->DownloadMedia([$options], [$media_id]);
            $weixinMediaM->add(array('media_id'=>$media_id,'path'=>$file,'time'=>time()));
            $this->ajaxReturn(array('file'=>$file));
        }else{
            $this->ajaxReturn(array('file'=>$file['path']));
        }
    }

    /**
     * 关闭用户
     */
    public function Chatclose()
    {
        $token = $this->input->get('token');
        $openid = $this->input->get('openid');
        $only = $this->input->get('only');
        $info = $this->kefuListsM->find(array('token' => $token, 'openid' => $openid, 'only' => $only));
        if ($info && $this->kefuListsM->del(array('id' => $info['id']))) {
            $options = self::loadModel('app')->getNormalWechatAccount($info['token']);
            $this->wxApiM->sendCustomMessage([$options], [$openid, CC('CLOSE_MSG')]);
            $this->wxApiM->sendCustomMessage([$options], [$openid, CC('INTELLIGENCE_SUCC')]);
            $chat_cache = self::$cacheHandle->getCache(ChatStatusKefu($token, $openid));
            self::$cacheHandle->delCache(ChatStatusKefu($token, $openid));
            $cache = array(
                'token'		=> $token,
                'openid'	=> $openid,
                'only'		=> $only,
                'chatid'	=> $chat_cache['chatid'],
            );
            self::$cacheHandle->setCache($openid.'score', $cache, 3600);
            $data[0] = array(
                'title'	=> '客服MM诚邀您对本次服务进行评价',
                'description'	=> '点击进入【服务评价】',
                'url'	=> CC('HOST') . 'Api/Index/score'.'/id/'.$token.'/key/'.$openid,
                'picurl'	=> 'http://kf.wx.gome.com.cn/img/evaluate.png',
            );
            $this->wxApiM->sendCustomMessage([$options], [$openid,$data,'news']);
        } else {
            echo 'error';
        }

    }

}

