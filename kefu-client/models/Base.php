<?php
namespace Kefu\Model;
class Base
{
    protected $table = null;
    public function __construct()
	{
	}
    public function __call($name, $arguments)
    {
        try {
            $db = \Kefu\Lib\Db::getMysqlInstance();
            $refMethod = new \ReflectionMethod($db, $name);
            $parameters = $refMethod->getParameters();
            $parameters = array_shift($parameters);
            if (isset($parameters->name) && 'table' == $parameters->name) {
                array_unshift($arguments, $this->table);
            }
            $data = call_user_func_array([$db, $name], $arguments);
            return $data;
        } catch (\Exception $e) {

        }
    }

}
