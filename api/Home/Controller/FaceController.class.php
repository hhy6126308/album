<?php

namespace Home\Controller;

use Home\Model\AlbumModel;
use \Home\Model\FaceTaskModel;
use \Home\Model\FaceTaskResultModel;
use \Home\Model\ImgModel;
use \Home\Model\AlbumGroupRoleModel;
use Home\Model\RedisModel;

class FaceController extends BaseController
{

    const APP_ID = '11728659';
    const API_KEY = 'WWKqasb1sTQYHWu9cqZT4ilu';
    const SECRET_KEY = 'VzNkFWWAk7ddiqmH7052L7q1l46TZBmx';

    const REDIS_LIST_FACE_LIST = 'redis_list_face_list';

    private $redis_lock = 'face_recognition_lock_tmp';

    private $client;
    private $redis;
    private $task;
    private $face_token = '';

    public function __construct()
    {
        Vendor('Baiduai.AipFace');
        $this->client = new \Vendor\AipFace(self::APP_ID, self::API_KEY, self::SECRET_KEY);
        $this->redis  = new RedisModel();
    }

    public function querytask()
    {
        $id = safe_string($_GET['task_id']);
        if (empty($id)) {
            $rs['error'] = 1;
            $rs['msg']   = '任务id不能为空';
            $this->out_put($rs);
        }
        $faceTask = new FaceTaskModel();

        $res = $faceTask->where("id = $id")->find();

        if (!$res) {
            $rs['error'] = 1;
            $rs['msg']   = '未找到任务';
            $this->out_put($rs);
        }


        $image = new FaceTaskResultModel();

        $items = $image->where("task_id=$id")->order("score desc")->select();

        $rs['error'] = 0;
        $rs['msg']   = 'ok';
        $rs['data']  = [
            'status' => $res['status'],
            'items'  => $items,
        ];

        $this->out_put($rs);

    }

    public function createTask()
    {
        try {
            $album_id = safe_string($_GET['album_id']);
            $space_id = safe_string($_GET['space_id']);
            $img_url  = safe_string($_GET['img_url']);

            if (!$img_url) {
                throw new \Exception('请上传图片');
            }

            if (empty($album_id) && empty($space_id)) {
                throw new \Exception('参数不能为空');
            }

            $albumM = new AlbumModel();
            $imgM = new ImgModel();
            $type = $album_id ? 0 : 1;
            $type_id = 0;
            $count = 0;
            if ($type == 0) {
                $album = $albumM->where("id = $album_id")->find();
                if (!$album) {
                    throw new \Exception('未知相册！');
                }
                if (!$album['is_face']) {
                    throw new \Exception('该相册不支持人脸识别！');
                }
                $type_id = $album_id;
                $count = $imgM->where("album_id = $album_id")->count();
            } else {
                $type_id = $space_id;
                $AlbumGroupRoleModel = new AlbumGroupRoleModel();
                $album_lists = $AlbumGroupRoleModel->where("group_id = $space_id")->select();
                foreach ($album_lists as $val) {
                    $album_id = $val['album_id'];
                    $album = $albumM->where("id = $album_id")->find();
                    if ($album && $album['is_face']) {
                        $count += $imgM->where("album_id = $album_id")->count();
                    }
                }
            }

            if ($count == 0) {
                throw new \Exception('未找到可识别照片！');
            }

            $faceTask = new FaceTaskModel();
            $data     = [
                'type'    => $type,
                'type_id' => $type_id,
                'img_url' => $img_url,
                'status'  => 0,
                'c_t'     => time(),
                'u_t'     => time(),
            ];
            $res = $faceTask->add($data);
            if (!$res) {
                throw new \Exception('识别失败！');
            }

            $data = [
                "id" => $res,
                'count' => $count
            ];
            $redis = new RedisModel();
            $redis->set("face_task_" . $res, json_encode($data));
            $rs['error'] = 0;
            $rs['msg']   = 'ok';
            $rs['data']  = $data;
            $this->out_put($rs);
        } catch (\Exception $e) {
            $rs['error'] = 1;
            $rs['msg']   = $e->getMessage();
            $this->out_put($rs);
        }
    }

