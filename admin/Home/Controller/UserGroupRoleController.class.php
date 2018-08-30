<?php
namespace Home\Controller;

use Home\Model\AlbumGroupModel;
use Home\Model\UserGroupRoleModel;
use Home\Model\UserModel;
use Home\Model\UserSocialModel;

class UserGroupRoleController extends BaseController {

    protected $npc = array(
        array("url" => '/AlbumGroup', 'name' => '相册组列表' )
    );

    public function _initialize () {
        layout("Comon/layout");
        $this->checkAuth('UserGroupRole');
    }

    public function users()
    {
        Vendor('Mypaging.page');
        $UserSocialModel = new UserSocialModel();
        $UserGroupRoleModel = new UserGroupRoleModel();
        $AlbumGroupModel = new AlbumGroupModel();
        $keyword = safe_string($_GET['keyword']);
        $group_id = safe_string($_GET['group_id']);
        if(!empty($group_id)){
            session('group_id', $group_id);
        }else{
            $group_id = $_SESSION['group_id'];
        }
        $where = "group_id={$group_id} and user_id is null";
        if ($keyword) {
            $where .= " and user_social.nick_name like '%$keyword%'";
        }
        $count = $UserGroupRoleModel->join('LEFT JOIN user_social ON user_social.id = user_group_role.user_social_id')
            ->where($where)
            ->count();
        $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
        $page = $pageM->show();
        $lists = $UserGroupRoleModel->join('LEFT JOIN user_social ON user_social.id = user_group_role.user_social_id')
            ->where($where)
            ->field('user_group_role.*,user_social.nick_name')
            ->page($_GET['page'], 20)
            ->order("user_group_role.id desc")
            ->select();
        $albumGroup = $AlbumGroupModel->where("id=$group_id")->find();
        $this->assign('page',$page);
        $this->assign('lists',$lists);
        $this->assign('albumGroup',$albumGroup);
        $this->assign('keyword',$keyword);
        $this->assign('sidebar_name','album_group');
        $this->display('users');
    }

    public function delete()
    {
        try {
            $id = safe_string($_GET['id']);
            if($id){
                $UserGroupRoleModel = new UserGroupRoleModel();
                if (false === $UserGroupRoleModel->where("id=$id")->delete()){
                    throw new \Think\Exception("删除失败！", 1);
                }
                $this->success('用户操作成功！', '/UserGroupRole/users');
            }else{
                throw new \Think\Exception("删除失败！", 1);
            }
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/UserGroupRole/users', 2);
        }
    }

    /**
     * 添加用户相册分组
     */
    public function role()
    {
        $id = safe_string($_GET['id']);
        $ac = safe_string($_GET['ac']);
        if($id){
            $UserModel = new UserModel();
            $UserGroupRoleModel = new UserGroupRoleModel();
            $AlbumGroupModel = new AlbumGroupModel();
            if ($ac && $ac == 'add') {
                try {
                    M()->startTrans();
                    $group_ids = $_POST['group_ids'];
                    $UserGroupRoleModel->where("user_id=$id")->delete();
                    if(!empty($group_ids)){
                        $data['user_id'] = $id;
                        $data['create_time'] = date("Y-m-d H:i:s");
                        foreach ($group_ids as $group_id){
                            $data['group_id'] = $group_id;
                            if ( false === $UserGroupRoleModel->add($data)) {
                                throw new \Think\Exception("分组添加失败！", 1);
                            }
                        }
                    }
                    M()->commit();
                    $this->success('用户操作成功！', '/User');
                    exit();
                } catch ( \Think\Exception $e ) {
                    M()->rollback();
                    $this->error($e->getMessage(), '/User', 3);
                }
            }
            $user = $user = $UserModel->where("id=$id")->find();
            $groups = $AlbumGroupModel->order("id desc")->select();
            $roles = $UserGroupRoleModel->where("user_id={$id}")->order("id desc")->select();
            if(!empty($groups)){
                foreach ($groups as &$group){
                    $group['is_selected'] = 0;
                    if(!empty($roles)){
                        foreach ($roles as $role){
                            if($group['id'] == $role['group_id']){
                                $group['is_selected'] = 1;
                            }
                        }
                    }
                }
            }
            $this->assign('user',$user);
            $this->assign('groups',$groups);
            $this->assign('roles',$roles);
            $this->assign('sidebar_name','user_list');
        }
        $this->npc = array(
            array("url" => '/User', 'name' => '用户列表' )
        );
        $this->display('role');
    }
}