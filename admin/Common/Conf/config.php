<?php
return array(
	//'配置项'=>'配置值'
    'MODULE_ALLOW_LIST' => array ('Home'),
    'DEFAULT_MODULE' => 'Home',
    'DB_TYPE'   => 'mysql',     // 数据库类型
    'DB_HOST'   => '47.92.88.114', // 服务器地址
    'DB_NAME'   => 'album',        // 数据库名
    'DB_USER'   => 'root',      // 用户名
    'DB_PWD'    => '123456',    // 密码
    'DB_PORT'   => 3306,        // 端口
    //'DB_PREFIX' => 'wm_tb_',            // 数据库表前缀
    'DB_CHARSET'=> 'utf8',
    /*redis*/
    'REDIS_HOST'=>"127.0.0.1",
    'REDIS_PORT'=>"6379",
);