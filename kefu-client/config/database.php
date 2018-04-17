<?php
defined('ROOT_PATH') || die('Access denied!');
return array(
    array(
        'DB_HOST' => CC('DB_HOST'),
        'DB_NAME' => CC('DB_NAME'),
        'DB_USER' => CC('DB_USER'),
        'DB_PWD' => CC('DB_PWD'),
        'DB_PORT' => CC('DB_PORT'),
    	'DB_PREFIX' => CC('DB_PREFIX'),
    ),
);