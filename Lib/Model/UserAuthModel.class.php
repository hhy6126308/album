<?php
class UserAuthModel extends Model {
	protected $trueTableName = 'iq_app_list';
	protected $connection = 'DB_CONFIG1';

	public function auth($username, $password) {
		$username = addslashes ( $username );
		$password = md5 ( 'iqkj' . addslashes ( $password ) );
		$rs = $this->field ( "id,pwd,appname" )->where ( "`appname`='{$username}'" )->find ();
		if ($rs && $rs ['pwd'] == $password) {
			$ip	= get_client_ip();
			$Model = new Model ();
			$Model->execute ( "update ".$this->trueTableName." set last_login='" . date ( "Y-m-d H:i:s" ) . "',lastip='$ip' where `id`=" . $rs ['id'] );
			
			session ( "user", $username );
			session ( "mp_authId", $rs ['id'] );
			session ( "is_admin", 1 );
			return true;
		} else {
			return false;
		}
	}
	
	public function getuser($where){
		if(!$where) return false;
		$sql = "select * from $this->trueTableName where $where";
		return $this->query($sql);
	}
	
	public function phone_auth($phone){
		$phone = addslashes ( $phone );
		$rs = $this->field ( "id,user_name,is_admin,right_action" )->where ( "`phone`='{$phone}'" )->find ();
		if ($rs) {
			$ip	= get_client_ip();
			$Model = new Model ();
			$Model->execute ( "update ".$this->trueTableName." set last_login='" . date ( "Y-m-d H:i:s" ) . "',lastip='$ip' where `id`=" . $rs ['id'] );
			session ( "user", $rs['user_name']);
			session ( "authId", $rs ['id'] );
			session ( "is_admin", $rs ['is_admin'] );
			session ( "right_action", $rs ['right_action'] );
			return true;
		} else {
			return false;
		}
	}
	
	
	public function user_add($user_name, $password, $email, $qq) {
		$Model = new Model (); // 实例化一个model对象 没有对应任何数据表
		$rs = $Model->execute ( "insert into ".$this->trueTableName."(`user_name`,`password`,`email`,`phone`) values('{$user_name}','{$password}','{$email}','{$qq}')" );
		return $rs;
	}
	public function modify_user($user_name, $password, $email, $qq,$r_password){
		$date	= "";
		if(!empty($r_password)){
			$date	= "password = '$r_password',";
		}
		$date .= "email = '$email',phone='$qq'";
		$Model = new Model (); // 实例化一个model对象 没有对应任何数据表
		$sql = "update ".$this->trueTableName." set $date where user_name='$user_name' and password='$password'";
		$rs = $Model->execute ($sql);
		return $rs;
	}
	
	public function findUserByName($username) {
		$rs = $this->field ( "id" )->where ( "`user_name`='{$username}'" )->find ();
		if ($rs && $rs ['id']) {
			return true;
		}
		return false;
	}
	public function getUserInfoById($uid){
		return $this->where("`id`=".$uid)->find();
	}
	public function get_total_num(){
		$rs	= $this->field("count(id) as c")->find();
		return $rs['c'];
	}
	public function member_list(){
		$userlistdb	= $this->field("user_name,is_admin,lastip,last_login,user_type")->order("is_admin desc")->select();
		return $userlistdb;
	}
	public function member_add($username,$pwd,$phone,$phonetype,$user_type){
		if(empty($username) || empty($pwd)) return false;
		$data['user_name']	= $username;
		$data['password']	= md5 ( C ( 'USER_SALT' ) . addslashes ( $pwd ) );
		$data['phone']	= $phone;
		$data['phonetype']	= $phonetype;
		$data['user_type']	= $user_type;
		$data['register_time']	= date("Y-m-d H:i:s");
		$data['lastip']		= get_client_ip();
		$rs	= $this->data($data)->add();
		return $rs;
	}
	/**
	 * 编辑信息
	 * @param <type> $id
	 */
	public function member_edit($name)
	{
		if(empty($name)) return false;
		return $this->where("user_name='$name'")->limit(1)->find();
	}
	/**
	 * 保存编辑信息
	 * @param <type> $name
	 * @param <type> $auth_info
	 */
	public function member_save($name,$password,$rightdb)
	{
		if(empty($name)) return false;
		$user = $this->where("user_name='$name' and is_admin=0")->limit(1)->find();
		if(!$user)
		{
			throw new Exception("没有这个用户.");//编辑超级管理员,也会提示改错误.
		}
		$sqladd='';
		if ($password!='')
		{
			if(strlen($password)<6)
			{
				throw new Exception("密码长度不够,最少6位.");
			}
			$S_key=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
			foreach($S_key as $value)
			{
				if (strpos($password,$value)!==false)
				{
					throw new Exception("密码不能包含特殊字符.");
				}
			}
			$password=md5 ( C ( 'USER_SALT' ) . addslashes ( $password ) );
			$data['password']	= $password;
// 			$sqladd.=", password='$password' ";
		}
		$data['right_action']	= $rightdb;
		$this->data($data)->where("user_name='$name'")->save();
		return true;
	}
	/**
	 * 修改密码
	 * @param <type> $name
	 * @param <type> $password
	 * @param <type> $type
	 * @return <type>  2:超级管理员
	 */
	public function member_password($name,$password,$type=1)
	{
		$sql_add='';
		if($type==1)$sql_add=" AND is_admin='0' ";
		if(USERNAME==$name)
		{
			$oldpassword=(empty($_POST['oldpassword']))?'':$_POST['oldpassword'];
			if(empty($oldpassword))
			{
				throw new Exception("请输入原始密码.");
			}
			$oldpassword=md5 ( C ( 'USER_SALT' ) . addslashes ( $oldpassword ) );
			$how	= $this->where("user_name='$name' and password='$oldpassword' ".$sql_add)->find();
			if(!$how)
			{
				throw new Exception("原始密码不正确.");
			}
			//修改自己密码
		}
		$how	= $this->where("user_name='$name' ".$sql_add)->find();
		if(!$how)
		{
			throw new Exception("没有这个用户.");
		}
	
	
		if ($password!='')
		{
			if(strlen($password)<6)
			{
				throw new Exception("密码长度不够,最少6位.");
			}
			$S_key=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
			foreach($S_key as $value)
			{
				if (strpos($password,$value)!==false)
				{
					throw new Exception("密码不能包含特殊字符.");
				}
			}
			$password=md5 ( C ( 'USER_SALT' ) . addslashes ( $password ) );
			$data['password']	= $password;
			return $this->data($data)->where("user_name='$name'")->save();
		}
		else
		{
			throw new Exception("请输入新密码.");
		}
	}
	public function member_delete($name)
	{
		if(is_array($name))
		{
			$name=implode('\',\'',$name);
		}
		if(strlen($name)<2)
		{
			throw new Exception("没有选择用户");
		}
		return $this	->where("user_name in ('$name') and is_admin=0")->delete();
	}
	
	public function get_phone_type($phone){
		if(!$phone) return false;
		return $this->field('phonetype')->where("phone = '$phone'")->find();
	}
}