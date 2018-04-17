<?php
class mdl_common extends Model
{
    protected $table;
    protected static $cacheHandle;
    public function __construct()
	{
		parent::__construct();
		if (!self::$cacheHandle) {
            self::$cacheHandle = self::loadModel('cache');
        }
	}

    /**
     * 获取一条数据
     *
     * @param $where
     * @param string $fields
     * @param null $order
     * @return mixed
     */
    public function find($where, $fields = '*', $order = null)
    {
        $fields = empty($fields) ? '*' : $fields;
        $sql = sprintf("SELECT %s FROM `%s` WHERE %s", $fields, $this->table, $this->getWhere($where));
        if ($order) {
            $sql = sprintf("SELECT %s FROM `%s` WHERE %s ORDER BY %s", $fields, $this->table, $this->getWhere($where), $order);
        }
        $data = $this->db_dao->getRow($sql, true);
        return $data;
    }

    /**
     * 更新数据
     *
     * @param $where
     * @param array $set
     * @param null $limit
     * @return mixed
     */
    public function save($where, $set = array(), $limit = null)
    {
        return $this->db_dao->update($this->table, $this->getWhere($where), $set, $limit);
    }

    /**
     * 添加数据
     *
     * @param $set
     * @return mixed
     */
    public function add($set)
    {
        return $this->db_dao->insert($this->table, $set);
    }

    public function getWhere($where)
    {
        if (is_array($where) && $where) {
            $whereArr = array();
            array_walk($where, function ($value, $key) use (&$whereArr) {
                $operator = '=';
                if (is_array($value)) {
                    $operator = $value[0];
                    $value = $value[1];
                    if ('IN' == strtoupper($operator) && is_array($value)) {
                        array_push(
                            $whereArr,
                            sprintf("`%s` %s %s", $key, $operator, sprintf("('%s')", implode("','", $value)))
                        );
                    } else {
                        array_push(
                            $whereArr,
                            sprintf("`%s` %s '%s'", $key, $operator, $value)
                        );
                    }
                } else {
                    array_push(
                        $whereArr,
                        sprintf("`%s` %s '%s'", $key, $operator, $value)
                    );
                }

            });
            return implode(' AND ', $whereArr);
        }
        return $where;
    }

    /**
     * 统计条数
     *
     * @param $where
     * @return int
     */
    public function count($where)
    {
        $data = $this->db_dao->select($this->table, null, $this->getWhere($where));
        return count($data);
    }

    /**
     * 获取列表
     *
     * @param array $fields
     * @param string $where
     * @param string $limit
     * @param string $order
     * @return mixed
     */
    public function doSelect($fields = array(), $where = '', $limit = '', $order = '')
    {
        $data = $this->db_dao->select(sprintf('`%s`', $this->table), $fields, $this->getWhere($where), $limit, $order);
        return $data;
    }

    /**
     * 删除数据
     *
     * @param $where
     * @return mixed
     */
    public function del($where)
    {
        return $this->db_dao->delete($this->table, $this->getWhere($where));
    }

    /**
     * 获取SQl
     *
     * @return mixed
     */
    public function getSql()
    {
        return $this->db_dao->getLastSql();
    }


}
