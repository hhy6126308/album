<?php
namespace Home\Controller;

use \Home\Model\AlbumModel;
use \Home\Model\AlbumGroupRoleModel;
use \Home\Model\UserGroupRoleModel;
use Think\Exception;

class AlbumLockController extends BaseController {

    protected $npc = array(
        array("url" => "/Album", 'name' => '相册列表' ),
    );

    public function _initialize () {
        layout("Comon/layout");
        $this->checkAuth('AlbumLock');
    }

    public function index(){
        $id = safe_string($_GET['album_id']);
        $M = new AlbumModel();
        try {;
            if(empty($id)){
                throw new Exception("无效id");
            }

            if (session("ADMIN_TYPE") <= 10) {
                $uid = $this->getuid();
                $groupRole = new AlbumGroupRoleModel;
                $roles = $groupRole->where("album_id = $id")->select();
                $group_ids = array2Ids($roles, 'group_id');
                if (!$group_ids) {
                    throw new Exception("无权操作此相册", 304);
                }
                $userRole = new UserGroupRoleModel;
                if(!$userRole->where("group_id in ($group_ids) and user_id = $uid")->select()) {
                    throw new Exception("无权操作此相册", 304);
                }

            }

            if ($_POST) {
                $isLock = safe_string($_POST['is_lock']);
                $lock_pwd = safe_string($_POST['lock_pwd']);

                if ($isLock && !$lock_pwd) {
                    throw new Exception('请输入密码');
                }

                $data = array(
                    'is_lock' => $isLock,
                    'lock_pwd' => $isLock ? $lock_pwd : ''
                );
                if (false === $M->where("id=$id")->save($data)) {
                    throw new Exception('保存失败！');
                }
                $this->success('操作成功！', '/AlbumLock/index?album_id=' . $id);
                exit();
            }
            $info = $M->where("id=$id")->find();
            $this->assign('info', $info);
            $this->display();
        } catch (Exception $e ) {
            $url = $id && $e->getCode() != 304 ? '/AlbumLock/index?album_id=' . $id : '/';
            $this->error($e->getMessage(), $url, 3);
        }
    }

}