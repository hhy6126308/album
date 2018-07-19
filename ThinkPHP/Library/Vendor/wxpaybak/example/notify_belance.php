<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';
require_once 'log.php';

//初始化日志
// $logHandler= new CLogFileHandler("logs/".date('Y-m-d').'.log');
// $log = LogWX::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
// 		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{	
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		$uid = $data["attach"];
		$pay = $data["total_fee"];
		@mwtlog("belance_log",json_encode($data),true);
		$belanceM = new BelanceinfoModel();
		$updateRes =  $belanceM->updateBelanceLog($uid, $data["transaction_id"], $pay,$data);
		if($updateRes){
			$integralM = new IntegralModel();
			$integralM->belance_add_integral($uid,round($pay/100,2),$data["transaction_id"]);
		}
		return $updateRes;
	}
}
$notify = new PayNotifyCallBack();
$notify->Handle(false);
