<?php
namespace Home\Controller;

use \Home\Model\AlbumGroupModel;
use Home\Model\AlbumGroupRoleModel;
use Home\Model\AlbumModel;
use Home\Model\ImgModel;
use Home\Model\UserGroupRoleModel;

class AlbumGroupController extends BaseController {

    protected $npc = array(
        array("url" => '/AlbumGroup', 'name' => '相册组列表' ),
    );

    public function _initialize () {
        layout("Comon/layout");
        $this->checkAuth('AlbumGroup');
        $this->assign('sidebar_name','album_group');
    }

    public function index(){
        Vendor('Mypaging.page');
        $M = new AlbumGroupModel;
        $keyword = safe_string($_GET['keyword']);
        $user_type = session("ADMIN_TYPE");
        $where = "1=1";
        if ($keyword) {
            $where .= " and  group_name like '%$keyword%'";
        }
        if($user_type == 10){
            $uid = $this->getuid();
            $UserGroupRoleModel = new UserGroupRoleModel();
            $roles = $UserGroupRoleModel->where("user_id={$uid}")->order("id desc")->select();
            if(empty($roles)){
                $count = 0;
                $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
                $page = $pageM->show();
                $lists = [];
            }else{
                $ids = array_column($roles, 'group_id');
                $count = $M->where($where)->where(['id' => ['in', $ids]])->count();
                $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
                $page = $pageM->show();
                $lists = $M->where($where)->where(['id' => ['in', $ids]])->page($_GET['page'], 20)->order("id desc")->select();
            }
        }else{
            $count = $M->where($where)->count();
            $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
            $page = $pageM->show();
            $lists = $M->where($where)->page($_GET['page'], 20)->order("id desc")->select();
        }
        $this->assign('savePicUrl',C('SavePicUrl'));
        $this->assign('page',$page);
        $this->assign('lists',$lists);
        $this->assign('keyword',$keyword);
        $this->display('index');
    }

    public function info(){
        $ac = safe_string($_GET['ac']);
        $id = safe_string($_GET['id']);
        $M = new AlbumGroupModel();
        $AlbumGroupRoleModel = new AlbumGroupRoleModel();
        try {
            switch ( $ac ) {
                case 'edit':
                case 'add':
                    if ( $ac == 'edit' && empty($id)) {
                        throw new \Think\Exception("未知分组！", 1);
                    }
                    if ($_POST) {
                        $data['group_name'] = safe_string($_POST['group_name']);
                        $data['group_desc'] = safe_string($_POST['group_desc']);
                        if ( empty($data['group_name']) ) {
                            throw new \Think\Exception("组名不能为空！", 1);
                        }
                        if ($ac == 'add') {
                            $data['create_time'] = date("Y-m-d H:i:s");
                            $id = $M->add($data);
                            if ( false === $id) {
                                throw new \Think\Exception("分组添加失败！", 1);
                            }
                            //生产二维码
                            $url = \getWxaqrcode('pages/myspace/index?group_id='.$id);
                            $data['aqrcode_url'] = $url;
                            $M->where("id=$id")->save($data);
                        } else {
                            if ( false === $M->where("id=$id")->save($data)) {
                                throw new \Think\Exception("分组修改失败！", 1);
                            }
                        }
                        $this->success('分组操作成功！', '/AlbumGroup');
                        exit();
                    }
                    break;
                case 'del':
                    if(empty($id)){
                        throw new \Think\Exception("未知分组！", 1);
                    }
                    $M->where("id=$id")->delete();
                    $AlbumGroupRoleModel->where("group_id={$id}")->delete();
                    $this->success('分组删除成功！', '/AlbumGroup');
                    exit();
                    break;
                default:
                    if (empty($id)) {
                        throw new \Think\Exception("未知分组！", 1);
                    }
                    break;
            }
            $id && $info = $M->where("id=$id")->find();
            $this->assign('ac',$ac);
            $this->assign('info',$info);
            $this->assign('savePicUrl',C('SavePicUrl'));
            $this->display();
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/AlbumGroup', 3);
        }
    }

    public function detail(){
        Vendor('Mypaging.page');
        $M = new AlbumModel();
        $AlbumGroupRoleModel = new AlbumGroupRoleModel();
        $group_id = safe_string($_GET['group_id']);
        $ac = safe_string($_GET['ac']);
        $album_id = safe_string($_GET['album_id']);
        if($group_id){
            //delete
            if ( $ac == 'reject' && !empty($album_id)) {
                $AlbumGroupRoleModel->where("group_id={$group_id}")->where("album_id={$album_id}")->delete();
            }

            $album_ids = $AlbumGroupRoleModel->where("group_id={$group_id}")->order("id desc")->select();
            $keyword = safe_string($_GET['keyword']);
            $where = "1=1";
            if ($keyword) {
                $where .= " and album_name like '%$keyword%'";
            }
            if(empty($album_ids)){
                $count = 0;
                $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
                $page = $pageM->show();
                $lists = [];
            }else{
                $ids = array_column($album_ids, 'album_id');
                $count = $M->where($where)->where(['id' => ['in', $ids]])->count();
                $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
                $page = $pageM->show();
                $lists = $M->where($where)->where(['id' => ['in', $ids]])->page($_GET['page'], 20)->order("id desc")->select();
            }
            $this->assign('savePicUrl',C('SavePicUrl'));
            $this->assign('page',$page);
            $this->assign('lists',$lists);
            $this->assign('group_id',$group_id);
            $this->assign('keyword',$keyword);
        }

        $this->display();
    }

    public function images()
    {
        Vendor('Mypaging.page');
        $ac = safe_string($_GET['ac']);
        $album_id = safe_string($_GET['album_id']);
        $keyword = safe_string($_GET['keyword']);
        $album = new AlbumModel();
        $image = new ImgModel();

        $info = $album->where("id=$album_id")->find();
        $where = "album_id=$album_id";
        if ($keyword) {
            $where .= " and  img_name like '%$keyword%'";
        }

        $count = $image->where($where)->count();
        $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
        $page = $pageM->show();
        $detail = $image->where($where)->page($_GET['page'], 20)->order("id desc")->select();
        $this->assign('page',$page);
        $this->assign('info',$info);
        $this->assign('detail',$detail);
        $this->assign('keyword',$keyword);
        $this->assign('savePicUrl',C('SavePicUrl'));
        $this->display();
    }
}