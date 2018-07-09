<?php

class LoginAction extends Action {
	
	Public function _initialize(){
		if($this->checkip()){
			session("Safetyip",1);
			$this->assign('Safetyip',1);
		}else{
			session("Safetyip",0);
			$this->assign('Safetyip',0);
		}
// 		B('AuthCheck');
	}
	public function index() {
		$this->display ( "login" );
	}
	/**
	 * 登录界面
	 */
	public function login() {
		if ($this->isPost()) {
			$user_name = addslashes(trim($_POST['user_name']));
			$password = addslashes(trim($_POST['password']));
			$auth = new UserAuthModel();
			if($auth->auth($user_name, $password)){//登陆成功
				redirect("?s=index&a=index");
			}else{
				die($auth->getLastSQl());
				$this->assign ( "error", '用户名或密码错误！' );	
			}
		}
		$this->display ( "login" );
	}
	/**
	 * 跳转方法(独立与login模块)
	 * @param <type> $message
	 * @param <type> $url
	 * @param <type> $timeout     默认:2秒跳转
	 * @param <type> $stop_loop   1:停止跳走,   默认0:自动跳转
	 */
	public function message($message, $url = null, $timeout = 2, $stop_loop=0)
	{
		if ($url == null)
		{
			$url = $_SERVER['HTTP_REFERER'];
		}
		$ac	= new LoginAction();
		$ac->assign('stop_loop', $stop_loop);
		$ac->assign('url_page', $url);
		$ac->assign('message', $message);
		$ac->assign('timeout', $timeout);
		$ac->display('Login:message');
		exit();
	}
	/**
	 * 退出
	 */
	public function logout()
	{
		session('authId',null);
		session('user',null);
		session('is_admin',null);
		session('right_action',null);
		header('location:?s=login');
	}
	/**
	 * 验证码
	 */
	public function verify() {
		import ( 'ORG.Util.Image_sae' );
		Image::buildImageVerify(5, 1, $type='png', 70, 26);
	}
	
	public function checkip(){
// 		//关闭帐号密码登录
 		return true;
		$ip = get_client_ip(); //得到IP
		$ALLOWED_IP=array('124.207.107.221','127.0.0.1','192.168.1.*');//安全IP
		$check_ip_arr= explode('.',$ip);//要检测的ip拆分成数组
		#限制IP
		if(!in_array($ip,$ALLOWED_IP)) {
			$bl=true;
			foreach ($ALLOWED_IP as $val){
				if(strpos($val,'*')!==false){//发现有*号替代符
					$arr=array();//
					$arr=explode('.', $val);
					//$bl=true;//用于记录循环检测中是否有匹配成功的
					for($i=0;$i<4;$i++){
						if($arr[$i]!='*'){//不等于*  就要进来检测，如果为*符号替代符就不检查
							if($arr[$i]!=$check_ip_arr[$i]){
								$bl=false;
								break;//终止检查本个ip 继续检查下一个ip
							}
						}
					}//end for
					if($bl){//如果是true则找到有一个匹配成功的就返回
						break;
					}
				}
			}//end foreach
			return $bl;
		}else{
			return true;
		}
	}
	
	public function phone_login(){
		if ($this->isPost()) {
			$phone_num = addslashes(trim($_POST['phone_num']));
			$capa = addslashes(trim($_POST['capa']));
			$memcache = new Memcache;
			$memcache->pconnect(C('MEMCACHE_HOST'), C('MEMCACHE_PORT'));
			if($phone_num){//登陆成功
				if($capa){
					$ac = "admin_login";
					if($capa==$memcache->get($ac."_".$phone_num)){
						$auth = new UserAuthModel();
						if($auth->phone_auth($phone_num)){
							$memcache->set($ac."_".$phone_num,null);
							redirect("?s=index&a=index");
						}else{
							$this->assign ( "error", '验证码错误！' );
						}
					}else{
						$this->assign ( "error", '验证码错误！' );
					}
				}else{
					$this->assign ( "error", '请输入正确的验证码！' );
				}
				//
			}else{
				$this->assign ( "error", '请输入正确的手机号！' );	
			}
		}
		$this->display ( "login" );
	}
	
	public function phoneverify(){
		$rs = array('error'=>65533,"msg"=>"未知错误！");
		$phone = $_GET['phone'];
 		$ac = "admin_login";
		if(!$phone) die("phone error");
		$a = range(0,9);
		for($i=0;$i<6;$i++){
			$b[] = array_rand($a);
		}
		$phonecode = join("",$b);
		$memcache = new Memcache;
		$memcache->pconnect(C('MEMCACHE_HOST'), C('MEMCACHE_PORT'));
	
		$ip = get_client_ip();
		$date = Date('Ymd');
	
		if($memcache->get("PHONE_LOGIN_ADMIN_".$ip."_SENDTIME".$date)>30){
			//ip发送短信超过次数
			$rs['error'] = 1;
			$rs['msg'] = "系统繁忙！";
		}else{
			if(!$memcache->get("PHONE_LOGIN_ADMIN_".$ip."_SENDTIME".$date)){
				$memcache->set("PHONE_LOGIN_ADMIN_".$ip."_SENDTIME".$date,1);
			}else{
				$memcache->increment("PHONE_LOGIN_ADMIN_".$ip."_SENDTIME".$date, 1);
			}
			if($memcache ->set($ac."_".$phone,$phonecode)){
				$accountM = new UserAuthModel();
				$where = "phone = '$phone'";
				if($accountM->getuser($where)){
					//发送成功
					$M = new SendSmsModel();
					$smsres = $M->send("您的激活验证码为".$phonecode."，请及时激活",$phone);
					if($smsres['error']===0){
						$rs['error'] = 0;
						$rs['msg'] = "短信发送成功！";
					}else{
						$rs['error'] = 1;
						$rs['msg'] = "短信发送失败！";
					}
				}else{
					//已经存在用户
					$rs['error'] = 3;
					$rs['msg'] = "手机不存在！";
				}
			}else{
				//插入memecache错误
				$rs['error'] = 3;
				$rs['msg'] = "系统繁忙！";
			}
		}
		echo json_encode($rs);
	}
	
	public function sms_verify(){
		$phone = trim($_GET['phone']);
		if($phone){
			$auth = new UserAuthModel();
			$res = $auth->get_phone_type($phone);
			if($res){
				$this->send_sms_verify($phone, $res['phonetype']);
				echo json_encode(array("error"=>0,"msg"=>"success"));
			}else{
				echo json_encode(array("error"=>1,"msg"=>"手机号不正确"));
			}
		}else{
			echo json_encode(array("error"=>1,"msg"=>"请输入手机号"));
		}
	}
}