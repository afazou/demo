<?php
class mdl_cache extends Model
{
    /**
     * 设置缓存
     *
     * @param $key
     * @param $value
     * @param null $time
     * @return mixed
     */
    public function setCache($key, $value, $time = null)
    {
        return $this->cache_dao->set($this->getCacheKey($key), $this->getCacheValue($value), $this->getCacheTime($time));
    }

    /**
     * 获取缓存
     *
     * @param $key
     * @return mixed
     */
    public function getCache($key)
    {
        return $this->cache_dao->get($this->getCacheKey($key));
    }

    /**
     * 删除缓存
     *
     * @param $key
     * @return mixed
     */
    public function delCache($key)
    {
        return $this->cache_dao->delete($this->getCacheKey($key));
    }

    protected function getCacheKey($key)
    {
        return CC('DATA_CACHE_PREFIX') . $key;
    }

    protected function getCacheValue($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        return $value;
    }

    protected function getCacheTime($time = null)
    {
        return $time ? $time : CC('DATA_CACHE_TIME');
    }

}


