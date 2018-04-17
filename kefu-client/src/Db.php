<?php
namespace Kefu\Lib;
use Medoo\Medoo;
class Db
{
    protected static $instance = null;
    public static function getMysqlInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new Medoo([
                // required
                'database_type' => 'mysql',
                'database_name' => CC('DB_NAME'),
                'server' => CC('DB_HOST'),
                'username' => CC('DB_USER'),
                'password' => CC('DB_PWD'),

                // [optional]
                'charset' => 'utf8',
                'port' => CC('DB_PORT'),

                // [optional] Table prefix
                'prefix' => CC('DB_PREFIX'),

                // [optional] Enable logging (Logging is disabled by default for better performance)
                'logging' => false
            ]);
        }
        return self::$instance;
    }
}

