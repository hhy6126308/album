<?php
namespace Home\Controller;

use \Home\Model\AlbumGroupModel;
use Home\Model\UserGroupRoleModel;
use Home\Model\RedisModel;
use Home\Model\UserSocialModel;

class AlbumGroupController extends BaseController {

    public function index(){
        $token = safe_string($_GET['token']);
        $openid = $this->getOpenid();
        //get user
        $userSocial = new UserSocialModel();
        $user = $userSocial->where("openid='{$openid}'")->find();
        $UserGroupRoleModel = new UserGroupRoleModel();
        $roles = $UserGroupRoleModel->where("user_id={$user['id']}")->order("id desc")->select();
        $ids = array_column($roles, 'group_id');

        $M = new AlbumGroupModel();
        $rs['error'] = 0;
        $rs['msg'] = 'ok';
        $rs['data'] = $M->where(['id' => ['in', $ids]])->order("id desc")->select();

        $this->out_put($rs);
    }

    public function info(){
        $id = safe_string($_GET['id']);
        $M = new AlbumGroupModel();
        $rs['error'] = 0;
        $rs['msg'] = 'ok';
        $rs['data'] = $M->where("id=$id")->find();

        $this->out_put($rs);
    }
}