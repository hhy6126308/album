<?php
namespace Home\Controller;

use \Home\Model\AlbumModel;
use \Home\Model\ImgModel;

class AlbumController extends BaseController {

    protected $npc = array(
        array("url" => '/AlbumGroup', 'name' => '相册组列表' ),
        array("url" => "/Album", 'name' => '相册列表' ),
    );

    public function _initialize () {
        layout("Comon/layout");
        $this->checkAuth('Album');
        if(isset($_SESSION['group_id'])){
            $this->npc[1]['url'] = '/Album?group_id='.$_SESSION['group_id'];
        }
        $this->assign('sidebar_name','album');
    }

    public function index(){
        Vendor('Mypaging.page');
        $M = new AlbumModel;
        $group_id = safe_string($_GET['group_id']);
        if($group_id){
            $this->npc[1]['url'] = '/Album?group_id='.$group_id;
            session('group_id', $group_id);
            $keyword = safe_string($_GET['keyword']);
            $id = safe_string($_GET['id']);
            $where = "group_id={$group_id}";
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
        }

        $this->display('index');
    }

    public function info(){
        $ac = safe_string($_GET['ac']);
        $id = safe_string($_GET['id']);
        $M = new AlbumModel();
        try {
            switch ( $ac ) {
                case 'edit':
                case 'add': 
                    if ( $ac == 'edit' && empty($id)) {
                        throw new \Think\Exception("未知相册！", 1);
                    }
                    if ($_POST) {
                        $data['album_name'] = safe_string($_POST['album_name']);
                        $data['group_id'] = $_SESSION['group_id'];
                        $banner = parse_url(safe_string($_POST['album_banner']));
                        $index = parse_url(safe_string($_POST['album_index']));
                        $album_share = parse_url(safe_string($_POST['album_share']));
                        $data['album_banner'] = $banner['path'];
                        $data['album_index'] = $index['path'];
                        $data['album_share'] = $album_share['path'];
                        $data['is_face'] = safe_string($_POST['is_face']) ? safe_string($_POST['is_face']) : 0;
                        $data['is_reward'] = safe_string($_POST['is_reward']) ? safe_string($_POST['is_reward']) : 0;
                        $data['album_des'] = safe_string($_POST['album_des']);
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
                        $this->success('相册操作成功！', '/Album?group_id='.$_SESSION['group_id']);
                        exit();
                    }
                    break;
                case 'del':
                    if(empty($id)){
                        throw new \Think\Exception("未知相册！", 1);
                    }
                    $M->where("id=$id")->delete();
                    $this->success('相册操作成功！', '/Album?group_id='.$_SESSION['group_id']);
                    exit();
                    break;
                default:
                    if (empty($id)) {
                        throw new \Think\Exception("未知相册！", 1);
                    }
                    break;
            }
            $id && $info = $M->where("id=$id")->find();
            #$info['g_banners'] = $info['g_banners'] ? unserialize($info['g_banners']) : array() ;
            $this->assign('ac',$ac);
            $this->assign('info',$info);
            $this->assign('savePicUrl',C('SavePicUrl'));
            $this->display();
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/Album?group_id='.$_SESSION['group_id'], 3);
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
            $this->error($e->getMessage(), '/Album?group_id='.$_SESSION['group_id'], 3);
        }
    }

}