<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\RedisModel;

class BaseController extends Controller {

    protected $rs_data = array('error'=>'65535','msg'=>'未知错误','data'=>array(),"datetime" =>'');
    protected $get_data;
    protected $startTime;
    public function __construct() {
        //计算程序开始时间
        $this->startTime	= get_microtime();
        $this->get_data = $_REQUEST;
    }

    public function getOpenid(){
        $rd_session = safe_string($_POST['rd_session']);
        if(empty($rd_session)){
            $this->out_put(array("error"=>304,"msg"=>"用户未登录","data"=>""));
        }

        $redisM = new RedisModel();
        $sessionStr = $redisM->hget("ZXHY_XCX_RDSESSION",$rd_session);
        $sesion_json = json_decode($sessionStr,true);
        if($sesion_json['openid']){
            return $sesion_json['openid'];
        }else{
            $this->out_put(array("error"=>304,"msg"=>"用户未登录","data"=>""));
        }

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
            $array['datetime'] = date("Y-m-d H:i:s");
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