<?php

namespace Home\Controller;

use Home\Model\AlbumModel;
use \Home\Model\FaceTaskModel;
use \Home\Model\FaceTaskResultModel;
use \Home\Model\ImgModel;
use Home\Model\RedisModel;

class FaceAiController extends BaseController
{

    private $redis;

    const REDIS_LIST_FACE_LIST = 'redis_list_face_list';

    public function __construct()
    {
        $this->redis  = new RedisModel();
    }

    /**
     * exec
     */
    public function exec()
    {
        while (true) {
            $data = $this->redis->rpop(self::REDIS_LIST_FACE_LIST);
            if (!$data) {
                sleep(1);
                continue;
            }
            $data = json_decode($data, true);
            if (!$data) {
                $this->log("decode error");
                continue;
            }

            $task_id = $data['task_id'];

            if (empty($task_id)) {
                $this->log("no task_id");
                continue;
            }

            while (true){
                if ($this->redis->setnx("face_task_count_" . $task_id, "lock")) {
                    $info = $this->redis->get("face_task_" . $task_id);
                    $info = json_decode($info, true);
                    $info['task_num'] = $info['task_num'] + 1;
                    $this->redis->set("face_task_" . $task_id, json_encode($info));
                    $this->redis->del("face_task_count_" . $task_id);
                    break;
                } else {
                    usleep(10000);
                    continue;
                }

            }


            $req = array(
                "type"        => 0,
                "image_url_1" => $data['image_url_1'], //"https://image.album.iqikj.com/5b841028ee232.jpg",
                "image_url_2" => $data['image_url_2']//"https://image.album.iqikj.com/20180812/11/11_2069_DSC_4192.jpg"
            );

            $res = $this->send($req);
            $imageResM    = new FaceTaskResultModel();
            if (isset($res['errno'])) {
                $score = $res['confidence'];
                if ($score >= 80) {
                    $this->log("ok");
                    $cell = array(
                        'img_url' => $data['image_url_2'],
                        'task_id' => $task_id,
                        'score' => $score,
                        'c_t' => time(),
                        'u_t' => time(),
                    );
                    $imageResM->add($cell);
                } else {
                    $this->log("score:" . $score);
                }
            } else {
                $this->log(json_encode($res));
            }

        }
    }

    private function log($msg)
    {
        echo Date("Y-m-d H:i:s") . "\t". $msg . PHP_EOL;
    }

    private function send($req)
    {
        $akId = "LTAIhQ2xf5Txoi0b";
        $akSecret = "sgUNel1ZENMeAaxD0cxQjJzoYiacdi";
        //更新api信息
        $url = "https://dtplus-cn-shanghai.data.aliyuncs.com/face/verify";
        $options = array(
            'http' => array(
                'header' => array(
                    'accept'=> "application/json",
                    'content-type'=> "application/json",
                    'date'=> gmdate("D, d M Y H:i:s \G\M\T"),
                    'authorization' => ''
                ),
                'method' => "POST", //可以是 GET, POST, DELETE, PUT
                'content' => json_encode($req) //如有数据，请用json_encode()进行编码
            )
        );
        $http = $options['http'];
        $header = $http['header'];
        $urlObj = parse_url($url);
        if(empty($urlObj["query"]))
            $path = $urlObj["path"];
        else
            $path = $urlObj["path"]."?".$urlObj["query"];
        $body = $http['content'];
        if(empty($body))
            $bodymd5 = $body;
        else
            $bodymd5 = base64_encode(md5($body,true));
        $stringToSign = $http['method']."\n".$header['accept']."\n".$bodymd5."\n".$header['content-type']."\n".$header['date']."\n".$path;
        $signature = base64_encode(
            hash_hmac(
                "sha1",
                $stringToSign,
                $akSecret, true));
        $authHeader = "Dataplus "."$akId".":"."$signature";
        $options['http']['header']['authorization'] = $authHeader;
        $options['http']['header'] = implode(
            array_map(
                function($key, $val){
                    return $key.":".$val."\r\n";
                },
                array_keys($options['http']['header']),
                $options['http']['header']));
        $context = stream_context_create($options);
        $file = file_get_contents($url, false, $context);
        return json_decode($file, true);
    }


}