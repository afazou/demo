<?php

/**
 * Class mdl_wx_api
 *
 * @author <renfajing@yolo24.com>
 */
class mdl_wx_api
{
    public function __call($name, $arguments)
    {
        try {
            $params = $arguments;
            $wxApiClass = new ReflectionClass('Wechat');
            $wxApi = $wxApiClass->newInstanceArgs(array_shift($arguments));

            $data =  call_user_func_array(array($wxApi, $name), array_shift($arguments));
            appLog($name, array('params' => $params, 'response' => $data));
            return $data;
        } catch (Exception $e) {
            return isset($data) ? $data : false;
        }
    }

}


