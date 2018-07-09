<?php
/**
 * 商口
 * @author hhy
 * @createTime 2017-10-30 上午11:39:53
 */
class GoodsAction extends Action{
	protected $class = array(
			'iq_news' => '新闻动态',
			'iq_act' =>'中心活动',
			'iq_bussiness' => '业务能力',
			'iq_case' => '成功案例',
			'iq_act' => '中心活动',
			'iq_files' => '文件下载',
			'iq_product' =>'产品中心'
			); 
    public function _initialize(){
        B('AuthCheck');
    }

    /**
     * 文章 列表页
     **/
    public function index(){
        import("ORG.Mine.pagination");
        if($_GET['page']){
            $currpage = intval($_GET['page']);
        }else
        {
            $currpage = 1;
        }
        $sname = htmlspecialchars(addslashes(trim($_GET['sname'])));
		$sname = $sname?$sname:"iq_news";
		if($sname=='iq_files'){
			redirect("?s=Files");
		}
        $ArticleM = new ArticleModel();

        $count = $ArticleM->getArticleListCount($sname);
        $count = empty($count) ? 0 : $count;
        $pagenum="20";
        $num = ceil($count/$pagenum);
        $start	= $pagenum * ($currpage-1);
        $limit = $start.",".$pagenum;
        $Page = new pagination($num,$currpage,"./?s=Article&page=\$p");
        $data = $ArticleM->getArticleList($sname,$limit);

        $this->assign( 'page',$Page->PageLinks2());
        $this->assign( 'list', $data );
        $this->assign( 'sname', $sname );
        $this->assign( 'count', $count );
        $this->assign('page_title',$this->class[$sname]);
        $npa = array(
            array('url'=>'?s=index&a=welcome','name'=>'管理首页'),
            array('url'=>'#','name'=>$this->class[$sname])
        );
        $this->assign('npa', $npa);
        $this->display();
    }
    
    /**
     * 文章 分类
     **/
    public function cls(){
    	$sname = htmlspecialchars(addslashes(trim($_GET['sname'])));
    	$ac = htmlspecialchars(addslashes(trim($_GET['ac'])));
    	$clsM = new ArticleClsModel();
    	switch($ac){
    		case "edit":
    			$ArticleM = new ArticleModel();
    			if($_POST){
    				$id = htmlspecialchars(addslashes(trim($_GET['id'])));
    				$data['c_name']=htmlspecialchars(addslashes(trim($_POST['c_name'])));
    				$data['c_orderid']=htmlspecialchars(addslashes(trim($_POST['c_orderid'])));	
    				if($clsM->where("id='{$id}'")->save($data)===false){
    					message('操作失败。','?s=Article&a=cls&sname='.$sname);
    					exit;
    				}
    				message('操作成功。','?s=Article&a=cls&sname='.$sname);
    				exit;
    			}
    			break;
    		case "add":
    			if($_POST){
    				$data['c_name']=htmlspecialchars(addslashes(trim($_POST['c_name'])));
    				$data['c_orderid']=htmlspecialchars(addslashes(trim($_POST['c_orderid'])));	
    				$data['c_short_name'] = $sname;
    				if(!$clsM->add($data)){
    					message('操作失败。','?s=Article&a=cls&sname='.$sname);
    					exit;
    				}
    				message('操作成功。','?s=Article&a=cls&sname='.$sname);
    				exit;
    			}
    			break;
    		case "del":
    			$id = htmlspecialchars(addslashes(trim($_GET['id'])));
    			if($clsM->where("id=$id")->delete()===false){
    				message('操作失败。','?s=Article&a=cls&sname='.$sname);
    				exit;
    			}
    			message('操作成功。','?s=Article&a=cls&sname='.$sname);
    			exit;
    			break;
    	}
    	$data = $clsM->where("c_short_name ='{$sname}' ")->order('c_orderid desc,id desc')->select();
    	$this->assign( 'list', $data );
    	$this->assign( 'sname', $sname );
    	$this->assign('page_title',$this->class[$sname]);
    	$npa = array(
    			array('url'=>'?s=index&a=welcome','name'=>'管理首页'),
    			array('url'=>'?s=Article&sname='.$sname,'name'=>$this->class[$sname]),
    			array('url'=>'#','name'=>$this->class[$sname].'分类')
    	);
    	$this->assign('npa', $npa);
    	$this->display();
    }

