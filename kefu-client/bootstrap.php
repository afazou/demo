<?php
define('ROOT_PATH', dirname(__FILE__));
define('APPLICATION_PATH', ROOT_PATH);
// 开发环境
define('APP_ENV', 'uat');
// 是否调试模式
define('DEBUG', false);
// 初始化配置
require ROOT_PATH . '/common/function.php';
CC(require ROOT_PATH . '/config/convention_'.strtolower(APP_ENV).'.php');
// 载入核心文件
require APPLICATION_PATH . '/vendor/autoload.php';
require ROOT_PATH. '/../kefu-common/core/route.php';
require ROOT_PATH . '/m/mdl.common.php';
require ROOT_PATH . '/controller/ctl.common.php';

// 启动session
session_start();

