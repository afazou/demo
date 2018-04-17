<?php
date_default_timezone_set('Asia/Shanghai');
define('ROOT_PATH', dirname(__FILE__));
define('APPLICATION_PATH', ROOT_PATH);
// 开发环境
define('APP_ENV', empty($_SERVER['KEFU_CLIENT_ENV']) ? 'LIVE' : $_SERVER['KEFU_CLIENT_ENV']);
// 是否调试模式
define('DEBUG', false);
// 初始化配置
require APPLICATION_PATH . '/common/function.php';
CC(require APPLICATION_PATH . '/config/convention_'.strtolower(APP_ENV).'.php');
// 载入核心文件
require APPLICATION_PATH . '/vendor/autoload.php';
require APPLICATION_PATH. '/../kefu-common/core/route.php';
require APPLICATION_PATH . '/m/mdl.common.php';
require APPLICATION_PATH . '/controller/ctl.common.php';
// 解析URL
appParseUrl();
// 启动session
session_start();
Route::dispatcher();
