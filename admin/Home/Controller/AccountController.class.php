<?php
namespace Home\Controller;

class AccountController extends BaseController {

    protected $npc = array(
        array("url" => '/Account','name' => '用户中心' ),
    );       
    
    public function login(){
        $redirectUrl = safe_string($_GET['redirect_url']);
        if ($_POST) {
            try {
                $data['email'] = safe_string($_POST['email']);
                $data['login_pwd'] = safe_string($_POST['login_pwd']);
                if (empty($data['email']) || empty($data['login_pwd'])) {
                    throw new \Think\Exception("请填写登录邮箱和密码！", 1);
                } 

                $userM = new  \Home\Model\UserModel;
                $user = $userM->where("email = '{$data['email']}'")->find();
                if ($user) {
                    $data['last_login'] = Date('Y-m-d H:i:s');
                    $data['login_pwd'] = md5(C('USER_SALT') . $data['login_pwd']);
                    if ( $user['login_pwd'] != $data['login_pwd']) {
                        throw new \Think\Exception("帐号或密码错误！", 1);
                    }
                    $userM->where("id={$user['id']}")->save($data);
                    session('ADMIN_AUTHID', $user['id']);
                    session('ADMIN_EMAIL', $user['email']);
                    session('ADMIN_APPNAME', $user['appname']);
                    $redirectUrl = $redirectUrl ? $redirectUrl : session('SESSION_HISTORYURL') ? session('SESSION_HISTORYURL') : '/';
                    redirect($redirectUrl);
                } else {
                    throw new \Think\Exception("帐号密码错误！", 1);   
                }
                $this->assign('msg', '注册成功！');
            } catch ( \Think\Exception $e ) {
                $this->error($e->getMessage(), '/Account/login', 2);
                //$this->assign('msg', 'error:'.$e->getMessage());
            }
        }
        $this->assign('redirect_url', $redirect_url);
        $this->display();
    }

    public function register(){
        die("不支持注册！");
//        if ($_POST) {
//            try {
//                $data['email'] = safe_string($_POST['email']);
//                $data['login_pwd'] = safe_string($_POST['login_pwd']);
//                $data['re_login_pwd'] = safe_string($_POST['re_login_pwd']);
//                if (empty($data['email']) || empty($data['login_pwd']) || empty($data['re_login_pwd'])) {
//                    throw new \Think\Exception("请填写完整的注册信息！", 1);
//                }
//
//                if ($data['login_pwd'] !== $data['re_login_pwd']) {
//                    throw new \Think\Exception("两次输入的密码不一致！", 1);
//                }
//
//                $userM = new  \Home\Model\UserModel;
//                if ( null === $userM->where("email = '{$data['email']}'")->find()) {
//                    $data['create_time'] = Date('Y-m-d H:i:s');
//                    $data['login_pwd'] = md5(C('USER_SALT') . $data['login_pwd']);
//                    if ( $userM ->add($data) === false ) {
//                        throw new \Think\Exception("系统错误！", 1);
//                    }
//                } else {
//                    throw new \Think\Exception("该邮箱已被注册！", 1);
//                }
//                $this->assign('msg', '注册成功！');
//            } catch ( \Think\Exception $e ) {
//                $this->error($e->getMessage(), '/Account/register', 3);
//                //$this->assign('msg', 'error:'.$e->getMessage());
//            }
//        }
        $this->display();
    }

    public function loginout(){
        session("ADMIN_AUTHID", null);
        redirect("/Account/login");
    }
}