<?php
namespace Home\Controller;

use \Home\Model\AlbumGroupModel;
use Home\Model\UserGroupRoleModel;
use Home\Model\RedisModel;
use Home\Model\UserSocialModel;
use Home\Model\UserModel;

class AlbumGroupController extends BaseController {

    public function index(){
        $token = safe_string($_GET['token']);
        $user_id = 0;
        if($token){
            $redisM = new RedisModel();
            if($redisM->exists($token) == 0){
                $this->out_put(array("error"=>304,"msg"=>"管理员未登录","data"=>""));
            }
            $user_id = $redisM->get($token);
            //get admin
            $UserGroupRoleModel = new UserGroupRoleModel();
            $roles = $UserGroupRoleModel->where("user_id={$user_id}")->order("id desc")->select();
        }else{
            $openid = $this->getOpenid();
            //get user
            $userSocial = new UserSocialModel();
            $user = $userSocial->where("openid='{$openid}'")->find();
            $UserGroupRoleModel = new UserGroupRoleModel();
            $roles = $UserGroupRoleModel->where("user_social_id={$user['id']}")->order("id desc")->select();
        }

        $ids = array_column($roles, 'group_id');
        $M = new AlbumGroupModel();
        if(empty($ids)){
            $data = [];
        }else{
            $data = $M->where(['id' => ['in', $ids]])->order("id desc")->select();
        }
        if ($user_id) {
            $userM = new UserModel();
            $user = $userM->where("id = {$user_id}")->find();
            //超管
            if ($user) {
                $cell = [
                    'id' => "0",
                    'group_name' => '公共相册',
                    'group_desc' => '公共相册',
                    'aqrcode_url' => 'https://image.album.iqikj.com/gh_9a715158ff89_344.jpg'
                ];
                array_unshift($data, $cell);
            }
        }
        
        $rs['error'] = 0;
        $rs['msg'] = 'ok';
        $rs['data'] = $data;

        $this->out_put($rs);
    }

    public function info(){
        $id = safe_string($_GET['id']);
        if ($id>0) {
            $M           = new AlbumGroupModel();
            $rs['error'] = 0;
            $rs['msg']   = 'ok';
            $rs['data']  = $M->where("id=$id")->find();
        } else {
            $rs['error'] = 0;
            $rs['msg']   = 'ok';
            $rs['data']  = [
                'id'          => "0",
                'group_name'  => '公共相册',
                'group_desc'  => '公共相册',
                'aqrcode_url' => 'https://image.album.iqikj.com/gh_9a715158ff89_344.jpg',
            ];
        }

        $this->out_put($rs);
    }
}