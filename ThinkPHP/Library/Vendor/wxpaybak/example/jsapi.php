<?php 
class wxpay{
	function getjsApiParameters($order){
		ini_set('date.timezone','Asia/Shanghai');
		//error_reporting(E_ERROR);
// 		die;
		require_once "lib/WxPay.Api.php";
 		require_once "WxPay.JsApiPay.php";
 		require_once 'log.php';

		//初始化日志
		$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
		$log = LogWX::Init($logHandler, 15);
		
		//①、获取用户openid
		$tools = new JsApiPay();
		if($order['integral_payment']>0){
			$integral = 1;
		}else{
			$integral = 0;
		}
		$openId = $tools->GetOpenid("http://m.zx42195.com/PayResult/payorder?orderid=".$order['orderid']."&integral=".$integral);
		if(!$openId){
			$openId = session("SESSION_ZX_OPENID");
		}
		//②、统一下单
		$input = new WxPayUnifiedOrder();
		$input->SetBody("知行合逸马拉松报名");
		$input->SetAttach($order['orderid']);
		$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
		$price = round($order['payprice']-$order['discount']-$order['integral_payment'],2)*100;
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("http://m.zx42195.com/PayResult/pay_notify");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);
		// echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
		// printf_info($order);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		
		//获取共享收货地址js函数参数
		//$editAddress = $tools->GetEditAddressParameters();
		return $jsApiParameters;
	}
	
	/**
	 * 小程序
	 * @param unknown_type $order
	 * @return Ambigous <json数据，可直接填入js函数作为参数, string>
	 */
	function getXcxApiParameters($openid,$order){
		ini_set('date.timezone','Asia/Shanghai');
		//error_reporting(E_ERROR);
		// 		die;
		require_once "lib/WxPay.Api.php";
		require_once "WxPay.JsApiPay.php";
		require_once 'log.php';
	
		//①、获取用户openid
		$tools = new JsApiPay();
		//②、统一下单
		$input = new WxPayUnifiedOrder();
		$input->SetBody("相片打赏");
		$input->SetAttach($order['order_id']);
		$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
		$price = round($order['amount'], 2) * 100;
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("https://api.album.iqikj.com/Pay/pay_notify");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openid);
		$order = WxPayApi::unifiedOrderForXcx($input);
		// echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
		//mytrace($order);
		$jsApiParameters = $tools->GetJsApiParametersArray($order);
		return $jsApiParameters;
	}
	
	/**
	 * 微信充值
	 * @param unknown_type $order
	 * @return Ambigous <json数据，可直接填入js函数作为参数, string>
	 */
	function getBelanceApiParameters($amount,$uid){
		ini_set('date.timezone','Asia/Shanghai');
		require_once "lib/WxPay.Api.php";
 		require_once "WxPay.JsApiPay.php";
 		require_once 'log.php';

		//初始化日志
		$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
		$log = LogWX::Init($logHandler, 15);
		
		//①、获取用户openid
		$tools = new JsApiPay();
		$openId = $tools->GetOpenid("http://m.zx42195.com/Rechange/callpay?amount=".$amount);
		if(!$openId){
			$openId = session("SESSION_ZX_OPENID");
		}
		//②、统一下单
		$input = new WxPayUnifiedOrder();
		$input->SetBody("知行合逸充值");
		$input->SetAttach($uid);
		$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
		$price = $amount*100;
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		//$input->SetNotify_url("http://weixin.zx-tour.com/Rechange/pay_notify");
		$input->SetNotify_url("http://m.zx42195.com/PayResult/pay_belance_notify");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);
		// echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
		// printf_info($order);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		return $jsApiParameters;
	}
	
	function getDiffjsApiParameters($order){
		ini_set('date.timezone','Asia/Shanghai');
		//error_reporting(E_ERROR);
		// 		die;
		require_once "lib/WxPay.Api.php";
		require_once "WxPay.JsApiPay.php";
		require_once 'log.php';
	
		//初始化日志
		$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
		$log = LogWX::Init($logHandler, 15);
	
		//①、获取用户openid
		$tools = new JsApiPay();
		if($order['integral_payment']>0){
			$integral = 1;
		}else{
			$integral = 0;
		}
		$openId = $tools->GetOpenid("http://m.zx42195.com/PayResult/diff_pay?orderid=".$order['orderid']);
		if(!$openId){
			$openId = session("SESSION_ZX_OPENID");
		}
		//②、统一下单
		$input = new WxPayUnifiedOrder();
		$input->SetBody("知行合逸马拉松报名");
		$input->SetAttach($order['orderid']);
		$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
		$price = round($order['payprice']-$order['discount']-$order['integral_payment'],2)*100;
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("http://m.zx42195.com/PayResult/pay_notify");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);
		// echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
		// printf_info($order);
		$jsApiParameters = $tools->GetJsApiParameters($order);
	
		//获取共享收货地址js函数参数
		//$editAddress = $tools->GetEditAddressParameters();
		return $jsApiParameters;
	}
	
	/**
	 * 微信充值
	 * @param unknown_type $order
	 * @return Ambigous <json数据，可直接填入js函数作为参数, string>
	 */
	function getTestApiParameters($order){
		ini_set('date.timezone','Asia/Shanghai');
		//error_reporting(E_ERROR);
// 		die;
		require_once "lib/WxPay.Api.php";
 		require_once "WxPay.JsApiPay.php";
 		require_once 'log.php';

		//初始化日志
		$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
		$log = LogWX::Init($logHandler, 15);
		
		//①、获取用户openid
		$tools = new JsApiPay();
		if($order['integral_payment']>0){
			$integral = 1;
		}else{
			$integral = 0;
		}
		$openId = $tools->GetOpenid("http://m.zx42195.com/PayResult/wxcallpay?orderid=".$order['orderid']);
		if(!$openId){
			$openId = session("SESSION_ZX_OPENID");
		}
		//②、统一下单
		$input = new WxPayUnifiedOrder();
		$input->SetBody("知行合逸马拉松报名");
		$input->SetAttach($order['orderid']);
		$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
		$price = round($order['payprice']-$order['discount']-$order['integral_payment'],2)*100;
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("http://m.zx42195.com/PayResult/pay_notify");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);
		// echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
		// printf_info($order);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		
		//获取共享收货地址js函数参数
		//$editAddress = $tools->GetEditAddressParameters();
		return $jsApiParameters;
	}
	
	
}
