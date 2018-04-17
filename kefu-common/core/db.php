<?php
/**
 * mysql 数据库操作类
 *
 */

class MySql {

    private $debug = false; // same as $trace
    private $num_queries = 0; //操作次数
    private $queries = array();
    private $last_query = null; //最后一个sql
    private $last_error = null; //最后一个错误信息
    private $insertId;
    // 事务指令数
    private $transTimes = 0;

    private $link;
    protected $host;
    private $user;
    private $password;
    protected $dbname;
    private $port;
    Private $charset = 'utf8';
    protected $lastQueryTime;

    private $config;
    private static $instances = array();
/**
 *实例化Mysql
 **/
    public static function get_instance($db = '', $config = null) {
        $key = $db ? $db : 0;
        if (empty(self::$instances[$key])) {
            self::$instances[$key] = new MySql($key, $config);
        }
        return self::$instances[$key];
    }

    public function __construct($db, $config = array(), $connect = false, $charset = 'utf8') {
        $this->config = $config ? $config : Config::get('database');
        $this->host = $this->config[$db]['DB_HOST'];
        $this->user = $this->config[$db]['DB_USER'];
        $this->password = $this->config[$db]['DB_PWD'];
        $this->dbname = $this->config[$db]['DB_NAME'];
        $this->port = $this->config[$db]['DB_PORT'] ? $this->config[$db]['DB_PORT'] : '3306';
        $this->charset = $charset;
        if (!$connect) {
            $this->connect();
        }
    }

    //链接数据库
    public function connect() {
        if (!function_exists('mysql_connect')) {
            $this->error("undefined function mysql_connect(), 请加载php_mysql模块");
            return false;
        }
        if ($this->link && mysql_ping($this->link)) {
            return true;
        }
        if ($this->link) {
            mysql_close($this->link);
        }
        $startT = microtime(true);
        if (!$this->link = mysql_connect($this->host . ':' . $this->port, $this->user, $this->password, true)) {
            $this->lastQueryTime = sprintf("%.4f", (microtime(true) - $startT) * 1000);
            $this->error("connect error！ " . mysql_error(), array($this->host, $this->user, $this->password));
            return false;
        }
        mysql_query("set names " . $this->charset, $this->link);
        $this->lastQueryTime = (microtime(true) - $startT) * 1000;
        $this->logger('connect db');
        if ($this->dbname) {
            $this->selectDb();
        }
        return true;
    }

    //选择库
    public function selectDb($dbname = null) {
        if ($dbname) {
            $this->dbname = $dbname;
        }
        if (!$this->link) {
            return;
        }

        if (!@mysql_select_db($this->dbname, $this->link)) {
            $this->error("select db error : " . mysql_error());
            return;
        }
        mysql_query("set names " . $this->charset, $this->link);
    }

    //按sql获取所有
    public function getAll($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_assoc($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return null;
        }
    }

    //按sql获取一条数据
    public function getRow($sql, $limited = false) {
        if ($limited == true) {
            $sql = trim($sql . ' LIMIT 1');
        }
        $res = $this->query($sql);
        if ($res !== false) {
            return mysql_fetch_assoc($res);
        } else {
            return null;
        }
    }

