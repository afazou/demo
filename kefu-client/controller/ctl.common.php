<?php
class ctl_common extends ctl_base
{
    protected $checkM;
    protected $configM;
    protected $url;
    protected $wxApiM;
    protected static $cacheHandle;

    public function __construct()
    {
        parent::__construct();
        if (!self::$cacheHandle) {
            self::$cacheHandle = self::loadModel('cache');
        }
        $this->configM = self::loadModel('config');
        $this->url = self::loadModel('url');
        $this->checkM = self::loadModel('check');
        $this->wxApiM = self::loadModel('wx_api');
        $this->initialize();
    }

    /**
     * 初始化配置项
     */
    protected function initialize()
    {
        CC($this->configM->readConfig());
    }

    /**
     * http请求
     * @date   2016-05-10T11:07:53+0800
     * @param  [type]                   $url          请求url
     * @param  string                   $post         post数组
     * @param  string                   $cookie       请求需要发送的cookie
     * @param  integer                  $returnCookie 是否返回服务端cookie
     * @param  string                   $refer        请求来源url
     * @param  integer                  $time_out     超时时间
     * @return [type]                                 如果returnCookie为true，返回cookie和content数组，否则返回请求值
     */
    protected function curl_request($url, $post = array(), $returnCookie = false, $cookie = '', $refer = '', $time_out = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, $refer);
        if ($post)
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
        }
        if ($cookie)
        {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, $time_out);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);
        if (curl_errno($curl))
        {
            return false;
        }
        curl_close($curl);
        if ($returnCookie)
        {
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = array();
            if ($matches[1])
            {
                $matches_cookie_arr = $matches[1];
                $cookie_arr = array();
                foreach ($matches_cookie_arr as $row_str)
                {
                    list($key, $val) = explode('=', $row_str);
                    $cookie_arr[trim($key)] = trim($val);
                }
                $info['cookie'] = $cookie_arr;
            }
            $info['header'] = $header;
            $info['response'] = $data;
            $info['content'] = $body;
            return $info;
        }
        else
        {
            return $data;
        }
    }


}

