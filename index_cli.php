<?php
/***
 * crontab 入口文件
 * 
 */
//线上环境请注意关闭APP_DEBUG为true
define("APP_DEBUG", true);
define('APP_NAME','admin');
define('APP_PATH',dirname(__FILE__));
define('MODE_NAME', 'cli');
define("PUBLIC_LIB", dirname(dirname(__FILE__)).('/lib/'));
require PUBLIC_LIB.'/ThinkPHP/ThinkPHP.php';
// require PUBLIC_LIB.'/ThinkPHP/Extend/Engine/Sae.php';
?>
