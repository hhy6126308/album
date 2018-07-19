<?php
namespace Home\Controller;
use \Home\Model\ImgModel;

class DownloadController extends BaseController {

    public function _initialize(){
        $this->SAVE_PIC_PATH    = C('SavePicPath');
        $this->SAVE_TEMP_PATH    = C('SaveTempPath');
    }

    public function index()
    {
        try {
            $ids = safe_string($_GET['ids']);
            $album_id = safe_string($_GET['album_id']);
            if (empty($ids))
                return false;
            $ids = explode(',', $ids);
            $M = new ImgModel();
            $images = $M->where(['id' => ['in', $ids]])->select();
            $fileNames = array_column($images, 'img_url');
            $tmpPath = $this->SAVE_TEMP_PATH."/".rand(10000, 99999).'.zip';
            if(file_exists($tmpPath)){
                unlink($tmpPath);
            }
            $zip = new \ZipArchive();
            if($zip->open($tmpPath, \ZipArchive::CREATE)=== TRUE){
                foreach ($fileNames as $fileName){
                    $file = $this->SAVE_PIC_PATH . $fileName;
                    if(is_file($file)){
                        $file_info_arr= pathinfo($file);
                        $zip->addFile($file, $file_info_arr['basename']);
                    }
                }
            }
            $zip->close();

            ob_end_clean();
            header("Cache-Control:");
            header("Cache-Control: public");
            #header("Content-Type: application/force-download");
            #header("Content-Transfer-Encoding: binary");
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename='.$album_id.'_'.rand(10000, 99999).'.zip');
            header("Accept-Ranges: bytes");
            #header('Content-Length: '.filesize($filename));
            error_reporting(0);

            $size = filesize($tmpPath);
            //如果有$_SERVER['HTTP_RANGE']参数
            $range = 0;
            if (isset ($_SERVER['HTTP_RANGE'])) {
                list ($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
                //if yes, download missing part
                str_replace($range, "-", $range); //这句干什么的呢。。。。
                $size2      = $size - 1; //文件总字节数
                $new_length = $size2 - $range; //获取下次下载的长度
                header("HTTP/1.1 206 Partial Content");
                header("Content-Length: $new_length"); //输入总长
                header("Content-Range: bytes $range$size2/$size"); //Content-Range: bytes 4908618-4988927/4988928 95%的时候
            } else {
                //第一次连接
                $size2 = $size - 1;
                header("Content-Range: bytes 0-$size2/$size"); //Content-Range: bytes 0-4988927/4988928
                header("Content-Length: " . $size); //输出总长
            }
            //打开文件
            $fp = fopen("$tmpPath", "rb");
            //设置指针位置
            fseek($fp, $range);
            //虚幻输出
            while (!feof($fp)) {
                //设置文件最长执行时间
                set_time_limit(0);
                print (fread($fp, 1024)); //输出文件
                flush(); //输出缓冲
                ob_flush();
                usleep(1000);
            }
            fclose($fp);
            unlink($tmpPath);
            exit;
        } catch ( \Think\Exception $e ) {
            $this->error($e->getMessage(), '/Album', 3);
        }
    }

    public function indextest()
    {
        $res = array(
            array(
                'img_path'=>"/static/images/close.png",
            ),
            array(
                'img_path'=>"/static/images/closed.gif",
            ),
            array("img_path" =>"/static/WechatIMG73.jpeg"),
            array("img_path" =>"/static/1111.dmg"),
        );

        //创建压缩包的路径
        $time = "default";
        $filename = $_SERVER['DOCUMENT_ROOT'].'/download/'.$time.'.zip';

//        $zip = new \ZipArchive;
//
//        $zip->open($filename,$zip::CREATE);
//        //往压缩包内添加目录
//        $zip->addEmptyDir('images');
//
//        foreach ($res as $value) {
//            $fileData = file_get_contents($_SERVER['DOCUMENT_ROOT'].$value['img_path']);
//            if ($fileData) {
//                $add = $zip->addFromString('images/'.$value['img_path'], $fileData);
//            }
//        }
//
//        $zip->close();
        //打开文件
        //下载文件
       // ob_end_clean();
        header("Cache-Control:");
        header("Cache-Control: public");
        #header("Content-Type: application/force-download");
        #header("Content-Transfer-Encoding: binary");
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename='.$time.'.zip');
        header("Accept-Ranges: bytes");
        #header('Content-Length: '.filesize($filename));
        error_reporting(0);

        $size = filesize($filename);
        //如果有$_SERVER['HTTP_RANGE']参数
        $range = 0;
        if (isset ($_SERVER['HTTP_RANGE'])) {
            list ($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
            //if yes, download missing part
            str_replace($range, "-", $range); //这句干什么的呢。。。。
            $size2      = $size - 1; //文件总字节数
            $new_length = $size2 - $range; //获取下次下载的长度
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length"); //输入总长
            header("Content-Range: bytes $range$size2/$size"); //Content-Range: bytes 4908618-4988927/4988928 95%的时候
        } else {
            //第一次连接
            $size2 = $size - 1;
            header("Content-Range: bytes 0-$size2/$size"); //Content-Range: bytes 0-4988927/4988928
            header("Content-Length: " . $size); //输出总长
        }
        //打开文件
        $fp = fopen("$filename", "rb");
        //设置指针位置
        fseek($fp, $range);
        //虚幻输出
        while (!feof($fp)) {
            //设置文件最长执行时间
            set_time_limit(0);
            print (fread($fp, 1024)); //输出文件
            flush(); //输出缓冲
            ob_flush();
            usleep(1000);
        }
        fclose($fp);
        exit;

//        $zip = new \ZipArchive;
//        //压缩文件名
//        $filename = 'download.zip';
//        //新建zip压缩包
//        $zip->open($filename,\ZipArchive::OVERWRITE);
//        //把图片一张一张加进去压缩
//        foreach ($images as $key => $value) {
//            $zip->addFile($value);
//        }
//        //打包zip
//        $zip->close();
//
//        //可以直接重定向下载
//       # header('Location:'.$filename);
//
//        //或者输出下载
//        header("Cache-Control: public");
//        header("Content-Description: File Transfer");
//        header('Content-disposition: attachment; filename='.basename($filename)); //文件名
//        header("Content-Type: application/force-download");
//        header("Content-Transfer-Encoding: binary");
//        header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小
//        readfile($filename);
    }

    /*
     * 对于没有防盗链的图片
     * $url 图片地址
     * $filename 图片保存地址
     * return 返回下载的图片路径和名称,图片大小
    */
    function GrabImage($url, $filepath, $filename = "") {
        if ($url == "") return false;
        $ext = strrchr($url, ".");
        if ($filename == "") {
            if ($ext != ".gif" && $ext != ".jpg" && $ext != ".png") return false;
            $filename = date("YmdHis");
        }
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img); // 图片大小
        !is_dir(getcwd() . $filepath) ? mkdir(getcwd() . $filepath) : null; //生成文件夹
        $fp2 = fopen(getcwd() . $filepath . $filename . $ext, "a");
        fwrite($fp2, $img);
        fclose($fp2);
        return array(
            $filepath . $filename . $ext,
            $size
        );
    }

}