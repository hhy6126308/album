<?php

class MessageAction extends Action {
	Public function _initialize(){
		B('AuthCheck');
	}
    public function index(){
    	$url		= trim($_GET['url']);
    	$message	= trim($_GET['msg']);
    	$stop_loop	= trim($_GET['loop'])?trim($_GET['loop']):0;
    	$timeout	= trim($_GET['timeout'])?trim($_GET['timeout']):2;
    	if ($url == null || empty($url))
      	{
      		$url = $_SERVER['HTTP_REFERER'];
      	}
      	$ac	= $this;
      	$ac->assign('stop_loop', $stop_loop);
      	$ac->assign('url_page', $url);
      	$ac->assign('message', $message);
      	$ac->assign('timeout', $timeout);
      	$ac->display('Login:message');
      	exit();
	}
}