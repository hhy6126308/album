<?php

class MemberAction extends Action {
	Public function _initialize(){
		B('AuthCheck');
	}
    public function index(){
    	$this->assign( 'npa', array('管理首页', '管理员管理') );
        $this->member_list();
	}
	/**
	 * 管理员列表
	 */
	public function member_list()
	{
		try
		{
			$user	= new UserAuthModel();
			$data=$user->member_list();
			$this->assign('data', $data);
		}
		catch (Exception $e)
		{
			$this->assign('error', $e->getMessage());
// 			$this->assignassign('login', $login_data);
		}
		$sys=array('formurl'=>'./?s=Member&a=member_delete',  );
		$this->assign('sys', $sys);
		$this->display('member_list');
	}
	public function member_add(){
		try
		{
			$this->assign( 'npa', array('管理首页', '新增管理员') );
			$step=(empty($_POST['step']))?'':$_POST['step'];
			if(2==$step)
			{
				$name=(empty($_POST['user_name']))?'':$_POST['user_name'];
				$password=(empty($_POST['password']))?'':$_POST['password'];
				$phone=(empty($_POST['phone']))?'':$_POST['phone'];
				$user_type=(empty($_POST['user_type']))?'未知':$_POST['user_type'];
				$phonetype=(empty($_POST['phonetype']))?'':$_POST['phonetype'];
				$user	= new UserAuthModel();
				$data=$user->member_add($name, $password,$phone,$phonetype,$user_type);
				$login	= new LoginAction();
				$login->message('添加用户成功,进入设置改用户权限!',"./?s=Member&a=member_edit&name=$name");
				exit;
			}
		}
		catch (Exception $e)
		{
			$this->assign('error', $e->getMessage());
			$this->assign('data', $_POST);
		}
		$sys=array('goback'=>'./?s=Member&a=index',   'subform'=>'./?s=Member&a=member_add');
		$this->assign('sys', $sys);
		$this->display('member_add');
	}
	/*
     * 编辑权限和密码
     */
	public function member_edit(){
	 
        $this->assign( 'npa', array('管理首页', '权限管理') );
        $this->assign( 'menu', C('Menu') );
        $step=(empty($_POST['step']))?'':$_POST['step'];
        if(2==$step)//save it
        {
            try
            {
                $name=(empty($_POST['name']))?'':$_POST['name'];

                $password=(empty($_POST['password']))?'':$_POST['password'];
                $rightdb=(empty($_POST['auth']))?'':$_POST['auth'];
                
                $right = array();
                foreach($rightdb as $r){
                	$tmp = explode("|", $r);                	
                	$right = array_merge($right,$tmp);
                }
                $User	= new UserAuthModel();
                $User->member_save($name, $password, serialize($right));
                $login	= new LoginAction();
                $login->message('修改资料成功.',"./?s=Member&a=member_list&name=$name");// go on
                exit;
            }
            catch (Exception $e)
            {
                $this->assign('error', $e->getMessage());
            //返回错误信息以及数据,
            }
                $this->display('member_list');
            }
        else//read it
        {
            try
            {
                $name=(empty($_GET['name']))?'':$_GET['name'];
                $User	= new UserAuthModel();
                $data=$User->member_edit($name);
                $this->assign('data',$data);
                $sys=array('goback'=>'./?s=Member&a=index',   'subform'=>'./?s=Member&a=member_edit');
                $this->assign('sys', $sys);
                
                $right=unserialize($data['right_action']);
                if(!empty($right)){
                	foreach($right as $key=>$val){
                	$auth[$val] =1;
                }
                $this->assign('auth',$auth);//模板权限
                }
              
            }
            catch (Exception $e)
            {
                $this->assign('error', $e->getMessage());
            }
            $this->display('member_edit');
        }
		
	}
	public function member_password()//change password
	{
		try
		{
			$this->assign( 'npa', array('管理首页', '重设密码') );
			$step=(empty($_POST['step']))?'':$_POST['step'];
			if(2==$step)
			{
				$name=(empty($_POST['name']))?'':$_POST['name'];
				$password=(empty($_POST['password']))?'':$_POST['password'];
				$repassword=(empty($_POST['repassword']))?'':$_POST['repassword'];
				if($repassword!=$password){
					throw new Exception("两次密码输入不一致");
				}
				if(1==If_manager)
				{
					$User	= new UserAuthModel();
					$User->member_password($name, $password,2);//超级管理员修改密码
				}
				else if(USERNAME==$name)
				{
					$User	= new UserAuthModel();
					$User->member_password($name, $password);
				}else{
					throw new Exception("非管理员用户不能修改他人密码");
				}
				$login	= new LoginAction();
				$login->message('密码修改成功！！！',"./?s=Member&a=member_password&name=$name");
				exit;
				$this->display('member_password');
			}
		}
		catch (Exception $e)
		{
			$this->assign('name',$_POST['name']);
			$this->assign('error', $e->getMessage());
		}
		$sys=array('goback'=>'./?s=Member&a=index',   'subform'=>'./?s=Member&a=member_password');
		$this->assign('sys', $sys);
		$this->display('member_password');
	}
	
	/**
	 * 删除
	 * @param <type> $name
	 * @return <type>
	 */
	public function member_delete()
    {
        try
        {
            $name=(empty($_REQUEST['id']))?'':$_REQUEST['id'];
            $User	= new UserAuthModel();
            $rs	= $User->member_delete($name);
            $login	= new LoginAction();
            if($rs){
	            $login->message("用户删除成功!", './?s=Member&a=index');
            }else{
	            $login->message("用户删除失败!!!!!!!!!!!!!", './?s=Member&a=index');
            }
        }
        catch (Exception $e)
        {
            $this->assign('error', $e->getMessage());
        }
        $this->member_list();
    }
	
}