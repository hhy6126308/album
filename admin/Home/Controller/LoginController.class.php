<?php
namespace Home\Controller;

use Home\Model\UserModel;

class LoginController extends BaseController {

    protected $npc = array(
        array("url" => '/Login','name' => '登陆' ),
    );

    public function index(){
        $redirectUrl = safe_string($_GET['redirect_url']);
        if ($_POST) {
            try {
                $data['email'] = safe_string($_POST['email']);
                $data['login_pwd'] = safe_string($_POST['login_pwd']);
                if (empty($data['email']) || empty($data['login_pwd'])) {
                    throw new \Think\Exception("请填写登录邮箱和密码！", 1);
                }

                $userM = new UserModel();
                $user = $userM->where("email = '{$data['email']}'")->find();
                if ($user) {
                    $data['last_login'] = date('Y-m-d H:i:s');
                    $data['login_pwd'] = md5(C('USER_SALT') . $data['login_pwd']);
                    if ( $user['login_pwd'] != $data['login_pwd']) {
                        throw new \Think\Exception("帐号或密码错误！", 1);
                    }
                    $userM->where("id={$user['id']}")->save($data);
                    session('ADMIN_AUTHID', $user['id']);
                    session('ADMIN_EMAIL', $user['email']);
                    session('ADMIN_NAME', $user['name']);
                    session('ADMIN_TYPE', $user['user_type']);
                    $redirectUrl = '/AlbumGroup';
                    redirect($redirectUrl);
                } else {
                    throw new \Think\Exception("帐号密码错误！", 1);
                }
                $this->assign('msg', '注册成功！');
            } catch ( \Think\Exception $e ) {
                $this->error($e->getMessage(), '/Login', 2);
                //$this->assign('msg', 'error:'.$e->getMessage());
            }
        }
        $this->assign('redirect_url', $redirectUrl);
        $this->display();
    }
}