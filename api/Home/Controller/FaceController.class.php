<?php

namespace Home\Controller;

use \Home\Model\FaceTaskModel;
use \Home\Model\FaceTaskResultModel;
use \Home\Model\ImgModel;
use Home\Model\RedisModel;

class FaceController extends BaseController
{

    const APP_ID = '11728659';
    const API_KEY = 'WWKqasb1sTQYHWu9cqZT4ilu';
    const SECRET_KEY = 'VzNkFWWAk7ddiqmH7052L7q1l46TZBmx';

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

    public function test()
    {
        $result = $this->client->match([
            [
                'image'      => "42975681652f2907fc5cb62093a781d5",
                'image_type' => 'FACE_TOKEN',
            ],
            [
                'image'      => "https://image.album.iqikj.com/20180812/11/11_2071_DSC_4206.jpg",
                'image_type' => 'URL',
            ]
        ]);

        echo json_encode($result);
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
        $album_id = safe_string($_GET['album_id']);
        $space_id = safe_string($_GET['space_id']);
        $img_url  = safe_string($_GET['img_url']);

        if (!$img_url) {
            $rs['error'] = 1;
            $rs['msg']   = '请上传图片';
            $this->out_put($rs);
        }

        if (empty($album_id) && empty($space_id)) {
            $rs['error'] = 1;
            $rs['msg']   = '参数不能为空！';
            $this->out_put($rs);
        }

        $faceTask = new FaceTaskModel();
        $data     = [
            'type'    => $album_id ? 0 : 1,
            'type_id' => $album_id ? $album_id : $space_id,
            'img_url' => $img_url,
            'status'  => 0,
            'c_t'     => time(),
            'u_t'     => time(),
        ];
        $res      = $faceTask->add($data);
        if (!$res) {
            $rs['error'] = 1;
            $rs['msg']   = '识别失败';
            $this->out_put($rs);
        }

        $rs['error'] = 0;
        $rs['msg']   = 'ok';
        $rs['data']  = $res;

        $this->out_put($rs);
    }

    public function recognition()
    {
        try {
            $faceTask = new FaceTaskModel();
            register_shutdown_function([$this, 'shutdown']);
            while (true) {
                $task_list = $faceTask->where('status = 0')->select();
                if (!$task_list) {
                    sleep(5);
                    continue;
                }

                foreach ($task_list as $task) {
                    $this->task = $task;
                    $this->face_token = '';
                    if (!$this->registerLosk()) {
                        continue;
                    };
                    $this->log('开始');
                    if ($this->task['type'] == 1) {
                        //TODO
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
            $score =$this->recognitionImage($this->task['img_url'], $url);
            if ($score > 30) {
                $cell = array(
                    'img_url' => $url,
                    'task_id' => $this->task['id'],
                    'score' => $score,
                    'c_t' => time(),
                    'u_t' => time(),
                );
                $imageResM->add($cell);
            }
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