<?php
namespace Home\Controller;
use Think\Controller;

class BaseController extends Controller {

    protected $npc = array();
    
    /**
    * 验证登录
    */
    protected function checkAuth () {
        if (!session("ADMIN_AUTHID")) {
            session("SESSION_HISTORYURL",$_SERVER['REQUEST_URI']);
            redirect ( '/Account/login' );
        }
    }

    protected function getuid() {
        $uid = session("ADMIN_AUTHID");
        if (!$uid) $this->checkAuth();
        return $uid;
        #$this->show("getuid");
    }

    protected function myserialize ( $arr ){
        //var_dump($arr);
        if (!is_array($arr)) return "";
        foreach ($arr as $key=>$val) {
            $arr[$key] = safe_string($arr[$key]);
            if (empty($arr[$key])) {
                unset($arr[$key]);
            }
        }
        return $arr ? serialize($arr) : "";
    }

    protected function display($tpl = null) {
        $this->assign("npc", $this->npc);
        parent::display($tpl);
    }

    /**
     * 公共输出
     * @param  $str
     */
    public function out_put($array=array(),$jsonp=false){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST,OPTIONS,PUT");    
        $returnStr = "";
        if(is_array($array)){
            $array['datetime'] = Date("Y-m-d H:i:s");
            $rs = json_encode($array);
            $rs = preg_replace("/null/i", '""', $rs);
            $returnStr = $rs;
        }else{
            $returnStr = $array;
        }
        $callback = $_GET['callback'];
        if($jsonp || $callback){
            $callback = $callback?$callback:"jsonp".time();
            echo $callback."(".$returnStr.")";
        }else{
            echo $returnStr;
        }
        $this->close();
    }
    
    /**
     * 程序关闭
     */
    protected function close(){
        exit();
    }
    
    /**
     * (non-PHPdoc)
     * @see Action::__call()
     */
    public function __call($method,$arg){
        $rs_data['error'] = 404;
        $rs_data['msg'] = "非法请求";
        $this->out_put($rs_data);
    }



}