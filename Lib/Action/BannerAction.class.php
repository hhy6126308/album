<?php
class BannerAction extends Action{
	
	Public function _initialize(){
		B('AuthCheck');
	}

	public function index(){
		$nowpage = $_GET['page']?$_GET['page']:1;
		$casedb= new BannerModel();
		import('ORG.Util.Page');
		
		$count=$casedb->get_total_num();
		$list=$casedb->get_list($nowpage);
		
		import("ORG.Mine.pagination");
		$Reques_uri	= $_SERVER['REQUEST_URI'];
		$Reques_uri	= preg_replace("/&?page=?[\d]{0,}/", "", $Reques_uri);
		$pagenum = 20;
		$num = ceil($count/$pagenum);
		$page  = new pagination($num,$nowpage,$Reques_uri."&page=\$p");
		$this->assign("page",$page->PageLinks2());
		$this->assign("count",$count);
		$this->assign("list",$list);
		
		$dh = array(
				array("name"=>"用户反馈列表","url"=>"?s=Feedback")
		);
		$this->assign("npa",$dh);
		$this->display('index');
	}
	
	public function info(){
		try{
			$ac = htmlspecialchars(addslashes(trim($_GET['ac'])));
			$db= new BannerModel();
			switch( $ac ){
				case 'add' :
					$data['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
					$data['url'] = htmlspecialchars(addslashes(trim($_POST['url'])));
					$data['img'] = htmlspecialchars(addslashes(trim($_POST['img'])));
					$data['orderid'] = htmlspecialchars(addslashes(trim($_POST['orderid'])));
					$data['ctime'] = Date("Y-m-d H:i:s");
					if(!$data['url'] || !$data['img'] || !$data['title']) throw new Exception("请填写完成后再提交！！");
					if(false==$db->add($data)){
						throw new Exception("添加失败！");
					}
					message("添加成功","?s=Banner");
					break;
				case 'edit' :
					$id = htmlspecialchars(addslashes(trim($_GET['id'])));
					if(!$id) throw new Exception("无效ID");
					$data['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
					$data['url'] = htmlspecialchars(addslashes(trim($_POST['url'])));
					$data['img'] = htmlspecialchars(addslashes(trim($_POST['img'])));
					$data['orderid'] = htmlspecialchars(addslashes(trim($_POST['orderid'])));
					if(false==$db->where("id=$id")->save($data)){
						throw new Exception("修改失败！");
					}
					message("修改成功","?s=Banner");
					break;
				case 'del' :
					$id = htmlspecialchars(addslashes(trim($_GET['id'])));
					if(!$id) throw new Exception("无效ID");
					if(false==$db->where("id=$id")->save(array("isdel"=>1))){
						throw new Exception("删除失败！");
					}
					message("删除成功","?s=Banner");
					break;
				default:
					throw new Exception("未知操作！");
					break;
			}
			
		}catch(Exception $e){
			message($e->getMessage(),"?s=Banner");
		}
	} 

	
}
?>