    /**
     *  文章编辑页面
     */

     public function info(){
        $id = htmlspecialchars(addslashes(trim($_GET['id'])));
        $sname = htmlspecialchars(addslashes(trim($_GET['sname'])));
		$sname = $sname?$sname:"iq_news";
		$this->assign( 'sname', $sname );
        $ac = htmlspecialchars(addslashes(trim($_GET['ac'])));
        $clsM = new ArticleClsModel();
        $cls = $clsM->where("c_short_name ='{$sname}' ")->order('c_orderid desc,id desc')->select();
        $this->assign("cls",$cls);
        $npa = array(
        	array('url'=>'?s=index&a=welcome','name'=>'管理首页'),
            array('url'=>'/?s=Article&sname='.$sname,'name'=>$this->class[$sname])
        );

         switch($ac){
             case "edit":
                 $ArticleM = new ArticleModel();
                 if($_POST){
                     $data['title']=htmlspecialchars(addslashes(trim($_POST['title'])));
                     $data['source']=htmlspecialchars(addslashes(trim($_POST['source'])));
                     $data['content']=$_POST['content'];
                     $data['abstract']=htmlspecialchars(addslashes(trim($_POST['abstract'])));
                     $data['aid']=htmlspecialchars(addslashes(trim($_POST['aid'])));
                     $data['orderid']=htmlspecialchars(addslashes(trim($_POST['orderid'])));
                     $data['n_img']=htmlspecialchars(addslashes(trim($_POST['n_img'])));
                     $data['utime']=date("Y-m-d H:i:s");
                     $id=htmlspecialchars(addslashes(trim($_POST['id'])));

                     //$data['banner'] = $this->myserialize($_POST['banner']);

                     if($ArticleM->where("id=$id")->save($data)===false){
                         message('操作失败。','?s=Article&sname='.$sname);
                         exit;
                     }

                     message('操作成功。','?s=Article&sname='.$sname);
                     exit;
                 }
                 $npa[2] = array('url'=>'#','name'=>'编辑文章');
                 $info = $ArticleM->getArticleInfo($id);

                 $this->assign( 'data', $info );

             break;
             case "add":
                 if($_POST){
                     $ArticleM = new ArticleModel();
                     $res = $ArticleM->getSnameCid($sname);
                     if(!$res){
                     	message('非法操作分类！','?s=Article&sname='.$sname);
                     	exit;
                     }
                     $data['cid'] = $res['id'];
                     $data['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
                     $data['source'] = htmlspecialchars(addslashes(trim($_POST['source'])));
                     $data['content'] = $_POST['content'];
                     $data['aid']=htmlspecialchars(addslashes(trim($_POST['aid'])));
                     $data['abstract'] = htmlspecialchars(addslashes(trim($_POST['abstract'])));
                     $data['orderid']=htmlspecialchars(addslashes(trim($_POST['orderid'])));
                     $data['n_img'] = htmlspecialchars(addslashes(trim($_POST['n_img'])));
                     $data['ctime'] = $data['utime'] = date("Y-m-d H:i:s");
                     $data['isdel'] = htmlspecialchars(addslashes(trim($_POST['isdel'])));
                     if(!$ArticleM->add($data)){
                         message('操作失败。','?s=Article&sname='.$sname);
                         exit;
                     }
                     message('操作成功。','?s=Article&sname='.$sname);
                     exit;
                 }

                 $npa[2] = array('url'=>'#','name'=>'发布新文章');
             break;
             case "state":
                 $data['isdel'] = htmlspecialchars(addslashes(trim($_GET['state'])));
                 $ArticleM = new ArticleModel();
                 if($ArticleM->where("id=$id")->save($data)===false){
                     message('操作失败。',"");
                     exit;
                 }
                 message('操作成功。','?s=Article&sname='.$sname);
                 exit;
                 break;
         }
         $this->assign('npa', $npa);
         $this->assign( 'ac', $ac );
         $this->display();
     }

}

































