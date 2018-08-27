<?php
namespace Home\Controller;

use Home\Model\UserModel;

class UserController extends BaseController {

    protected $npc = array(
        array("url" => '/User','name' => '用户管理' ),
    );

    public function _initialize () {
        layout("Comon/layout");
        if($_SERVER['REQUEST_URI'] == '/User/edit' || $_SERVER['REQUEST_URI'] == '/User/loginout'){
            $this->checkAuth();
        }else{
            $this->checkAuth('User');
        }

    }

    public function index()
    {
        Vendor('Mypaging.page');
        $M = new UserModel();
        $keyword = safe_string($_GET['keyword']);
        $where = "1=1";
        if ($keyword) {
            $where .= " and  name like '%$keyword%'";
        }

        $count = $M->where($where)->count();
        $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
        $page = $pageM->show();
        $lists = $M->where($where)->page($_GET['page'], 20)->order("id desc")->select();
        $this->assign('page',$page);
        $this->assign('lists',$lists);
        $this->assign('keyword',$keyword);
        $this->assign('sidebar_name','user_list');
        $this->display('index');
    }

    public function add()
    {
        $this->assign('sidebar_name','user_list');
        $M = new UserModel();
        try {
            if ($_POST) {
                $data['name'] = safe_string($_POST['name']);
                $data['email'] = safe_string($_POST['email']);
                $data['user_type'] = safe_string($_POST['user_type']);
                $data['login_pwd'] = $_POST['login_pwd'] ? md5(C('USER_SALT') . safe_string($_POST['login_pwd'])) : md5(C('USER_SALT') . 123456);
                $data['create_time'] = date("Y-m-d H:i:s");
                if ( empty($data['email']) ) {
                    throw new \Think\Exception("账号不能为空！", 1);
                }
                if ( empty($data['name']) ) {
                    throw new \Think\Exception("昵称不能为空！", 1);
                }
                if ( false === $M->add($data)) {
                    throw new \Think\Exception("用户添加失败！", 1);
                }
                $this->success('用户操作成功！', '/User');
                exit();
            }
            $this->display();
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/User/add', 3);
        }
    }

    /**
     * 修改密码
     */
    public function edit()
    {
        //$this->assign('sidebar_name','edit');
        $M = new UserModel();
        try {
            $uid = $this->getuid();
            if ($uid) {
                $user = $M->where("id=$uid")->find();
                if ($_POST) {
                    $data['name'] = safe_string($_POST['name']);
                    $data['old_pwd'] = $_POST['old_pwd'] ? md5(C('USER_SALT') . safe_string($_POST['old_pwd'])) : '';
                    $data['login_pwd'] = $_POST['login_pwd'] ? md5(C('USER_SALT') . safe_string($_POST['login_pwd'])) : '';
                    if ( empty($data['name']) ) {
                        throw new \Think\Exception("昵称不能为空！", 1);
                    }

                    if (!empty($data['login_pwd']) ) {
                        if ( empty($data['old_pwd']) ) {
                            throw new \Think\Exception("旧密码不能为空！", 1);
                        }
                        if($data['old_pwd'] !== $user['login_pwd']){
                            throw new \Think\Exception("旧密码错误！", 1);
                        }
                    }elseif($data['name'] == $user['name']){
                        $this->assign('user', $user);
                        $this->display();
                    }

                    if ( false === $M->where("id=$uid")->save(array_filter($data)) ) {
                        throw new \Think\Exception("修改失败！", 1);
                    }
                    session('ADMIN_NAME', $data['name']);
                    session("ADMIN_AUTHID", null);
                    $this->success("修改成功", '/Login', 1);
                    exit();
                }
                $this->assign('user', $user);
            }
            $this->display();
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/User/edit', 3);
        }
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
//                $this->error($e->getMessage(), '/User/register', 3);
//                //$this->assign('msg', 'error:'.$e->getMessage());
//            }
//        }
        $this->display();
    }

    public function loginout(){
        session("ADMIN_AUTHID", null);
        redirect("/Login");
    }
}