<?php

/**
 * 公共上传
 * @author kitter
 *
 */
class UploadAction extends Action {

	Public function _initialize(){
		B('AuthCheck');
		$this->SAVE_CDN_PATH	= "/ios/upload";
		//$this->SAVE_PATH	= WWWROOT."/st/upload/";
		$this->SAVE_PATH	="../wwwroot/st/public/";
		$this->SAVE_URL		= C('SavePicUrl');
		//$this->SAVE_URL		= "http://127.0.0.1:8085//public/image/";
		import("ORG.Mine.UploadDlKhdCdn");
	}
	public function index(){
		$this->display('rest');
	}

	public function uploadfile(){
		//$name = $_FILES['file'];
		import("ORG.Net.UploadFile");
		$upload = new UploadFile();
		$upload->maxSize = 30971520; //上传大小20M
		$upload->allowExts = array("jpg","gif","png","jpeg","pdf","txt","rar");
		$upload->savePath = "../wwwroot/st/public/";
		//$upload->saveRule = md5($name);
		if(!$upload->upload()){
			$error = $upload->getErrorMsg();
			if(strstr($error, '文件已经存在！')){
				$filename = explode("/",$error);
				echo C('SavePicUrl')."public/".$filename[count($filename)-1];
			}else {
				echo $upload->getErrorMsg();
			}
			
		}else{
			$info = $upload->getUploadFileInfo();
			echo C('SavePicUrl')."/public/".$info[0]['savename'];
		}
	}
	
	public function getfile(){
		if($_FILES['attachment']['tmp_name']){
			$str=file_get_contents($_FILES['attachment']['tmp_name']);
			$order=array("\r\n","\n","\r");
			$str = str_replace($order, ";", $str);
			echo $str;
		}else{
			echo "no";
		}
	}
	
	public function plug_upload()
	{
		$toobj	= $_GET['toobj'];
		if($_FILES['file']['name']!=''){
			$path = UploadModel::upload($_FILES);
			$this->assign('path',$path);
		}
		$this->assign('toobj',$toobj);
		$this->assign('dir',$_GET['dir']);
		$this->assign('new_name',$_GET['new_name']);
		//  	 	app_tpl::assign('obj',$obj);
		$this->display('plug_upload');
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
			$save_url .= '/public/image/'.$ymd . "/";
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
}