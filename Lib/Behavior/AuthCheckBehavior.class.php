<?php

class AuthCheckBehavior extends Behavior {
	// 行为参数定义
	

	// 行为扩展的执行入口必须是run
	public function run(&$return) {
		// 进行权限认证逻辑 如果认证通过 $return = true;
		// 否则用halt输出错误信息
		if (! session ( 'mp_authId' )) {
			redirect ( '?s=login' );
		}else{
			ini_get('session.gc_maxlifetime',3600);
			define("USERNAME", session('user'));
			define("If_manager", session('is_admin'));
			if(If_manager!=1){
				$right	= session("right_action");
				$right	= unserialize($right);
				$QUERY_STRING	= $_SERVER['QUERY_STRING']?$_SERVER['QUERY_STRING']:$_SERVER['REQUEST_URI'];
				preg_match("/s=([^&]+)&a=([^&]+)/i", $QUERY_STRING,$uri);
// 				$s	= $_GET["_URL_"][0];
				$uri[2]	= $uri[2]?$uri[2]:"index";
				$s	= $_GET["_URL_"][0]."_".$uri[2];
// 				var_dump($right);
// 				$a	= $uri[2];
				if(
						$s!="index" && 
						$s!="index_index" && 
						$s!="index_header" && 
						$s!="index_menu" &&
						$s!="index_welcome" &&
						$s!="message" && 
						!in_array($s, $right)){
					if(in_array('Member_index', $right) && $_GET["_URL_"][0]!=='Member'){
						echo "no right";die;
						//message("no right!! please connect to administrator","./?s=index&a=welcome");
					}
				}
			}
		}
	}
}
