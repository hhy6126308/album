<?php
return array(
	'URL_MODEL'     =>0,
	'IS_PRO'   => false, 	// 是否正式环境
	/*数据库相关配置*/
	'DB_TYPE'   => 'mysql', 	// 数据库类型
	'DB_HOST'   => '127.0.0.1', // 服务器地址
	'DB_NAME'   => 'album', 		// 数据库名
	'DB_USER'   => 'root', 		// 用户名
	'DB_PWD'    => '123456', 	// 密码
	'DB_PORT'   => 3306, 		// 端口
	'DB_PREFIX' => 'tb_', 			// 数据库表前缀
	'DB_CHARSET'=> 'utf8',
	//数据库配置1
	'DB_CONFIG1' => array(
			'db_type'  => 'mysql',
			'db_user'  => 'root',
			'db_pwd'   => '123456',
			'db_host'  => '127.0.0.1',
			'db_port'  => '3306',
			'db_name'  => 'album'
	),

	/*缓存相关*/
	'DB_CACHE'         => false,//cache开关
	/*redis*/
	'DATA_CACHE_TYPE'  => "Redis",
	'REDIS_HOST'=>"127.0.0.1",
	'REDIS_PORT'=>"6379",
	'REDIS_AUTH'=>'',

	'MEMCACHE_HOST'    => '127.0.0.1',
	'MEMCACHE_PORT'    => '11211',
	'MEMCACHE_API_HOST'=>"192.168.5.81",
	'MEMCACHE_API_PORT'=>"11888",
	/*其它相关配置*/
	'URL_CASE_INSENSITIVE'	=>true,//忽略大小写
	'TMPL_ENGINE_TYPE' =>'Smarty',
	'TMPL_ENGINE_CONFIG' => array(
				'debugging'=>false,
				//    'error_reporting'=>'',
				//    'exception_handler'=>array('ExceptionClass','ExceptionMethod'),
				'template_dir' 	=> APP_PATH.'Tpl/',  //模板目录
				'compile_dir' 	=>SmartyCompile,//编译目录
				'cache_dir' 	=>SmartyCatch,  //缓存目录
				'caching' 		=> false,  //是否启用缓存
				'compile_locking'	=> SmartyLock,//防止调用touch,saemc会自动更新时间，不需要touch
				'cache_lifetime' =>1,//缓存时间s
				'left_delimiter'=>'<{',
				'right_delimiter' =>'}>',
		),
	'TMPL_ENGINE_CONFIG_SAE' => array(
				'debugging'=>true,
				//    'error_reporting'=>'',
				//    'exception_handler'=>array('ExceptionClass','ExceptionMethod'),
				'template_dir' 	=> APP_PATH.'Tpl/',  //模板目录
// 				'compile_dir' 	=>APP_PATH.'Runtime/Temp/',//编译目录
// 				'cache_dir' 	=>APP_PATH.'Runtime/Cache/',  //缓存目录
				'compile_dir' 	=>'saemc://smartytpl/',//编译目录
				'cache_dir' 	=>'saemc://smartytpl/',  //缓存目录
				'compile_locking' 	=>false,  //防止调用touch,saemc会自动更新时间，不需要touch
				'caching' 		=> false,  //是否启用缓存
				'cache_lifetime' =>1,//缓存时间s
				'left_delimiter'=>'<{',
				'right_delimiter' =>'}>',
		),
	'USER_SALT' =>'4%&&*',
// 	'TMPL_TEMPLATE_SUFFIX'=>'.tpl',
	'SavePicUrl'			=>"http://127.0.0.1:8071/",//图片路径
	'SavePicPath'			=>"E:/zhandian/shouyoushe/api/wwwroot/st/pics/",//图片路径
);

?>