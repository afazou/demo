<?php
return array(
    'HOST' => 'http://kf.wxdev.com.cn/index.php?s=',
    'GET_TOKEN_URL' =>'http://10.58.47.203/live/service/GetWeixinToken.ashx?isforce=1',             // 刷新access_token接口
    'GET_PLUS_TOKEN_URL' =>'http://10.58.47.203/live/serviceplus/GetWeixinToken.ashx?isforce=1',    // 刷新gomeplus access_token接口

    // 日志
    'LOG_SWITCH' => true,
    'LOG_PATH' => 'E:\www\phplog',

    // SESSION 和 COOKIE 配置
    'GC_MAXLIFETIME' => '7200',             // session过期
    'SESSION_PREFIX' => 'wewin8_home',      // session前缀
    'COOKIE_PREFIX'  => 'wewin8_home_',     // Cookie前缀 避免冲突
    'COOKIE_DOMAIN' => '.wxdev.com.cn',

    // 数据缓存设置
    'REDIS_HOST' => '127.0.0.1',
    'REDIS_PORT' => '6379',
    'DATA_CACHE_TIME' =>  7200,             // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_PREFIX' =>  'wxdev:',       // 缓存前缀

    // 数据库链接配置
    'DB_HOST' => '127.0.0.1',               // 服务器地址
    'DB_NAME' => 'weikefu',                 // 数据库名
    'DB_USER' => 'root',                    // 用户名
    'DB_PWD'  => '111111',                  // 密码
    'DB_PORT' => '3306',                    // 端口
    'DB_PREFIX' => 'wewin8_',               // 数据库表前缀

    // 前端资源路径
    '__PUBLIC__' => '/public',
    '__STYLE__' => '/public/tmp/css',
    '__SCRIPT__' => '/public/tmp/scripts',
    '__IMAGES__' => '/public/tmp/img',
    '__PLUGINS__'=> '/public/tmp/plugins',
    '__IMG__' => '/public/client/images',
    '__CSS__' => '/public/client/css',
    '__JS__' => '/public/client/js',

    // 本地媒体文件储存
    'WEIXIN_FILE_LOCAL' => 'E:/www/Gwap/weixin-kefu/upload/',

);
