<?php
namespace Home\Controller;

use \Home\Model\AlbumModel;

class AlbumController extends BaseController {

    protected $npc = array(
        array("url" => '/Album', 'name' => '相册管理' ),
    );

    public function _initialize () {
        layout("Comon/layout");
        $this->checkAuth();
        $this->assign('sidebar_name','album');
    }

    public function index(){
        Vendor('Mypaging.page');
        $M = new AlbumModel;
        $keyword = safe_string($_GET['keyword']);
        $where = "is_del=0";
        if ($keyword) {
            $where .= " and  album_name like '%$keyword%'";
        }
        $lists = $M->where($where)->order("id desc")->select();
        $count = $lists ? count($lists) : 0;
        $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
        $page = $pageM->show();
        $this->assign('page',$page);
        $this->assign('lists',$lists);
        $this->assign('keyword',$keyword);
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
                        $data['album_banner'] = safe_string($_POST['album_banner']);
                        $data['album_des'] = safe_string($_POST['album_des']);
                        $data['u_t'] = Date("Y-m-d H:i:s");
                        if ( empty($data['album_name']) ) {
                            throw new \Think\Exception("商品名称不能为空！", 1);  
                        } 
                        if ($ac == 'add') {
                            $data['c_t'] = Date("Y-m-d H:i:s");
                            if ( false === $M->add($data)) {
                                throw new \Think\Exception("相册添加失败！", 1);
                            }
                        } else {
                            if ( false === $M->where("id=$id")->save($data)) {
                                throw new \Think\Exception("相册修改失败！", 1);
                            }
                        }
                        $this->success('相册操作成功！', '/Album');
                        exit();
                    }
                    break;
                case 'del':
                    break;
                default:
                    if (empty($id)) {
                        throw new \Think\Exception("未知商品！", 1);
                    }
                    break;
            }
            $id && $info = $M->where("id=$id")->find();
            #$info['g_banners'] = $info['g_banners'] ? unserialize($info['g_banners']) : array() ;
            $this->assign('ac',$ac);
            $this->assign('info',$info);
            $this->display();
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/Album', 3);
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