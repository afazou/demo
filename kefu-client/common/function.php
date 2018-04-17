<?php
/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @return mixed
 */
function CC($name=null, $value=null) {
    static $_config = array();
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name));
        return;
    }
    return null; // 避免非法参数
}

/**
 * session管理函数
 * @param string|array $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function c_session($name, $value = '') {
    $prefix = CC('SESSION_PREFIX');
    if('' === $value){
        if(0===strpos($name,'?')){ // 检查session
            $name   =  substr($name,1);
            if(strpos($name,'.')){ // 支持数组
                list($name1,$name2) =   explode('.',$name);
                return $prefix?isset($_SESSION[$prefix][$name1][$name2]):isset($_SESSION[$name1][$name2]);
            }else{
                return $prefix?isset($_SESSION[$prefix][$name]):isset($_SESSION[$name]);
            }
        }elseif(is_null($name)){ // 清空session
            if($prefix) {
                unset($_SESSION[$prefix]);
            }else{
                $_SESSION = array();
            }
        }elseif($prefix){ // 获取session
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$prefix][$name1][$name2])?$_SESSION[$prefix][$name1][$name2]:null;
            }else{
                return isset($_SESSION[$prefix][$name])?$_SESSION[$prefix][$name]:null;
            }
        }else{
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$name1][$name2])?$_SESSION[$name1][$name2]:null;
            }else{
                return isset($_SESSION[$name])?$_SESSION[$name]:null;
            }
        }
    }elseif(is_null($value)){ // 删除session
        if($prefix){
            unset($_SESSION[$prefix][$name]);
        }else{
            unset($_SESSION[$name]);
        }
    }else{ // 设置session
        if($prefix){
            if (!is_array($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }
            $_SESSION[$prefix][$name]   =  $value;
        }else{
            $_SESSION[$name]  =  $value;
        }
    }


}

function getsession($key){
    $session = c_session('user_auth');
    return $session[$key];
}

/**
 * 日志
 *
 * @param $method
 * @param $message
 */
function appLog($method, $message)
{
    $trace = debug_backtrace();
    $trace = array_shift($trace);
    $only = getsession('only');
    LogFile(
        sprintf(
            "%s:%s\t%s\tonly:%s\tmessage:%s",
            basename($trace['file']),
            basename($trace['line']),
            $method,
            $only ? $only : null,
            is_array($message) ? json_encode($message) : $message
        )
    );
}

/**
 * 解析URL
 */
function appParseUrl()
{
    if (isset($_GET['s'])) {
        $_SERVER['PATH_INFO'] = $_GET['s'];
        unset($_GET['s']);
    }
    $_SERVER['PATH_INFO'] = preg_replace('/\.('.trim('html','.').')$/i', '', $_SERVER['PATH_INFO']);
    $paths = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    array_shift($paths);
    // 获取控制器
    $_GET['ctl']   =   array_shift($paths);
    // 获取操作
    $_GET['act']  =   array_shift($paths);
    // 解析剩余的URL参数
    $var  =  array();
    preg_replace_callback('/(\w+)\/([^\/]+)/', function($match) use(&$var){$var[$match[1]]=strip_tags($match[2]);}, implode('/',$paths));
    $_GET   =  array_merge($var, $_GET);
    // 保证$_REQUEST正常取值
    $_REQUEST = array_merge($_POST,$_GET);
}