    public function recognition()
    {
        try {
            $faceTask = new FaceTaskModel();
            $albumM = new AlbumModel();
            register_shutdown_function([$this, 'shutdown']);
            while (true) {
                $task_list = $faceTask->where('status = 0')->select();
                if (!$task_list) {
                    sleep(1);
                    continue;
                }

                foreach ($task_list as $task) {
                    $this->task = $task;
                    $this->face_token = '';
                    $id = $this->task['id'];
                    if (!$this->registerLosk() || $faceTask->where('status = 1 and id = ' . $id)->find()) {
                        continue;
                    };
                    $this->log('开始');
                    if ($this->task['type'] == 1) {
                        $album_list = $albumM->where("group_id = " . $this->task['type_id'])->select();
                        if ($album_list) {
                            foreach ($album_list as $album) {
                                $this->recognitionAlbum($album['id']);
                            }
                        }
                    } else {
                        $this->recognitionAlbum($this->task['type_id']);

                    }
                    $this->releasLock();
                    $data = array(
                        'status' => 1
                    );
                    $faceTask->where("id = " . $this->task['id'])->save($data);
                    $this->log('任务（' . $this->task['id'] . '）结束');
                }
                sleep(5);
            }
        } catch (\Exception $e) {
            $this->releasLock();
            $this->log($e->getMessage());
        }
    }

    private function recognitionAlbum($album_id = 0)
    {
        if (!$album_id)
            return [];
        $imgModel = new ImgModel();
        $imageResM    = new FaceTaskResultModel();
        $img_list = $imgModel->where("album_id = $album_id")->select();
        foreach ($img_list as $img) {
            if (!$img['img_url'])
                continue;
            $url = 'https://image.album.iqikj.com' . $img['img_url'];
            //$score =$this->recognitionImage($this->task['img_url'], $url);
            $send = array(
                'task_id' => $this->task['id'],
                "image_url_1" => $this->task['img_url'], //"https://image.album.iqikj.com/5b841028ee232.jpg",
                "image_url_2" => $url
            );
            $this->redis->lpush(self::REDIS_LIST_FACE_LIST, json_encode($send));
//            if ($score > 30) {
//                $cell = array(
//                    'img_url' => $url,
//                    'task_id' => $this->task['id'],
//                    'score' => $score,
//                    'c_t' => time(),
//                    'u_t' => time(),
//                );
//                $imageResM->add($cell);
//            }
        }
    }

    private function recognitionImage($url1, $url2)
    {

        $request = [
            [
                'image'      => $url1,
                'image_type' => 'URL',
            ],
            [
                'image'      => $url2,
                'image_type' => 'URL',
            ],
        ];

        if ($this->face_token) {
            $request[0] = [
                'image'      => $this->face_token,
                'image_type' => 'FACE_TOKEN',
            ];
        }
        $result = $this->client->match($request);

        if (isset($result['error_code']) && $result['error_code'] == 0) {
            $score = $result['result']['score'];
            $this->face_token = !empty($result['result']['face_list'][0]['face_token']) ? $result['result']['face_list'][0]['face_token'] : '';

            if ($score > 50) {
                $this->log($score);
                return $score;
            }
        }
        return "";
    }

    private function log($msg)
    {
        echo Date("Y-m-d H:i:s") . "\t 任务（" . $this->task['id'] . "）\t". $msg . PHP_EOL;
    }

    private function shutdown()
    {
        $this->log("shutdown");
        $this->releasLock();
    }

    private function registerLosk()
    {
        $key = $this->task['id'];
        return $this->redis->setnx($this->redis_lock . $key, 'lock');
    }

    private function releasLock()
    {
        $key = $this->task['id'];
        $this->redis->del($this->redis_lock . $key);
    }


}