    //按sql获取第一个字段数据列表
    public function getCol($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_row($res)) {
                $arr[] = $row[0];
            }
            return $arr;
        } else {
            return null;
        }
    }

    //按sql获取第一条数据的第一列到值
    public function getOne($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            $row = mysql_fetch_row($res);
            return $row[0];
        } else {
            return null;
        }
    }

    /**
     * 启动事务
     * @return void
     */
    public function startTrans() {
        if (!$this->link || $this->transTimes <= 0) {
            $conn = $this->connect();
        } else {
            $conn = mysql_ping($this->link);
        }

        if (!$conn) {
            $this->error('开启事务时数据库链接异常');
        }
        //数据rollback 支持
        if ($this->transTimes == 0) {
            $tran = mysql_query('START TRANSACTION', $this->link);
            if (!$tran) {
                $this->error('开启事务异常: ' . mysql_error($this->link));
            }
        }
        $this->transTimes++;
        return;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @return boolen
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = mysql_query('COMMIT', $this->link);
            $this->transTimes = 0;
            if (!$result) {
                $this->error("数据库事务提交失败！ " . mysql_error());
                return false;
            }
        }
        return true;
    }

    /**
     * 事务回滚
     * @return boolen
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = mysql_query('ROLLBACK', $this->link);
            $this->transTimes = 0;
            if (!$result) {
                $this->error("数据库事务回滚失败！ " . mysql_error());
                return false;
            }
        }
        return true;
    }

    /**
     * SELECT
     */
    public function select($tables, $fields = array(), $where = '', $limit = '', $order = '') {
        $fields = empty($fields) ? '*' : implode(',', $fields);
        $sql = "SELECT $fields FROM {$tables} ";
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        if (!empty($order)) {
            $sql .= ' ORDER BY ' . $order;
        }
        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }
        return $this->getAll($sql);
    }

    /**
     * 插入数据
     * @param string $table
     * @param array $set
     * @param boolean $replace 是否replace
     */
    public function insert($table, $set = array(), $replace = false) {
        $fields = array();
        $values = array();
        foreach ($set as $key => $value) {
            $fields[] = "`{$key}`";
            $values[] = $this->parseValue($value);
        }
        $sql = ($replace ? 'REPLACE' : 'INSERT') . " INTO `{$table}` " . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
        if ($this->query($sql)) {
            $this->insertId = mysql_insert_id($this->link);
            return $this->insertId;
        }
        return false;
    }

    //更新数据
    public function update($table, $where, $set = array(), $limit = null) {
        foreach ($set as $key => $value) {
            $sets[] = "`{$key}` = " . $this->parseValue($value);
        }
        $sql = "UPDATE `{$table}` SET " . implode(',', $sets) . '  WHERE  ' . $where;
        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }
        if (!$this->query($sql)) {
            return false;
        }
        return true;
    }

    //delete
    public function delete($table, $where) {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        return $this->query($sql);
    }

    /**
     * 执行sql
     * @param string $sql
     */
    public function query($sql) {
        $this->last_query = $sql;
        if (!$this->link || !mysql_ping($this->link)) {
            $this->connect();
        }
        $startT = microtime(true);
        $this->num_queries++;
        $result = @mysql_query($sql, $this->link);
        $this->lastQueryTime = sprintf("%.4f", (microtime(true) - $startT) * 1000);

        $this->logger($sql);
        if (!$result) {
            $this->error('query error : ' . mysql_error($this->link));
        }

        return $result;
    }

    //insert id
    public function insertId() {
        $this->insertId = mysql_insert_id($this->link);
        return $this->insertId;
    }

    //影响结果集数
    public function affectedRows() {
        return mysql_affected_rows($this->link);
    }

    /**
     *
     * @param mixed $value
     */
    public function parseValue($value) {
        if (is_string($value)) {
            $value = '\'' . $this->escape($value) . '\'';
        } elseif (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = $this->escape($value[1]);
        } elseif (is_array($value)) {
            $value = array_map(array($this, 'parseValue'), $value);
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }

    /**
     * 转义字符
     * @param string $str
     * @return string
     */
    public function escape($str) {
        return @mysql_real_escape_string($str);
    }

    /**
     * 根据数组获取sql中in条件sql
     * @param array $arr
     * @param boolean $isString
     * @return string
     */
    public function genSqlInStr($arr, $isString = true) {
        $str = "";
        if (empty($arr) || !is_array($arr)) {
            return '';
        }

        $k = 0;
        foreach ($arr as $value) {
            if ($k != 0) {
                $str .= " , ";
            }

            if ($isString) {
                $str .= "'" . $this->escape($value) . "'";
            } else {
                $str .= intval($value);
            }
            $k++;
        }
        return $str;
    }

    /**
    +----------------------------------------------------------
     * 获取最近一次查询的sql语句
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @return string
    +----------------------------------------------------------
     */
    public function getLastSql() {
        return $this->last_query;
    }

    public function getLastError() {
        return $this->last_error;
    }

    /**
     * 系统时间
     */
    public function sysdate() {
        return 'NOW()';
    }

    /**
     * 添加error信息
     * @param string $error
     */
    public function error($error) {
        $this->last_error = $error;
        $this->logger($error . "\n  " . 'sql : ' . $this->last_query, 'error');
        //throw new MysqlException($error);
    }

    //close
    public function close() {
        mysql_close($this->link);
    }
    //日志
    public function logger($str, $type = 'sql') {
        if ($type == 'error') {
            file_put_contents(LOG_PATH . 'mysql_error.log', date('Y-m-d H:i:s').'   '.$str . "\n", FILE_APPEND);
        }
    }

    public function __destruct() {
        @mysql_close($this->link);
    }
}

class MysqlException extends Exception {

}
