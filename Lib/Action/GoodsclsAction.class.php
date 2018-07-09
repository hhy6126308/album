<?php
class GoodsclsAction extends CoreAction{
	
	Public function _initialize(){
		B('AuthCheck');
	}

	public function index(){
		echo session('mp_authId');
		import("ORG.Mine.pagination");    
        if($_GET['page']){
            $currpage = intval($_GET['page']);
        }else{
            $currpage = 1;
        }

        $ArticleM = new GoodsModel();

        $count = $ArticleM->getArticleListCount('iq_links');
        $count = empty($count) ? 0 : $count;
        $pagenum="20";
        $num = ceil($count/$pagenum);
        $start	= $pagenum * ($currpage-1);
        $limit = $start.",".$pagenum;
        $Page = new pagination($num,$currpage,"./?s=Links&page=\$p");
        $data = $ArticleM->getArticleList('iq_links',$limit);

        $this->assign( 'page',$Page->PageLinks2());
        $this->assign( 'list', $data );
        $this->assign( 'count', $count );
        $npa = array(
            array('url'=>'?s=index&a=welcome','name'=>'管理首页'),
            array('url'=>'#','name'=>"友情链接")
        );
        $this->assign('npa', $npa);
        $this->display();
	}
	
	public function info(){
		try{
			$ac = htmlspecialchars(addslashes(trim($_GET['ac'])));
			$db= new ArticleModel();
			switch( $ac ){
				case 'add' :
					$data['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
					$data['n_url'] = htmlspecialchars(addslashes(trim($_POST['url'])));
					$data['orderid'] = htmlspecialchars(addslashes(trim($_POST['orderid'])));
					$res = $db->getSnameCid('iq_links');
					if(!$res){
						message('非法操作分类！','?s=Links');
						exit;
					}
					$data['cid'] = $res['id'];
					$data['ctime'] = Date("Y-m-d H:i:s");
					if(!$data['n_url'] || !$data['title']) throw new Exception("请填写完成后再提交！！");
					if(false===$db->add($data)){
						echo $db->getLastSql();
						die;
						throw new Exception("添加失败！");
					}
					message("添加成功","?s=Links");
					break;
				case 'edit' :
					$id = htmlspecialchars(addslashes(trim($_GET['id'])));
					if(!$id) throw new Exception("无效ID");
					$data['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
					$data['n_url'] = htmlspecialchars(addslashes(trim($_POST['url'])));
					$data['orderid'] = htmlspecialchars(addslashes(trim($_POST['orderid'])));
					if(false==$db->where("id=$id")->save($data)){
						throw new Exception("修改失败！");
					}
					message("修改成功","?s=Links");
					break;
				case 'del' :
					$id = htmlspecialchars(addslashes(trim($_GET['id'])));
					if(!$id) throw new Exception("无效ID");
					if(false==$db->where("id=$id")->save(array("isdel"=>1))){
						throw new Exception("删除失败！");
					}
					message("删除成功","?s=Links");
					break;
				default:
					throw new Exception("未知操作！");
					break;
			}
			
		}catch(Exception $e){
			message($e->getMessage(),"?s=Links");
		}
	} 

	
}
?>