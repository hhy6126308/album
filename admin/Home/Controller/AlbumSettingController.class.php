<?php
namespace Home\Controller;

use \Home\Model\AlbumSettingModel;

class AlbumSettingController extends BaseController {

    protected $npc = array(
        array("url" => '/AlbumSetting', 'name' => '系统设置' ),
    );

    public function _initialize () {
        layout("Comon/layout");
        $this->checkAuth();
        session('SESSION_HISTORYURL', '/AlbumSetting');
        $this->assign('sidebar_name','album_setting');
    }

    public function index () {
        layout("Comon/layout");
        $M = new AlbumSettingModel;
        $uid = 1;
        if ($_POST) {
            try {
                $data['reward'] = safe_string($_POST['reward']) ? safe_string($_POST['reward']) : 0;
                if ( false === $M->where("id=$uid")->save($data) ) {
                    throw new \Think\Exception("系统错误保存失败！", 1);
                }
                $this->success("保存成功！", '/AlbumSetting', 3);
                exit();
            } catch ( \Think\Exception $e ) {
                $this->error($e->getMessage(), '/AlbumSetting', 3);
            }
        }
        $setting = $M->where("id=$uid")->find();
        $this->assign('setting', $setting);
        $this->display();
    }

}