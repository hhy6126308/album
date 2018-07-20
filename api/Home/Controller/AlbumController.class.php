<?php
namespace Home\Controller;

use \Home\Model\AlbumModel;
use \Home\Model\ImgModel;
use Home\Model\RedisModel;

class AlbumController extends BaseController {

    public function index()
    {
        $token = safe_string($_GET['token']);
        $album_name = safe_string($_GET['album_name']);
        $redisM = new RedisModel();
        if($redisM->exists($token) == 0){
            $rs['error'] = 304;
            $rs['msg'] = '用户未登录';
            $this->out_put($rs);
        }

        $M = new AlbumModel;
        $where = "1=1";
        if ($album_name) {
            $where .= " and  album_name like '%$album_name%'";
        }
        $rs['error'] = 0;
        $rs['msg'] = 'ok';
        $rs['data'] = $M->where($where)->order("id desc")->select();

        $this->out_put($rs);
    }

    public function image()
    {
        $album_id = safe_string($_GET['album_id']);
        if(empty($album_id)){
            $rs['error'] = 1;
            $rs['msg'] = '参数不能为空！';
            $this->out_put($rs);
        }

        $album = new AlbumModel();
        $info = $album->where("id=$album_id")->find();
        if(empty($info)){
            $rs['error'] = 1;
            $rs['msg'] = '相册不存在！';
            $this->out_put($rs);
        }
        $image = new ImgModel();
        $rs['error'] = 0;
        $rs['msg'] = 'ok';
        $rs['data']['album'] = $info;
        $rs['data']['images'] = $image->where("album_id=$album_id")->order("id desc")->select();

        $this->out_put($rs);
    }

    public function pv()
    {
        $album_id = safe_string($_GET['album_id']);
        if(empty($album_id)){
            $rs['error'] = 1;
            $rs['msg'] = '参数不能为空！';
            $this->out_put($rs);
        }

        $album = new AlbumModel();
        $info = $album->where("id=$album_id")->find();
        if(empty($info)){
            $rs['error'] = 1;
            $rs['msg'] = '相册不存在！';
            $this->out_put($rs);
        }
        $data['album_pv'] = $info['album_pv'] + 1;
        if(false === $album->where("id=$album_id")->save($data)){
            $rs['error'] = 1;
            $rs['msg'] = '更新pv失败';
            $rs['data'] = '';
        }else{
            $rs['error'] = 0;
            $rs['msg'] = 'ok';
            $rs['data'] = '';
        }

        $this->out_put($rs);
    }

}