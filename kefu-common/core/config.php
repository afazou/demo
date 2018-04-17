<?php defined('ROOT_PATH') || die('Access denied!');
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config.php 2013-12-24 10:45:22Z lilixing $
 */

date_default_timezone_set('Asia/Shanghai');

class Config
{
    private static $config = array();
    private static $instance;

    public static function initialization()
    {
        $path = ROOT_PATH . '/config/';
        $dir  = dir($path);
        while (false !== ($entry = $dir->read())) 
        {
            if(is_file($path . '/' .$entry))
            {
                self::$config[substr($entry, 0, -4)] = include($path . '/' .$entry);
            }
        }
        $dir->close();
    }

    public static function get($name , $default = null)
    {
        if(isset(self::$config[$name]))
        {
            return self::$config[$name];
        }
        elseif (strpos($name, '.') !== false)
        {
            $ks = explode('.' ,  $name);
            $t = null;
            foreach($ks as $k)
            {
                if(is_null($t) && isset(self::$config[$k]))
                {
                    $t = self::$config[$k];
                }
                else if(isset($t[$k]))
                {
                    $t = $t[$k];
                }
                else
                {
                    return $default;
                }
            }

            return $t ? $t : $default;
        }
        elseif(file_exists(ROOT_PATH . '/config/' . $name . '.php'))
        {
            return include(ROOT_PATH . '/config/' . $name . '.php');
        }

        return $default;
    }

    public static function set($name , $value)
    {
        self::$config[$name] = $value;
    }
}
