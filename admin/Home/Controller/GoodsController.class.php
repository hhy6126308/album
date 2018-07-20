<?php
namespace Home\Controller;

class GoodsController extends BaseController {

    protected $npc = array(
        array("url" => '/Goods', 'name' => '商品管理' ),
    );

    public function _initialize () {
        layout("Comon/layout");
    }

    public function index(){
        Vendor('Mypaging.page');
        $pageM = new \Vendor\MyPaging(1000 ,$_GET['page'] );
        $page = $pageM->show();
        $gclsM = new \Home\Model\GoodsclsModel;
        $GoodsM = new \Home\Model\GoodsModel;
        $uid = $this->getuid();
        $gcls = $gclsM->where("uid=$uid")->select();
        $lists = $GoodsM->getGoodsList($uid);
        $this->assign('ac',$ac);
        $this->assign('page',$page);
        $this->assign('gcls',$gcls);
        $this->assign('lists',$lists);
        $this->display('index');
    }

    public function goodsinfo(){
        $ac = safe_string($_GET['ac']);
        $id = safe_string($_GET['id']);
        $uid = $this->getuid();
        $GoodsM = new \Home\Model\GoodsModel;
        try {
            switch ( $ac ) {
                case 'edit':
                case 'add': 
                    if ( $ac == 'edit' && empty($id)) {
                        throw new \Think\Exception("未知商品！", 1);         
                    }
                    if ($_POST) {
                        $data['g_name'] = safe_string($_POST['g_name']);          
                        $data['g_releasetime'] = safe_string($_POST['g_releasetime'],Date('Y-m-d H:i:s'));          
                        $data['g_offlinetime'] = safe_string($_POST['g_offlinetime'],'2117-12-31 00:00');          
                        $data['g_state'] = safe_string($_POST['g_state']);          
                        $data['g_cid'] = safe_string($_POST['g_cid']);          
                        $data['g_sq_img'] = safe_string($_POST['g_sq_img']);          
                        $data['g_lg_img'] = safe_string($_POST['g_lg_img']);          
                        $data['g_banners'] = $this->myserialize($_POST['g_banners']);          
                        $data['g_des'] = safe_string($_POST['g_des']);       
                        $data['g_content'] = trim($_POST['g_content']);
                        $data['g_utime'] = Date("Y-m-d H:i:s");
                        if ( empty($data['g_name']) ) {
                            throw new \Think\Exception("商品名称不能为空！", 1);  
                        } 
                        if ($ac == 'add') {
                            $data['g_uid'] = $uid ;
                            $data['g_ctime'] = Date("Y-m-d H:i:s");
                            if ( false === $GoodsM->add($data)) {
                                throw new \Think\Exception("商品添加失败！", 1); 
                            }
                        } else {
                            if ( false === $GoodsM->where("id=$id")->save($data)) {
                                throw new \Think\Exception("商品修改失败！", 1); 
                            }
                        }
                        $this->success('商品操作成功！', '/Goods');
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
            $gclsM = new \Home\Model\GoodsclsModel;
            $gcls = $gclsM->where("uid=$uid")->select();
            $id && $info = $GoodsM->where("id=$id")->find();
            $info['g_banners'] = $info['g_banners'] ? unserialize($info['g_banners']) : array() ;
            $this->assign('ac',$ac);
            $this->assign('gcls',$gcls);
            $this->assign('info',$info);
            $this->display();
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/Goods', 3);
        }
    }

    public function goodscls(){
        $uid = $this->getuid();
        $ac = safe_string($_GET['ac']);
        $gclsM = new \Home\Model\GoodsclsModel;

        switch ($ac) {
            case 'add':
                try {
                    $add['g_cls_name'] = safe_string($_POST['g_cls_name']);
                    if (empty($add['g_cls_name'])) {
                        throw new \Think\Exception("请填写正确的分类名！",1);
                    }
                    if(null !== $gclsM->where("uid=$uid and g_cls_name = '" . $add['g_cls_name'] . "'")->find()){
                        throw new \Think\Exception("请不要重复添加分类！",1);
                    }
                    $add['uid'] = $uid ;
                    $add['g_cls_ctime'] = Date("Y-m-d H:i:s");
                    if( $gclsM->add($add) ===false ) {
                        throw new \Think\Exception("系统错误！",1);
                    }
                    $this->out_put(array('error'=>0));
                } catch ( \Think\Exception $e) {
                    $this->out_put(array('error'=>$e->getcode(), 'msg'=>$e->getMessage()));
                }
                break;
            case 'edit':
                try {
                    $id = safe_string($_GET['id']);
                    if (empty($id)) {
                        throw new \Think\Exception("未知商品分类", 1);
                    }
                    $data['g_cls_name'] = safe_string($_POST['g_cls_name']);
                    $data['g_cls_order'] = intval(safe_string($_POST['g_cls_order']));
                    if (empty($data['g_cls_name'])) throw new \Think\Exception('商品分类名称不能为空！');
                    if ( false === $gclsM->where("id=$id and uid = $uid")->save($data)) {
                        throw new \Think\Exception("保存失败！", 1);
                    }
                    $this->success('保存成功！', '/Goods/goodscls', 3);
                } catch ( \Think\Exception $e ) {
                    $this->error($e->getMessage(), '/Goods/goodscls', 3);
                }
                break;
            case 'del':
                try {
                    $id = safe_string($_GET['id']);
                    if (empty($id)) {
                        throw new \Think\Exception("未知商品分类", 1);
                    }
                    $data['g_cls_state'] = 1;
                    if ( false === $gclsM->where("id=$id and uid = $uid")->save($data)) {
                        throw new \Think\Exception("保存失败！", 1);
                    }
                    $this->success('删除成功！', '/Goods/goodscls', 3);
                } catch ( \Think\Exception $e) {
                    $this->error($e->getMessage(), '/Goods/goodscls', 3);
                }
                break;
            default:
                $clslists = $gclsM->where("uid=$uid and g_cls_state = 0 ")->order("g_cls_order desc")->select();
                $this->assign('lists', $clslists);
                $this->display();
                break;
        }
    }

    public function goodsstock(){
        $uid = $this->getuid();
        $id = safe_string($_GET['id']);
        $ac = safe_string($_GET['ac']);
        $GoodstockM = new \Home\Model\GoodStockModel;
        switch ($ac) {
            case 'add':
                try {
                    $add['g_sp_name'] = safe_string($_POST['g_sp_name']);
                    if (empty($add['g_cls_name'])) {
                        throw new \Think\Exception("请填写正确的分类名！",1);
                    }
                    if(null !== $gclsM->where("uid=$uid and g_cls_name = '" . $add['g_cls_name'] . "'")->find()){
                        throw new \Think\Exception("请不要重复添加分类！",1);
                    }
                    $add['uid'] = $uid ;
                    $add['g_cls_ctime'] = Date("Y-m-d H:i:s");
                    if( $gclsM->add($add) ===false ) {
                        throw new \Think\Exception("系统错误！",1);
                    }
                    $this->out_put(array('error'=>0));
                } catch ( \Think\Exception $e) {
                    $this->out_put(array('error'=>$e->getcode(), 'msg'=>$e->getMessage()));
                }
                break;
            case 'edit':
                try {
                    if (empty($id)) {
                        throw new \Think\Exception("未知商品分类", 1);
                    }
                    $data['g_cls_name'] = safe_string($_POST['g_cls_name']);
                    $data['g_cls_order'] = intval(safe_string($_POST['g_cls_order']));
                    if (empty($data['g_cls_name'])) throw new \Think\Exception('商品分类名称不能为空！');
                    if ( false === $gclsM->where("id=$id and uid = $uid")->save($data)) {
                        throw new \Think\Exception("保存失败！", 1);
                    }
                    $this->success('保存成功！', '/Goods/goodscls', 3);
                } catch ( \Think\Exception $e ) {
                    $this->error($e->getMessage(), '/Goods/goodscls', 3);
                }
                break;
            case 'del':
                try {
                    $id = safe_string($_GET['id']);
                    if (empty($id)) {
                        throw new \Think\Exception("未知商品分类", 1);
                    }
                    $data['g_cls_state'] = 1;
                    if ( false === $gclsM->where("id=$id and uid = $uid")->save($data)) {
                        throw new \Think\Exception("保存失败！", 1);
                    }
                    $this->success('删除成功！', '/Goods/goodsstock', 3);
                } catch ( \Think\Exception $e) {
                    $this->error($e->getMessage(), '/Goods/goodsstock', 3);
                }
                break;
            default:
                $splists = $GoodstockM->where("g_sp_uid=$uid and g_sp_gid = $id")->order("g_sp_ctime desc")->select(); 
                $this->assign('lists', $splists);
                $this->display();
                break;
        }
    }
}