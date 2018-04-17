<?php
class ctl_api extends ctl_common
{
    protected $token = 6;
    protected $openid = 'oyNuujn_zXi0EjcFMgCeQi0xpcss';
    protected $only = '56418405bd349';
    protected $customerChatM;
    protected $kefuMessageM;
    public function __construct()
    {
        parent::__construct();
        $this->customerChatM = self::loadModel('customer_chat');
        $this->kefuMessageM = self::loadModel('kefu_message');
    }

    public function index()
    {
        $options = self::loadModel('app')->getNormalWechatAccount(6);
        $this->wxApiM->sendCustomMessage([$options, '111111111111'], ['xxxxx', 'cccccc', 'text', true]);
    }

    /**
     * 微信会员回复“K”后触发人工客服 与系统客服队列模块进行互动，获取接待客服ID
     */
    public function k()
    {
        $kefuListsM = self::loadModel('kefu_lists');
        $room = $this->customerChatM->getSessionRoom($this->openid);
        if ($room) {
            $this->text();
        }
        $roomId = $this->customerChatM->createRoom($this->token, $this->openid, $this->only);
        if ($roomId) {
            $kefuListsM->upQueList($this->token, $this->openid, $this->only, 1, $roomId);
            self::$cacheHandle->setCache(md5($this->token . $this->openid . $this->only, 1));
            exit('有一种快乐飘飘洒洒，有一种幸福安安静静，有一种祝福长长久久，有一个我O(∩_∩)O~伴您快乐每一天！（回复J退出人工客服）');
        }
        exit('接入人工客服失败！可能是系统太过繁忙，请稍后再试！');
    }

    /**
     * 发送文本消息
     */
    public function text()
    {
        $content 	= $this->input->get('content', uniqid(uniqid(), true));
        $room = $this->customerChatM->getSessionRoom($this->openid);
        $data = array(
            'author'		=> 'you',
            'token'			=> $this->token,
            'chatid'		=> $room['chatid'],
            'openid'		=> $this->openid,
            'only'			=> $room['only'],
            'message'		=> $content,
            'type'			=> 'text',
            'time'			=> time(),
            'table'			=> table_time($room['time'])
        );
        exit($this->kefuMessageM->Addinfo($data));
    }

    /**
     * 发送图片
     */
    public function img()
    {
        $room = $this->customerChatM->getSessionRoom($this->openid);
        $data = array(
            'author'		=> 'you',
            'token'			=> $this->token,
            'chatid'		=> $room['chatid'],
            'openid'		=> $this->openid,
            'only'			=> $room['only'],
            'message'		=> '{"picUrl":"2018-03-29\/t75335abc5cfe692a9.jpg","wpicUrl":"http:\/\/mmbiz.qpic.cn\/mmbiz_jpg\/5W6LpcJOIVUJQDRUgpBibxG9jicD2hLqYjtNh1ophT29loxqDn1XxaqDzCicR97okpr449beeFHwnmGxOQMNb5FLA\/0","mediaId":"z_oenfBvqMB0mzqZvFoV8WQJCR9_wIHkzUFnmCWg3kevyrZvfDnQg4aBAHs5rsAh"}',
            'type'			=> 'image',
            'time'			=> time(),
            'table'			=> table_time($room['time'])
        );
        exit($this->kefuMessageM->Addinfo($data));
    }

    public function voice()
    {
        $room = $this->customerChatM->getSessionRoom($this->openid);
        $data = array(
            'author'		=> 'you',
            'token'			=> $this->token,
            'chatid'		=> $room['chatid'],
            'openid'		=> $this->openid,
            'only'			=> $room['only'],
            'message'		=> '{"mediaId":"KMdhstRn4Ei4GTIyKydH2K_jS1pVWfT8e9YEW0QXSRPJ93EpB5L5V2gVmW5wvdP4","format":"amr","text":"\u6b7b\u3002"}',
            'type'			=> 'voice',
            'time'			=> time(),
            'table'			=> table_time($room['time'])
        );
        exit($this->kefuMessageM->Addinfo($data));
    }



}
