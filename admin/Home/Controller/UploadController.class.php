<?php
namespace Home\Controller;
use \Home\Model\ImgModel;

class UploadController extends BaseController {

    public function _initialize(){
        $this->checkAuth();
        $this->SAVE_URL     = C('SavePicUrl');
        $this->SAVE_PIC_PATH    = C('SavePicPath');
        $this->SAVE_TEMP_PATH    = C('SaveTempPath');
    }

    public function image () {
        $this->display();
    }

    /*
     * js图片剪裁 转Base64 图片上转
     * */
    public function geturl(){
        $base64_image_content = $_POST['imgBase64'];
        $w = $_POST['w'];
        $h = $_POST['h'];
        $flag = $_POST['flag'];//上转原图

        $filesize=abs(filesize($base64_image_content));
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];

            $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
            //检查扩展名
            if (in_array($type, $ext_arr) === false) {
                message("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr) . "格式。");
                exit;
            }
            //文件保存目录路径
            $save_path = $this->SAVE_PIC_PATH;
            //文件保存目录URL
            $save_url = $this->SAVE_URL;
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            $save_path = realpath($save_path).'/';

            $ymd = date("Ymd");
            $save_path .= $ymd . "/";
            $save_url .= '/'.$ymd . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }

            //新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $type;
            //移动文件
            $file_path = $save_path . $new_file_name;
            $file_url = $save_url . $new_file_name;
            //base64 生成图片
            if (file_put_contents($file_path, base64_decode(str_replace($result[1], '', $base64_image_content)))){

                $info=$this->getImageInfo($file_path);
                if(empty($flag)){
                    if($info[0]>$w && $info[1]>$h){
                        $this->resize($file_path,$w,$h);
                    }else{
                        $this->resize($file_path,$info[0],$info[1]);
                    }
                }
                echo "<script language='javascript'>";
                echo "window.close();";
                echo "window.opener.setFile('" .$file_url. "');";
                echo "</script>";
                exit;

            }
            echo "<script language='javascript'>";
            echo "alert('上传文件保存失败 请在次上传');";
            echo "</script>";
            exit;

        }else{
            echo "<script language='javascript'>";
            echo "alert('上传文件非图片 请在次上传');";
            echo "</script>";
            exit;
        }
    }

    /**
     * 缩略图主函数
     * @param string $src 图片路径
     * @param int $w 缩略图宽度
     * @param int $h 缩略图高度
     * @return mixed 返回缩略图路径
     *
     */
    function resize($src,$w,$h)
    {
        $temp=pathinfo($src);
        $name=$temp["basename"];//文件名
        $dir=$temp["dirname"];//文件所在的文件夹
        $extension=$temp["extension"];//文件扩展名
        $savepath="{$dir}/{$name}";//缩略图保存路径,新的文件名为*.thumb.jpg

        //获取图片的基本信息
        $info=$this->getImageInfo($src);
        $width=$info[0];//获取图片宽度
        $height=$info[1];//获取图片高度
        $per1=round($width/$height,2);//计算原图长宽比
        $per2=round($w/$h,2);//计算缩略图长宽比

        //计算缩放比例
        if($per1>$per2||$per1==$per2)
        {
            //原图长宽比大于或者等于缩略图长宽比，则按照宽度优先
            $per=$w/$width;
        }
        if($per1<$per2)
        {
            //原图长宽比小于缩略图长宽比，则按照高度优先
            $per=$h/$height;
        }
        $temp_w=intval($width*$per);//计算原图缩放后的宽度
        $temp_h=intval($height*$per);//计算原图缩放后的高度
        $temp_img=imagecreatetruecolor($temp_w,$temp_h);//创建画布
        $im=$this->create($src);
        imagecopyresampled($temp_img,$im,0,0,0,0,$temp_w,$temp_h,$width,$height);

        if($per1==$per2)
        {
            if(imagejpeg($temp_img,$savepath, 100)){
                imagedestroy($im);
                return true;
            }
            imagedestroy($im);
            //return false;
        }
        return false;
    }

    protected function getImageInfo($src)
    {
        return getimagesize($src);
    }

    /**
    * 创建图片，返回资源类型
    * @param string $src 图片路径
    * @return resource $im 返回资源类型
    * **/
    function create($src)
    {
        $info=$this->getImageInfo($src);
        switch ($info[2])
        {
            case 1:
                $im=imagecreatefromgif($src);
                break;
            case 2:
                $im=imagecreatefromjpeg($src);
                break;
            case 3:
                $im=imagecreatefrompng($src);
                break;
        }
        return $im;
    }

    /*百度编辑器*/
    public function baidueditor(){
        $base64_image_content = "data:".$_FILES['upfile']['type'].";base64,".base64_encode(file_get_contents($_FILES['upfile']['tmp_name']));
        $data = $this->questionnaireimg($base64_image_content);
        $result = array(
                "originalName" => $_FILES['upfile']['name'] ,
                "name" => $_FILES['upfile']['name'] ,
                "url" => $data['msg'],
                "size" => $_FILES['upfile']['size'] ,
                "type" => $_FILES['upfile']['type'] ,
                "state" => ''
        );
        $result['state'] = empty($data['error']) ? 'SUCCESS' : $data['msg'];
        echo json_encode($result);

    }

    protected function questionnaireimg($base64_image_content){
        // $base64_image_content = $_POST['imgBase64'];
        $filesize=abs(filesize($base64_image_content));
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];
            $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
            //检查扩展名
            if (in_array($type, $ext_arr) === false) {
                //message("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr) . "格式。");
                return array("error"=>1,"msg"=>'上传文件扩展名错误');
                exit;
            }
            //文件保存目录路径
            $save_path = $this->SAVE_PIC_PATH;
            //文件保存目录URL
            $save_url = $this->SAVE_URL;
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            $save_path = realpath($save_path) . '/';
            $ymd = date("Ymd");
            $save_path .= $ymd . "/";
            $save_url .= '/'.$ymd . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            //新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $type;
            //移动文件
            $file_path = $save_path . $new_file_name;
            $file_url = $save_url . $new_file_name;
            //base64 生成图片
            if (file_put_contents($file_path, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                return array("error"=>0,"msg"=>$file_url);
                exit;
            }else{
                return array("error"=>1,"msg"=>"上传文件保存失败");
                exit;
            }
        }else{
            return array("error"=>1,"msg"=>"上传文件非图片 请在次上传");
            exit;
        }

    }

    public function webuploader()
    {
        $album_id = safe_string($_REQUEST['album_id']);
        if (empty($album_id)) {
            header("HTTP/1.0 500 Internal Server Error (empty album id) ");
            exit;
        }

        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");


        // Support CORS
        // header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }


        if ( !empty($_REQUEST[ 'debug' ]) ) {
            $random = rand(0, intval($_REQUEST[ 'debug' ]) );
            if ( $random === 0 ) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }

        // header("HTTP/1.0 500 Internal Server Error");
        // exit;


        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        // usleep(5000);

        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $targetDir = $this->SAVE_TEMP_PATH;
        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        $imgUrl = '/'. date('Ymd') . '/' . $album_id;
        $uploadDir = $this->SAVE_PIC_PATH . '/'. date('Ymd') ;
        // Create upload dir
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir);
        }
        $uploadDir = $uploadDir . '/' . $album_id . '/';
        // Create upload dir
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir);
        }

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        $uploadDir = realpath($uploadDir) . '/';

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        $M = new ImgModel();
        $lastImageId = 1;
        $lastImg = $M->order('id desc')->field('id')->limit(1)->find();
        if(!empty($lastImg)){
            $lastImageId = $lastImg['id'] + 1;
        }
        $fileName = $album_id.'_'.$lastImageId.'_'.$fileName;
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $uploadPath = $uploadDir . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;


        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }


        // Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        $index = 0;
        $done = true;
        for( $index = 0; $index < $chunks; $index++ ) {
            if ( !file_exists("{$filePath}_{$index}.part") ) {
                $done = false;
                break;
            }
        }

        if ( $done ) {
            if (!$out = @fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }

            if ( flock($out, LOCK_EX) ) {
                for( $index = 0; $index < $chunks; $index++ ) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        }

        //create img
        $data['album_id'] = $album_id;
        $data['img_name'] = explode('.', $fileName)[0];
        $data['img_url'] = $imgUrl.'/'.$fileName;
        $data['create_time'] = date("Y-m-d H:i:s");
        if(false === $M->add($data)){
            die('{"jsonrpc" : "2.0", "error" : {"code": 422, "message": "加入相册失败"}, "id" : "id"}');
        }
        //update name

        // Return Success JSON-RPC response
        //die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
        die('{"jsonrpc" : "2.0", "error" : {"code": 200, "message": "成功了"}, "id" : "id"}');
    }

}