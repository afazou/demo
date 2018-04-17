<?php
// timezone
date_default_timezone_set('PRC');

// session
ini_set('session.save_handler', 'redis');
ini_set('session.cookie_domain', CC('COOKIE_DOMAIN'));
ini_set('session.gc_maxlifetime', CC('GC_MAXLIFETIME'));
ini_set('session.save_path', sprintf('tcp://%s:%s', CC('REDIS_HOST'), CC('REDIS_PORT')));

//@ Log configure
define('LOG_SWITCH', CC('LOG_SWITCH'));
define('LOG_PATH', CC('LOG_PATH'));

define('NOW_TIME', $_SERVER['REQUEST_TIME']);

defined('TEMPLATE_PATH') or define('TEMPLATE_PATH', APPLICATION_PATH.'/view/default/');
defined('TEMPLATE_COMPILE_PATH') or define('TEMPLATE_COMPILE_PATH', APPLICATION_PATH.'/data/compile/');

define('WEIXIN_FILE_LOCAL', CC('WEIXIN_FILE_LOCAL'));
