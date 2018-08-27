<?php
return array(
	//'配置项'=>'配置值'
    'MODULE_ALLOW_LIST' => array ('Home'),
    'DEFAULT_MODULE' => 'Home',
    'DB_TYPE'   => 'mysql',     // 数据库类型
    'DB_HOST'   => '47.94.152.72', // 服务器地址
    'DB_NAME'   => 'album',        // 数据库名
    'DB_USER'   => 'root',      // 用户名
    'DB_PWD'    => '123456',    // 密码
    //local
//    'DB_HOST'   => '192.168.11.10', // 服务器地址
//    'DB_NAME'   => 'album',        // 数据库名
//    'DB_USER'   => 'homestead',      // 用户名
//    'DB_PWD'    => 'secret',    // 密码

    'DB_PORT'   => 3306,        // 端口
    //'DB_PREFIX' => 'wm_tb_',            // 数据库表前缀
    'DB_CHARSET'=> 'utf8',
    'DATA_CACHE_TYPE'  => "Redis",
    'REDIS_HOST'=>"127.0.0.1",
    'REDIS_PORT'=>"6379",
    'REDIS_AUTH'=>'',
);