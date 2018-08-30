<?php
namespace Home\Controller;

use Home\Model\AlbumModel;
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

        $album_id = safe_string($_GET['album_id']);
        if ($album_id) {
            $albumM = new AlbumModel();
            $album = $albumM->where("id = $album_id")->find();
            if ($album) {
                $rs['data']['reward'] = $rs['data']['reward'] && $album['is_reward'] ? 1 : 0;
            }
        }
        
        $this->out_put($rs);
    }

}