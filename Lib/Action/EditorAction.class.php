<?php

/**
 * 公共上传
 * @author kitter
 *
 */
class EditorAction extends Action {
	private $SAVE_PATH	= null;
	private $SAVE_URL	= null;
	private $SAVE_CDN_PATH	= null;
	Public function _initialize(){
		//B('AuthCheck');
		//$this->SAVE_PATH	= WWWROOT."/st/upload/";
		$this->SAVE_PATH	="../wwwroot/st/public/";
		$this->SAVE_URL		= C('SavePicUrl')."/public/image/";
		//$this->SAVE_URL		= "http://127.0.0.1:8085//public/image/";
		import("ORG.Mine.UploadDlKhdCdn");
	}
	
	public function changePath($savePath,$saveCdnPath){
		if(!empty($saveCdnPath)){
			$this->SAVE_CDN_PATH	= $saveCdnPath;
		}
		if(!empty($savePath)){
			$this->SAVE_PATH	= $savePath;
		}
	}
	/**
	 * 上传文件
	 */
	public function upload(){
		vendor("editor.JSON");
		//文件保存目录路径
		$save_path = $this->SAVE_PATH;
		//文件保存目录URL
		$save_url = $this->SAVE_URL;

		if (!file_exists($save_path)) {
			mkdir($save_path);
		}
		//定义允许上传的文件扩展名
		$ext_arr = array(
				'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
				'flash' => array('swf', 'flv'),
				'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
				'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
		);
		//最大文件大小
		//二次元撸美图照片有超过1MB，原来设置1000000
		$max_size = 2000000;
		
		$save_path = realpath($save_path) . '/';
		//PHP上传失败
		if (!empty($_FILES['imgFile']['error'])) {
			switch($_FILES['imgFile']['error']){
				case '1':
					$error = '超过允许的大小。';
					break;
				case '2':
					$error = '超过表单允许的大小。';
					break;
				case '3':
					$error = '图片只有部分被上传。';
					break;
				case '4':
					$error = '请选择图片。';
					break;
				case '6':
					$error = '找不到临时目录。';
					break;
				case '7':
					$error = '写文件到硬盘出错。';
					break;
				case '8':
					$error = 'File upload stopped by extension。';
					break;
				case '999':
				default:
					$error = '未知错误。';
			}
			$this->alert($error);
		}
		
		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES['imgFile']['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES['imgFile']['tmp_name'];
			//文件大小
			$file_size = $_FILES['imgFile']['size'];
			//检查文件名
			if (!$file_name) {
				$this->alert("请选择文件。");
			}
			//检查目录
			if (@is_dir($save_path) === false) {
				$this->alert("上传目录不存在。");
			}
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				$this->alert("上传目录没有写权限。");
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				$this->alert("上传失败。");
			}
			//检查文件大小
			if ($file_size > $max_size) {
				$this->alert("上传文件大小超过限制。");
			}
			//检查目录名
			$dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
			if (empty($ext_arr[$dir_name])) {
				$this->alert("目录名不正确。");
			}
			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
				$this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
			}
			//创建文件夹
			if ($dir_name !== '') {
				$save_path .= $dir_name . "/";
				//$save_url .= $dir_name . "/";
				if (!file_exists($save_path)) {
					mkdir($save_path);
				}
			}
			$ymd = date("Ymd");
			$save_path .= $ymd . "/";
			$save_url .= $ymd . "/";
			if (!file_exists($save_path)) {
				mkdir($save_path);
			}
			//新文件名
			$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
			//移动文件
			$file_path = $save_path . $new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				$this->alert("上传文件失败。");
			}
			@chmod($file_path, 0644);
			$file_url = $save_url . $new_file_name;
			//上传到cdn
			header('Content-type: text/html; charset=UTF-8');
			$json = new Services_JSON();
			//得到图片高宽
			//$size = getimagesize($file_url);
			//echo $json->encode(array('error' => 0, 'url' => $file_url,'width'=>$size[0],"height"=>$size[1]));
			echo $json->encode(array('error' => 0, 'url' => $file_url,'width' => $file_url));
			exit;
		}
		
		
	}
	private function alert($msg) {
			header('Content-type: text/html; charset=UTF-8');
			$json = new Services_JSON();
			echo $json->encode(array('error' => 1, 'message' => $msg));
			exit;
	}
	/**
	 * 文件管理
	 */
	public function manager()
	{
		vendor("editor.JSON");
		$php_path = dirname(__FILE__) . '/';
		$php_url = dirname($_SERVER['PHP_SELF']) . '/';
		
		//根目录路径，可以指定绝对路径，比如 /var/www/attached/
		$root_path = $this->SAVE_PATH;
// 		$root_path = $php_path . '../attached/';
		//根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
		$root_url = $this->SAVE_URL;
// 		$root_url = $php_url . '../attached/';
		//图片扩展名
		$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
		
		//目录名
		$dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
		if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
			echo "Invalid Directory name.";
			exit;
		}
		if ($dir_name !== '') {
			$root_path .= $dir_name . "/";
			$root_url .= $dir_name . "/";
			if (!file_exists($root_path)) {
				mkdir($root_path);
			}
		}
		
		//根据path参数，设置各路径和URL
		if (empty($_GET['path'])) {
			$current_path = realpath($root_path) . '/';
			$current_url = $root_url;
			$current_dir_path = '';
			$moveup_dir_path = '';
		} else {
			$current_path = realpath($root_path) . '/' . $_GET['path'];
			$current_url = $root_url . $_GET['path'];
			$current_dir_path = $_GET['path'];
			$moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
		}
		//echo realpath($root_path);
		//排序形式，name or size or type
		$order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);
		
		//不允许使用..移动到上一级目录
		if (preg_match('/\.\./', $current_path)) {
			echo 'Access is not allowed.';
			exit;
		}
		//最后一个字符不是/
		if (!preg_match('/\/$/', $current_path)) {
			echo 'Parameter is not valid.';
			exit;
		}
		//目录不存在或不是目录
		if (!file_exists($current_path) || !is_dir($current_path)) {
			echo 'Directory does not exist.';
			exit;
		}
		
		//遍历目录取得文件信息
		$file_list = array();
		if ($handle = opendir($current_path)) {
			$i = 0;
			while (false !== ($filename = readdir($handle))) {
				if ($filename{0} == '.') continue;
				$file = $current_path . $filename;
				if (is_dir($file)) {
					$file_list[$i]['is_dir'] = true; //是否文件夹
					$file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
					$file_list[$i]['filesize'] = 0; //文件大小
					$file_list[$i]['is_photo'] = false; //是否图片
					$file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
				} else {
					$file_list[$i]['is_dir'] = false;
					$file_list[$i]['has_file'] = false;
					$file_list[$i]['filesize'] = filesize($file);
					$file_list[$i]['dir_path'] = '';
					$file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					$file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
					$file_list[$i]['filetype'] = $file_ext;
				}
				$file_list[$i]['filename'] = $filename; //文件名，包含扩展名
				$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
				$i++;
			}
			closedir($handle);
		}
		
		
		usort($file_list, 'cmp_func');

		$result = array();
		//相对于根目录的上一级目录
		$result['moveup_dir_path'] = $moveup_dir_path;
		//相对于根目录的当前目录
		$result['current_dir_path'] = $current_dir_path;
		//当前目录的URL
		$result['current_url'] = $current_url;
		//文件数
		$result['total_count'] = count($file_list);
		//文件列表数组
		$result['file_list'] = $file_list;
		//输出JSON字符串
		header('Content-type: application/json; charset=UTF-8');
		$json = new Services_JSON();
		echo $json->encode($result);
	}

	/*
	 * js图片剪裁 转Base64 图片上转
	 * */
	public function mainsailsimg(){

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
            $save_path = $this->SAVE_PATH;
            //文件保存目录URL
            $save_url = $this->SAVE_URL;
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            $save_path = realpath($save_path) . '/';
            $dir_name='image';
            //创建文件夹
            if ($dir_name !== '') {
                $save_path .= $dir_name . "/";
                //$save_url .= $dir_name . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
            }
            $ymd = date("Ymd");
            $save_path .= $ymd . "/";
            $save_url .= $ymd . "/";
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
            message("上传文件保存失败 请在次上传");
            exit;

        }else{
            message("上传文件非图片 请在次上传");
            exit;
        }

    }

    public function questionnaireimg($base64_image_content){

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
            $save_path = $this->SAVE_PATH;
            //文件保存目录URL
            $save_url = $this->SAVE_URL;
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            $save_path = realpath($save_path) . '/';
            $dir_name='image';
            //创建文件夹
            if ($dir_name !== '') {
                $save_path .= $dir_name . "/";
                //$save_url .= $dir_name . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
            }
            $ymd = date("Ymd");
            $save_path .= $ymd . "/";
            $save_url .= $ymd . "/";
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

    /**
     * 缩略图主函数
     * @param string $src 图片路径
     * @param int $w 缩略图宽度
     * @param int $h 缩略图高度
     * @return mixed 返回缩略图路径
     * **/

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
//        if($per1<$per2)
//        {
//            imagejpeg($temp_img,$savepath, 100);
//            imagedestroy($im);
//            return addBg($savepath,$w,$h,"h");
//            //高度优先，在缩放之后宽度不足的情况下补上背景
//        }
        //        if($per1>$per2)
//        {
//            imagejpeg($temp_img,$savepath, 100);
//            imagedestroy($im);
//            return addBg($savepath,$w,$h,"w");
//            //宽度优先，在缩放之后高度不足的情况下补上背景
//        }
    }
    function getImageInfo($src)
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

	
}