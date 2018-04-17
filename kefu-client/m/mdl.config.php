<?php
class mdl_config extends mdl_common
{

    public function __construct()
	{
		parent::__construct();
		$this->table = 'wewin8_config';
	}

    /**
     * 获取数据库中的配置列表
     *
     * @return array
     */
	public function lists($fields = 'type,name,value', $where = 'status=1')
    {
        $data = $this->db_dao->select($this->table, explode(',', $fields), $where);
        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
                $config[$value['name']] = $this->parse($value['type'], $value['value']);
            }
        }
        return $config;
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    public function parse($type, $value){
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }

    /**
     * 读取配置项
     *
     * @return array
     */
    public function readConfig()
    {
        $config = $this->getConfigCache($this->getConfigCacheKey());
        if (empty($config)) {
            $config = $this->lists();
            $this->setConfigCache($this->getConfigCacheKey(), $config);
        }
        return $config;
    }

    /**
     * 设置配置项缓存
     *
     * @param $key
     * @param $config
     */
    protected function setConfigCache($key, $config)
    {
        self::$cacheHandle->setCache($key, $config);
    }

    /**
     * 获取配置项缓存KEY
     *
     * @return string
     */
    protected function getConfigCacheKey()
    {
        return 'DB_CONFIG_DATA';
    }

    /**
     * 读取配置项缓存
     *
     * @param $key
     * @return mixed
     */
    protected function getConfigCache($key)
    {
        $data = self::$cacheHandle->getCache($key);
        return $data;
    }


}
