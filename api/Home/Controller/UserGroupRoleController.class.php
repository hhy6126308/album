<?php
namespace Home\Controller;

use Home\Model\AlbumGroupModel;
use Home\Model\UserGroupRoleModel;
use Home\Model\UserModel;
use Home\Model\UserSocialModel;

class UserGroupRoleController extends BaseController {

    /**
     * 授权
     */
    public function role()
    {
        $id = safe_string($_GET['group_id']);
        $openid = $this->getOpenid();
        //get user
        $userSocial = new UserSocialModel();
        $user = $userSocial->where("openid='{$openid}'")->find();
        if($id){
            $UserGroupRoleModel = new UserGroupRoleModel();
            $data['user_social_id'] = $user['id'];
            $data['group_id'] = $id;
            $data['create_time'] = date("Y-m-d H:i:s");
            if(false === $UserGroupRoleModel->add($data)){
                $rs['error'] = 1;
                $rs['msg'] = '授权失败';
                $rs['data'] = '';
            }else{
                $rs['error'] = 0;
                $rs['msg'] = 'ok';
                $rs['data'] = '';
            }
        }else{
            $rs['error'] = 1;
            $rs['msg'] = '授权失败';
            $rs['data'] = '';
        }

        $this->out_put($rs);
    }

}