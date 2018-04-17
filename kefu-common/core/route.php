<?php
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: route.php 2013-11-14 14:17:22Z lilixing $
 */
(!DEBUG) ? (@ini_set("display_errors", 'Off')) : (@ini_set("display_errors", 'Off'));
error_reporting(E_ALL ^ E_NOTICE);
@set_time_limit(30);
ini_set('default_socket_timeout', -1);
define('IN_GOME_WEIXIN_SERVICE', true);
define('INCLUDE_PATH', dirname(__FILE__));

require INCLUDE_PATH . '/controller.php';
require INCLUDE_PATH . '/model.php';
require INCLUDE_PATH . '/function.php';
require INCLUDE_PATH . '/input.php';
require INCLUDE_PATH . '/config.php';
require INCLUDE_PATH . '/template.php';
// require INCLUDE_PATH . '/db.php';
require INCLUDE_PATH . '/../controller/ctl.base.php';
//require ROOT_PATH . '/controller/ctl.api.php';
require ROOT_PATH . '/config/config.php';
require INCLUDE_PATH . '/../lib/redis.class.php';
require INCLUDE_PATH . '/../lib/wechat.class.php';
//require INCLUDE_PATH . '/../lib/Weixinchat.class.php';
require INCLUDE_PATH . '/../lib/Http.class.php';
require INCLUDE_PATH . '/weixin.php';

class Route {
    public static function dispatcher() {
        $mod = $_GET['ctl'] ? $_GET['ctl'] : 'index';
        $act = $_GET['act'] ? $_GET['act'] : 'index';

        $controller = self::getController($mod);

        if (!is_object($controller)) {
            self::responseCode(404);
            exit();
        }

        if (!self::callAction($controller, $act, $_GET['p'])) {
            self::responseCode(404);
            exit();
        }
    }

    /**
     * getController
     *
     * @param    mixed $mod
     * @param    mixed $args
     * @access    private
     * @return    object
     */
    private static function getController($mod, $args = null) {

        $base_name = basename($mod, $args);
        $dir_name = dirname($mod);
        $file = ROOT_PATH . "/controller/" . $dir_name . "/ctl." . $base_name . ".php";
		

		if(!file_exists($file))
		{
			$file = INCLUDE_PATH . "/../controller/" . $dir_name . "/ctl." . $base_name . ".php";
		}
		
		$mod_name = "ctl_" . $base_name;
		$loaded = require realpath($file);
	
        if (!$loaded) {
            return false;
        }
        $object = new $mod_name($this);
        $object->controller = $mod;

        return $object;
    }

    /**
     * callAction
     *
     * @param    mixed $obj_ctl
     * @param    mixed $act_method
     * @param    mixed $args
     * @access    private
     * @return    void
     */
    private static function callAction($obj_ctl, $act_method, $args = null) {
        if ($act_method{
            0} !== '_' && method_exists($obj_ctl, $act_method)) {
            if (isset($args[0])) {
                call_user_func_array(array($obj_ctl, $act_method), $args);
            } else {
                $obj_ctl->$act_method();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * responseCode
     *
     * @param    int $code
     * @access    private
     * @return    void
     */
    private static function responseCode($code) {
        $codeArr = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
        );
        header('HTTP/1.1 ' . $code . ' ' . $codeArr[$code], true, $code);
    }
}