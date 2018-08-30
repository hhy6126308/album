<?php
namespace Home\Controller;

use Home\Model\AlbumGroupModel;
use Home\Model\AlbumGroupRoleModel;
use \Home\Model\AlbumModel;
use \Home\Model\ImgModel;

class AlbumController extends BaseController {

    protected $npc = array(
        array("url" => "/Album", 'name' => '相册列表' ),
    );

    public function _initialize () {
        layout("Comon/layout");
        $this->checkAuth('Album');
        $this->assign('sidebar_name','album');
    }

    public function index(){
        Vendor('Mypaging.page');
        $M = new AlbumModel();
        $keyword = safe_string($_GET['keyword']);
        $where = "1=1";
        if ($keyword) {
            $where .= " and album_name like '%$keyword%'";
        }
        $count = $M->where($where)->count();
        $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
        $page = $pageM->show();
        $lists = $M->where($where)->page($_GET['page'], 20)->order("id desc")->select();
        $this->assign('savePicUrl',C('SavePicUrl'));
        $this->assign('page',$page);
        $this->assign('lists',$lists);
        $this->assign('keyword',$keyword);

        $this->display('index');
    }

    public function info(){
        $ac = safe_string($_GET['ac']);
        $id = safe_string($_GET['id']);
        $groups = $roles = [];
        $M = new AlbumModel();
        $AlbumGroupModel = new AlbumGroupModel();
        $AlbumGroupRoleModel = new AlbumGroupRoleModel();
        try {
            M()->startTrans();
            switch ( $ac ) {
                case 'edit':
                case 'add': 
                    if ( $ac == 'edit' && empty($id)) {
                        throw new \Think\Exception("未知相册！", 1);
                    }
                    if ($_POST) {
                        $data['album_name'] = safe_string($_POST['album_name']);
                        $banner = parse_url(safe_string($_POST['album_banner']));
                        $index = parse_url(safe_string($_POST['album_index']));
                        $share = parse_url(safe_string($_POST['album_share']));
                        $data['album_banner'] = $banner['path'];
                        $data['album_index'] = $index['path'];
                        $data['is_face'] = safe_string($_POST['is_face']) ? safe_string($_POST['is_face']) : 0;
                        $data['is_reward'] = safe_string($_POST['is_reward']) ? safe_string($_POST['is_reward']) : 0;
                        $data['album_share'] = $share['path'];
                        $data['album_des'] = safe_string($_POST['album_des']);
                        $group_ids = $_POST['group_ids'];
                        if ( empty($data['album_name']) ) {
                            throw new \Think\Exception("商品名称不能为空！", 1);  
                        } 
                        if ($ac == 'add') {
                            $data['create_time'] = date("Y-m-d H:i:s");
                            $id = $M->add($data);
                            if ( false === $id) {
                                throw new \Think\Exception("相册添加失败！", 1);
                            }
                            //生产二维码
                            $url = \getWxaqrcode('pages/album/index?albumId='.$id);
                            $data['aqrcode_url'] = $url;
                            $M->where("id=$id")->save($data);
                        } else {
                            if ( false === $M->where("id=$id")->save($data)) {
                                throw new \Think\Exception("相册修改失败！", 1);
                            }
                        }
                        $AlbumGroupRoleModel->where("album_id=$id")->delete();
                        if(!empty($group_ids)){
                            $data['album_id'] = $id;
                            $data['create_time'] = date("Y-m-d H:i:s");
                            foreach ($group_ids as $group_id){
                                $data['group_id'] = $group_id;
                                if ( false === $AlbumGroupRoleModel->add($data)) {
                                    throw new \Think\Exception("添加分组失败！", 1);
                                }
                            }
                        }
                        M()->commit();
                        $this->success('相册操作成功！', '/Album');
                        exit();
                    }
                    break;
                case 'del':
                    if(empty($id)){
                        throw new \Think\Exception("未知相册！", 1);
                    }
                    $M->where("id=$id")->delete();
                    $AlbumGroupRoleModel->where("album_id=$id")->delete();
                    M()->commit();
                    $this->success('相册操作成功！', '/Album');
                    exit();
                    break;
                default:
                    if (empty($id)) {
                        throw new \Think\Exception("未知相册！", 1);
                    }
                    break;
            }

            $groups = $AlbumGroupModel->order("id desc")->select();
            if(!empty($id)){
                $roles = $AlbumGroupRoleModel->where("album_id={$id}")->order("id desc")->select();
            }
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
            $id && $info = $M->where("id=$id")->find();
            #$info['g_banners'] = $info['g_banners'] ? unserialize($info['g_banners']) : array() ;
            $this->assign('ac',$ac);
            $this->assign('groups',$groups);
            $this->assign('roles',$roles);
            $this->assign('info',$info);
            $this->assign('savePicUrl',C('SavePicUrl'));
            $this->display();
        } catch ( \Think\Exception $e ) {
            M()->rollback();
            $this->error($e->getMessage(), '/Album', 3);
        }
    }

    public function detail()
    {
        Vendor('Mypaging.page');
        $ac = safe_string($_GET['ac']);
        $album_id = safe_string($_GET['album_id']);
        $id = safe_string($_GET['id']);
        $ids = safe_string($_GET['ids']);
        $keyword = safe_string($_GET['keyword']);
        $album = new AlbumModel();
        $image = new ImgModel();
        try {
            if(!empty($ac) && !empty($id) && $ac == 'del'){
                $image->where("id=$id")->delete();
            }
            if(!empty($ac) && !empty($ids) && $ac == 'delImages'){
                $image->where(['id' => ['in', $ids]])->delete();
            }
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
        }catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/Album/detail?album_id='.$album_id, 3);
        }
    }

    public function upload()
    {
        try {
            $id = safe_string($_GET['id']);
            if (empty($id))
                throw new \Exception('未知相册！');
            $M = new AlbumModel();
            $info = $M->where("id=$id")->find();
            $this->assign('info',$info);
            $this->display();
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/Album', 3);
        }
    }

}