<?php
namespace Home\Controller;

use \Home\Model\AlbumSettingModel;

class AlbumSettingController extends BaseController {

    public function index () {
        $album = new AlbumSettingModel();
        $uid = 1;
        $rs['error'] = 0;
        $rs['msg'] = 'ok';
        $rs['data']['reward'] = 0;
        $info = $album->where("id=$uid")->field('reward')->find();
        if(!empty($info)){
            $rs['data']['reward'] = $info['reward'];
        }
        
        $this->out_put($rs);
    }

}