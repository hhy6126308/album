<?php
return array(
    //'配置项'=>'配置值'
    'URL_MODEL'     =>0,
    'MODULE_ALLOW_LIST' => array ('Home'),
    'DEFAULT_MODULE' => 'Home',
    'USER_SALT' =>'4%&&*',
    'LOG_TYPE' => 'File',
    'LOG_PATH' => APP_PATH.'Runtime/Logs/Common/api/',
//local
//    'SavePicUrl' => "http://image.album.com",//图片路径
//    'SavePicPath' => "/home/vagrant/storage/image",//图片目录
//    'SaveTempPath' => "/home/vagrant/storage/temp",//临时文件目录
    'SavePicUrl' => "https://image.album.iqikj.com",//图片路径
    'SavePicPath' => "/data/storage/image",//图片目录
    'SaveTempPath' => "/data/storage/temp",//临时文件目录
);