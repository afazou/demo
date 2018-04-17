<?php defined('ROOT_PATH') || die('Access denied!');
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: model.php 2013-12-23 11:18:22Z lilixing $
 */
class Model {
    protected $input;
    protected $cache_dao;
    protected $db_dao;
    protected $log_file = '';
    public $we_obj = null;
    public $db_pre = '';
    protected static $model = array();
    public function __construct() {
        $this->input = Input::get_instance();
        $this->cache_dao = RedisCache::get_instance(0);
        $this->db_dao = MySql::get_instance(0);
        $this->log_file = $this->input->get('ctl') . '-' . $this->input->get('act');
        $db_config =  Config::get('database');
        $this->db_pre = $db_config[0]['DB_PREFIX'];
    }

    /**
     * setCache  存cache
     *
     * @param    $key
     * @param    $val
     * @param    $expire_time    过期时间
     * @return     object $rs_cache
     * @author    <llx>lilixing@yolo24.com
     */
    protected function setCache($key, $val, $expire_time = 1800) {
        $rs_cache = $this->cache_dao->hmset($key, $val, $expire_time);

        return $rs_cache ? true : false;
    }

    /**
     * getCache  取cache
     *
     * @param    $key
     * @return    object $rs
     * @author   <llx>lilixing@yolo24.com
     *
     */
    protected function getCache($key) {
        $rs = $this->cache_dao->hgetall($key);
        Log2File('获取redis返回信息'. json_encode($rs), 'get_redis');
        return $rs;
    }

    /**
     * delCache    删cache
     *
     * @param    $key
     * @return     object $rs
     * @author    <llx>lilixing@yolo24.com
     *
     */
    protected function delCache($key) {
        $rs = $this->cache_dao->delete($key);

        return $rs ? true : false;
    }
    /*
     * 获取db
     *@return MySql
     */
    public function getDb($dbname = '') {
        $dbClassName = 'Mysql';
        $this->db_dao = $dbClassName::get_instance($dbname);
        return $this->db_dao;
    }

/**
 * 获取微信对象
 * @author <zxl>[zhangxianglong@yolo24.com]
 * @date   2015-07-22T12:21:11+0800
 * @return [type]                   [description]
 */
    public function getWeObj() {
        if (!$this->we_obj) {
            $options = array(
                'appid' => APP_ID,
                'token' => APP_TOKEN, //填写你设定的key
                'encodingaeskey' => APP_ENCODINGAESKEY, //填写加密用的EncodingAESKey
                'appsecret' => APP_SECRET, //填写高级调用功能的密钥
            );
            $this->we_obj = new weixin($options);
        }
        return $this->we_obj;
    }

    protected static function loadModel($model) {
        $key = $model;

        if (array_key_exists($key, self::$model)) {
            return self::$model[$key]->reset();
        } else {
            self::loader('m', $model);
            $words = explode('/', $model);
            $model = end($words);
            $action = 'mdl_' . $model;
            self::$model[$key] = new $action();
        }

        return self::$model[$key];
    }

    protected static function loader($type = 'm', $name) {
        $dir = ROOT_PATH . '/' . $type;
        if ($type == 'm') {
            $words = explode('/', $name);
            if (count($words) == 1) {
                $file_path = sprintf($dir . '/%s.php', 'mdl.' . $name);
            } else {
                $file_path = sprintf($dir . '/%s/%s.php', $words[0], 'mdl.' . $words[1]);
            }
        } elseif ($type == 'lib') {
            $file_path = sprintf($dir . '/%s.class.php', $name);
        } else {
            $file_path = sprintf($dir . '/%s.php', $name);
        }
        if (file_exists($file_path)) {
            include_once $file_path;
        } else {
            throw new BaseException(sprintf('The %s is not exists!', $file_path));
        }
    }
    
    function reset () {
    
    }
}