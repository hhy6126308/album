<?php
namespace Home\Controller;

use Home\Model\RewardModel;

class RewardController extends BaseController
{

    protected $npc = array(
        array("url" => '/Reawrd', 'name' => '打赏管理'),
    );

    public function _initialize()
    {
        layout("Comon/layout");
        $this->checkAuth();
        $this->assign('sidebar_name', 'reward');
    }

    public function index()
    {
        Vendor('Mypaging.page');
        $M = new RewardModel();
        $select = safe_string($_GET['select']);
        $keyword = safe_string($_GET['keyword']);
        $album_id = safe_string($_GET['album_id']);
        $where = "1=1";
        if ($keyword) {
            if($select == 'album_name'){
                $where .= " and  album.album_name like '%$keyword%'";
            }elseif($select == 'nick_name'){
                $where .= " and  user_social.nick_name like '%$keyword%'";
            }else{
                $where .= " and  img.img_name like '%$keyword%'";
            }
        }elseif($album_id){
            $where .= " and  reward.album_id = $album_id";
        }

        $count = $M->join('LEFT JOIN album ON album.id = reward.album_id')
            ->join('LEFT JOIN img ON img.id = reward.img_id')
            ->join('LEFT JOIN user_social ON user_social.id = reward.user_social_id')
            ->where($where)
            ->count();
        $pageM = new \Vendor\MyPaging($count ,$_GET['page'] );
        $page = $pageM->show();
        $lists = $M->join('LEFT JOIN album ON album.id = reward.album_id')
            ->join('LEFT JOIN img ON img.id = reward.img_id')
            ->join('LEFT JOIN user_social ON user_social.id = reward.user_social_id')
            ->where($where)
            ->field('reward.*,album.album_name,img.img_name,user_social.nick_name')
            ->page($_GET['page'], 20)
            ->order("reward.id desc")
            ->select();
        $this->assign('page', $page);
        $this->assign('lists', $lists);
        $this->assign('select', $select);
        $this->assign('keyword', $keyword);
        $this->display('index');
    }
}