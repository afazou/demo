<?php
return array(
    'HOST' => 'http://kf.wx.gome.com.cn/index.php?s=',
    'GET_TOKEN_URL' =>'http://10.58.47.203/live/service/GetWeixinToken.ashx?isforce=1',             // 刷新access_token接口
    'GET_PLUS_TOKEN_URL' =>'http://10.58.47.203/live/serviceplus/GetWeixinToken.ashx?isforce=1',    // 刷新gomeplus access_token接口

    // 日志
    'LOG_SWITCH' => true,
    'LOG_PATH' => '/app/phplog/',

    // SESSION 和 COOKIE 配置
    'GC_MAXLIFETIME' => '7200',             // session过期
    'SESSION_PREFIX' => 'wxkefu_client',    // session前缀
    'COOKIE_PREFIX'  => 'wxkefu_client_',   // Cookie前缀 避免冲突
    'COOKIE_DOMAIN' => '.gome.com.cn',

    // 数据缓存设置
    'REDIS_HOST' => '10.58.47.91',
    'REDIS_PORT' => '6693',
    'DATA_CACHE_TIME' =>  7200,             // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_PREFIX' =>  '',             // 缓存前缀

    // 数据库链接配置
    'DB_HOST' => '10.58.12.174',            // 服务器地址
    'DB_NAME' => 'weikefu',                 // 数据库名
    'DB_USER' => 'user_weikefu',            // 用户名
    'DB_PWD'  => 'gHbsnSKmBh3SH',           // 密码
    'DB_PORT' => '7308',                    // 端口
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
    'WEIXIN_FILE_LOCAL' => '/app/weixin_img/kefu/media/',

);
