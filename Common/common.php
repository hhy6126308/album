<?php
/*
 * 项目公共函数
 * @since 2013-7-11 12:05:07
 */


/***
 * 输出变量，在浏览器上比较好看，常用于调试
 */
function mytrace( $var ){
	echo "<pre>";
	var_dump($var);
	die;
}
function sae_log($str){
		$str.= "\r\n";
		$str.= "get:\r\n";
		$str.= serialize($_GET);
		$str.= "\r\n";
		$str.= "post:\r\n";
		$str.= serialize($_POST);
		$str.= "==========================================";
		$s	= new SaeStorage();
		$s->write('debug', date('Y-m-d').'_log.txt', $str);
}
/**
 * 取得参数值
 * @param string $keyName 参数名
 * @return bool
 */
function get_key($keyName)
{
	return do_command('sysctl', "-n $keyName");
}
/*
 * 执行系统命令
* @param string $commandName 命令名
* @param string $args 参数
* @return bool
*/
function do_command($commandName, $args)
{
	$buffer = "";
	if (false === ($command = find_command($commandName))) return false;
	if ($fp = @popen("$command $args", 'r'))
	{
		while (!@feof($fp))
		{
			$buffer .= @fgets($fp, 4096);
		}
		return trim($buffer);
	}
	return false;
}
// 计算文件大小
function bytes_to_string( $bytes )
{
	if (!preg_match("/^[0-9]+$/", $bytes)) return 0;
	$sizes = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
	$extension = $sizes[0];
	for( $i = 1; ( ( $i < count( $sizes ) ) && ( $bytes >= 1024 ) ); $i++ )
	{
		$bytes /= 1024;
		$extension = $sizes[$i];
	}

	return round( $bytes, 2 ) . ' ' . $extension;
}
function message($message, $url = null, $timeout = 2, $stop_loop=0)
{
	redirect("?s=message&msg=".urlencode($message)."&url=".urlencode($url)."&timeout=$timeout&loop=$stop_loop");
	exit();
}

/**
 * 显示memcache中所有的键值
 * @param  $host
 * @param  $port
 * @return Ambigous <multitype:Ambigous <mixed, boolean> , mixed, boolean>
 */
function show_mem($host,$port){

	$memcache_obj = new Memcache();
	$memcache_obj->addServer($host, $port);
	//echo $memcache_obj->getversion();

// 	$memcache_obj->set('xx','xx');
// 	$memcache_obj->set('yy','yy');
// 	$memcache_obj->set('zz','zz');
	 
	//方式一 网上的
	$result = $memcache_obj->getExtendedStats('items');
	$items=$result["$host:$port"]['items'];

	//方式二 php手册说的
	//$result = $memcache_obj->getExtendedStats('slabs');
	//$items = $result["$host:$port"];

	//print_r($items);exit;

	$arr_slabid=array_keys($items);

	//print_r($arr_slabid);
	//exit;
	foreach($arr_slabid as $id)
	{
		$id=intval($id);
		$str=$memcache_obj->getExtendedStats("cachedump",$id,0);
		$line=$str["$host:$port"];
		if(!empty($line))
		{
			$keys = array_keys($line);
			foreach($keys as $key)
			{
				$data[$key] = $memcache_obj->get($key);
			}
		}
	}
	$memcache_obj->close();
	return $data;
// 	print_r($data);
// 	exit;
}

/**
 * @param 前缀 $dom
 * @param memcache端口 $port
 * @param ip地址 $local
 * @return data=array(allPv,allUv,data=array(pv,uv))
 */
function get_mem_tj($dom="Nt_",$port="11666",$local="127.0.0.1"){
// 	$dom    = "Nt_";
	$pvs    = "pv";
	$uvs    = "uv";
	$date   = date("mdHi");
	$memcache = new Memcache();
	$memcache->connect($local, $port);
	$data['allPv']	= $memcache->get($dom.$pvs);
	$data['allUv']	= $memcache->get($dom.$uvs);
	//                 $data['mpv']     =$memcache->get($dom.$pvs.$date);
	//                 $data['mpv']	=  $data['mpv'] ? $data['mpv'] :0;
	$Y	= date("Y");
	$M	= date("m");
	$D	= date("d");
	$H	= date("H");
	$I	= date("i");
	$h	= "0";
	$m	= "0";
	$rs2	= array();
	for($i=1;$i<=1440;$i++){
		$m	= intval($m);
		$h	= intval($h);
		if($m>=60){
			$h++;
			$m=0;
		}
		if($h>=24){
			$h=0;
			break;
		}
		if($h<10){
			$h='0'.$h;
		}
		if($m<10){
			$m='0'.$m;
		}
		
		$tstr	= $M.$D.$h.$m;
		$t	= strtotime($Y.$tstr."00");
		$t	= $t*1000;
		$pv	= $memcache->get($dom.$pvs.$tstr);
		$pv	= empty($pv)?0:$pv;
		$rs2['pv'][]=array((float)$t,(float)$pv);
		$uv	= $memcache->get($dom.$uvs.$tstr);
		$uv	= empty($uv)?0:$uv;
		$rs2['uv'][]=array((float)$t,(float)$uv);
		//比当前时间大了,退出
		if($h>=$H && $m>=$I){
			break;
		}
		$m++;
	}
	 $data['data']=$rs2;
	 return $data;
	/* var_dump($rs2);die;
	$all = show_mem($local,$port);
	ksort($all);
// 	$all = show_mem("127.0.0.1","11666");
	foreach($all as $k=>$v){
		$pt	= preg_match("/$dom$pvs+([\d]{8})/", $k,$mpt);
		$ut	= preg_match("/$dom$uvs+([\d]{8})/", $k,$mut);
		if($pt){
			$t	= strtotime(date("Y").$mpt[1]."00");
			$t	= $t*1000;
			$rs['pv'][]=array((float)$t,(float)$v);
		}
		if($ut){
			$t2	= strtotime(date("Y").$mut[1]."00");
			$t2	= $t2*1000;
			$rs['uv'][]=array((float)$t2,(float)$v);
		}
	}
	$data['data']=$rs;
	return $data; */
}
/*获取日期差*/
function minus_datas($s,$e){
	$Date_1=date('Y-m-d',strtotime($e));
	$Date_2=date('Y-m-d',strtotime($s));;
	$Date_List_a1=explode("-",$Date_1);
	$Date_List_a2=explode("-",$Date_2);
	$d1=mktime(0,0,0,$Date_List_a1[1],$Date_List_a1[2],$Date_List_a1[0]);
	$d2=mktime(0,0,0,$Date_List_a2[1],$Date_List_a2[2],$Date_List_a2[0]);
	$Days=round(($d1-$d2)/3600/24);
	return $Days;
}

//http 模拟
function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

/**
 * curl获取远程文件通用函数
 * @param string $url
 * @param bool $info(是否显示头信息,如果返回头信息,则返回结果为数组,否则为字符串)
 * @return mixed||array
 */
function getData( $url ,$info=false,$referer="",$gzip=false,$data='',$header=array(),$userAgent = ''){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	$userAgent	= $userAgent?$userAgent:'Mozilla/5.0 (Windows NT 5.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1';
	
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 600);
	curl_setopt($ch, CURLOPT_HEADER, $info);
	//https start
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt ($ch,CURLOPT_REFERER,$referer);
	if($gzip){
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	}
	if($data){
		$option[CURLOPT_POST] = 1;
		$option[CURLOPT_POSTFIELDS] = $data;
		curl_setopt_array($ch,$option);
	}
	if(!empty($header)){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	//https start
	$result = curl_exec($ch);
	/*获取头信息*/
	if($info===true){
		$cookie_jar = tempnam('./tmp','cookie');
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar); //把返回来的cookie信息保存在$cookie_jar文件中
		$headerSize	= curl_getinfo($ch,CURLINFO_HEADER_SIZE);
		$header_str		= substr($result, 0, $headerSize);
		$header_str		= preg_split("/\n/", $header_str);
		$header	= array();
		$i	= 0;
		foreach ($header_str as $k=>$v){
			$v		= trim($v);
			if($i == 0){
				$tem	= preg_split("/\s/", $v);
				$header['state']	= $tem[1];
				$i++;
				continue;
			}

			if(!empty($v)){
				$tem	= preg_split("/:/", $v,2);
				$t			= $tem[0];
				$header[$t]	= $tem[1];
			}
			$i++;
		}
		unset($header_str);
		$rs['header']	= $header;
		$rs['data']		= substr($result, $headerSize );
		return $rs;
	}
	curl_close($ch);
	return $result;
}
////////////////////////////////////////////新浪微博////////////////////////////////////////////////////////
function login_weibo($acc,$pwd){
	if(empty($acc) || empty($pwd))return false;
	$info   = prepare_login_info();
	$info   = login($info, $acc, $pwd);
	if($info && $info['result']==true){
		echo 'login success!'.$acc."\r\n";
// 		sleep($GLOBALS['wait_time']);
// 		@mwt_log('login_success', "登录成功acc:{$acc['acc']}\t".serialize($info));
	}else{
		echo 'login fail!'.$pwd." and wait \r\n";
// 		sleep($GLOBALS['wait_time']);
// 		@mwt_log('login_fail', "登录失败acc:{$acc['acc']}\t".serialize($info));
	}
}
function login($info, $username, $password,$sp='') {
	$feedbackurl    = makeUrl('http://weibo.com/ajaxlogin.php', array(
			'framelogin'        => 1,
			'callback'          	=> 'parent.sinaSSOController.feedBackUrlCallBack',
	));
	if(empty($sp)){
		$sp	= encode_password($info['pubkey'], $password, $info['servertime'], $info['nonce']);
	}
	$datas  = array(
			'encoding'          => 'UTF-8',
			'entry'             		=> 'weibo',
			'from'              	=> '',
			'gateway'           	=> 1,
			'nonce'             	=> $info['nonce'],
			'prelt'             		=> $info['preloginTime'],
			'pwencode'			=> 'rsa2',
			'returntype'        => 'META',
			'rsakv'             		=> $info['rsakv'],
			'savestate'         	=> 7,
			'servertime'        	=> $info['servertime'],
			'service'           	=> 'miniblog',
			'sp'                		=> $sp,
			'ssosimplelogin' => 1,
			'su'                		=> encode_username($username),
			'url'               		=> $feedbackurl,
			'useticket'         	=> 1,
			'vsnf'              		=> 1,
	);
	$url    = makeUrl('http://login.sina.com.cn/sso/login.php', array(
			'client'    => 'ssologin.js(v1.4.2)',
	), FALSE);
	$response   = curl_request($url, REQUEST_METHOD_POST, $datas,'',urlencode($username));
	preg_match("/location.replace\(\"(.*)\"\)/", $response,$replace);
	$location		= $replace[1];
	$response   = curl_request($location,'get','','',urlencode($username));
	preg_match("/\((.*)\)/", $response,$calback);
	$response	= $calback[1];
	return json_decode($response, true);
}
function prepare_login_info($entry='sso') {
	$time   = get_js_timestamp();
	$url    = makeUrl('http://login.sina.com.cn/sso/prelogin.php', array(
			'entry'		=> $entry,
			'callback'	=> 'sinaSSOController.preloginCallBack',
			'su'			=> encode_username('undefined'),
			'rsakt'		=> 'mod',
			'client'		=> 'ssologin.js(v1.4.2)',
			'_'         	=> $time,
	), FALSE);
	$response   = curl_request($url);
	$length     = strlen($response);
	$left       = 0;
	$right      = $length - 1;
	while ( $left < $length )
		if ( $response[$left] == '{' ) break;
	else $left ++;
	while ( $right > 0 )
		if ( $response[$right] == '}' ) break;
	else $right --;
	$response   = substr($response, $left, $right - $left + 1);
	return array_merge(json_decode($response, TRUE), array(
			'preloginTime'  => max(get_js_timestamp() - $time, 100),
	));
}
function curl_request($url, $method = REQUEST_METHOD_GET, $datas = NULL, $headers = NULL,$uid='') {
	//     static  $curl;
	//     if ( !$curl )
	$curl   = curl_init();
	curl_switch_method($curl, $method);
	curl_setopt($curl, CURLOPT_URL,                     $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,          TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION,          TRUE);
	curl_setopt($curl, CURLOPT_AUTOREFERER,             TRUE);
	if(!empty($uid)){
		curl_setopt($curl, CURLOPT_COOKIEJAR,               APP_PATH."sina/c/".'sina.cookie');
	}
	curl_setopt($curl, CURLOPT_COOKIESESSION,           TRUE);
	if ( $datas )
		curl_set_datas($curl, $datas);
	if ( $headers)
		curl_set_headers($curl, $headers);
	$response   = curl_exec($curl);
	if ( $errno = curl_errno($curl) ) {
		error_log(sprintf("%10d\t%s\n", $errno, curl_error($curl)), 3, 'php://stderr');
		return FALSE;
	}
	return $response;
}
function encode_password($pub_key, $password, $servertime, $nonce) {
	$Path	= APP_PATH."sina";
	$is_pro	= C("IS_PRO");
	if($is_pro){
		//system 
		$cmd	= "/usr/local/node/0.9.0/bin/node ".$Path."/sina.js \"$pub_key\" \"$servertime\" \"$nonce\" \"$password\"";
		$response   = system($cmd);
		if(empty($response)){
			echo 'get password fail , please confirm installed node.exe!'."\r\n";
		}
		return $response;
	}else{
		$cmd	= $Path."/nodem ".$Path."/sina.js \"$pub_key\" \"$servertime\" \"$nonce\" \"$password\" > ".$Path."/logs/tmp.txt";
		$response   = `$cmd`;
		$response	= file_get_contents($Path.'/logs/tmp.txt');
	}
	if(empty($response)){
		echo 'get password fail , please confirm installed node.exe!'."\r\n";
// 		mwt_log('fail','获取node结果失败,请确认安装了node 并且环境变量已经执行了node cmd:'.$cmd);
	}
	return substr($response, 0, strlen($response) - 1);
}
function curl_switch_method($curl, $method) {
	switch ( $method) {
		case REQUEST_METHOD_POST:
			curl_setopt($curl, CURLOPT_POST, TRUE);
			break;
		case REQUEST_METHOD_HEAD:
			curl_setopt($curl, CURLOPT_NOBODY, TRUE);
			break;
		case REQUEST_METHOD_GET:
		default:
			curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
			break;
	}
}
function curl_set_headers($curl, $headers) {
	if ( empty($headers) ) return ;
	if ( is_string($headers) )
		$headers    = explode("\r\n", $headers);
	#类型修复
	foreach ( $headers as &$header )
		if ( is_array($header) )
		$header = sprintf('%s: %s', $header[0], $header[1]);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
}
function curl_set_datas($curl, $datas) {
	if ( empty($datas) ) return ;
	curl_setopt($curl, CURLOPT_POSTFIELDS, $datas);
}
function encode_username($username) {
	return base64_encode(urlencode($username));
}
function get_js_timestamp() {
	return time() * 1000 + rand(0, 999);
}
function makeUrl($url, $info, $encode = TRUE) {
	if ( !is_array($info) || empty($info) ) return $url;
	$components = parse_url($url);
	if ( array_key_exists('query', $components) )
		$query  = parse_str($components['query']);
	else
		$query  = array();
	if ( is_string($info) ) $info = parse_str($info);
	$query      = array_merge($query, $info);
	$query      = $encode
	? http_build_query($query)
	: http_build_query_no_encode($query);
	$components['scheme']   = array_key_exists('scheme', $components)
	? $components['scheme'] . '://'
			: '';
	$components['user']     = array_key_exists('user', $components)
	? $components['user'] . ':' . $components[HTTP_URL_PASS] . '@'
			: '';
	$components['host']     = array_key_exists('host', $components)
	? $components['host']
	: '';
	$components['port']     = array_key_exists('port', $components)
	? ':' . $components['port']
	: '';
	$components['path']     = array_key_exists('path', $components)
	? '/' . ltrim($components['path'], '/')
	: '';
	$components['query']    = $query
	? '?' . $query
	: '';
	$components['fragment'] = array_key_exists('fragment', $components)
	? '#' . $components['fragment']
	: '';
	return sprintf('%s%s%s%s%s%s%s', $components['scheme'], $components['user'], $components['host'],
			$components['port'], $components['path'],
			$components['query'], $components['fragment']);
}
function http_build_query_no_encode($datas) {
	$r  = array();
	foreach ( $datas as $k => $v )
		$r[]    = $k . '=' . $v;
	return implode('&', $r);
}
function get_weibo_content($uid,$page=1){
	$cookie	= APP_PATH."sina/c/".'sina.cookie';
	$js_time	= get_js_timestamp();
	$url	= "http://weibo.com/p/aj/mblog/mbloglist?domain=100505&pre_page=0&page=%d&count=15&pagebar=0&max_msign=&filtered_min_id=&pl_name=Pl_Official_LeftProfileFeed__20&id=%s&script_uri=/p/%s/weibo&feed_type=0&is_search=0&visible=0&is_tag=0&profile_ftype=1&__rnd=%s";
	$url	= sprintf($url,$page,$uid,$uid,$js_time);
	$html	= @curlRequest($url,'',$cookie);
	return json_decode($html,true);
}
/**
 * 获取uid里的微博
 * @param 账号主页 $uid_url
 */
function get_uid_weibo_url($uid_url,$cookie=""){
	if(empty($cookie)){
		$cookie	= APP_PATH."sina/c/".'sina.cookie';
	}
	$html	= curlRequest($uid_url,'',$cookie);
	if(empty($html)){
		$str	= 'to  login all acctout and try again,if not work again url '.$uid_url.' may be jump url!'."\r\n";
		// 		login_all();
		echo $str;
// 		@mwt_log('get_uid',$str);
	}else{
		/*获取最新的几条微博*/
		preg_match_all('/<div class=\\\\\"WB_from\\\\">.*?href=\\\\"([^"]+).*?<\\\\\\/div>/is', $html, $weiboids);
		if(!empty($weiboids[1])){
			foreach($weiboids[1] as $vv){
				if(!empty( $weiboids[1])){
					$report_url[]		= 'http://weibo.com'.preg_replace('/\\\\/', '',  $vv);
				}
			}
				
		}else{
			echo "get {$weiboids[1]} 's weibo url fail,please connect with developer!";
		}
		if(!empty($report_url)){
			return $report_url;
		}else{
			return false;
		}
	}

}

function curlRequest($url,$data='',$cookieFile="",$referer='',$login=false,$header=array(),$gzip=false){
	$ch = curl_init();
	$option = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER =>0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.92 Safari/537.1 LBBROWSER",
			// 			CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0",
			CURLOPT_REFERER		=>$referer
	);
	if($cookieFile){
		if($login){
			$option[CURLOPT_COOKIEJAR] = $cookieFile;
			$option[CURLOPT_COOKIESESSION] = true;
		}else{
			$option[CURLOPT_COOKIEJAR] = $cookieFile;
			$option[CURLOPT_COOKIEFILE] = $cookieFile;
		}

		// 			$option[CURLOPT_COOKIE] = $cookieFile;
		// 		$option[CURLOPT_COOKIESESSION] = true;
		//$option[CURLOPT_COOKIE] = 'prov=42;city=1';
	}
	if($data){
		$option[CURLOPT_POST] = 1;
		$option[CURLOPT_POSTFIELDS] = $data;
	}
	if(!empty($header)){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	if($gzip==true){
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	}
	// 	curl_setopt($ch, CURLOPT_REFERER, "http://weibo.com/");
	curl_setopt_array($ch,$option);
	$response = curl_exec($ch);
	if(curl_errno($ch) > 0){
		die("CURL ERROR:$url ".curl_error($ch));
	}
	curl_close($ch);
	return $response;
}
function frameurl($str,$config,$info){
	preg_match_all("/{(.*?)}/", $str, $key);
	$str	= preg_replace("/[&]?{.*?}/", "", $str);
	$tem	= "";
	if(!empty($key) && !empty($key[1])){
		foreach($key[1] as $v){
			$tem	.= "&".$v."=".$info[$v];
		}
	}
	return $str.$tem;
}

function picurl($str,$info){
	preg_match_all("/{(.*?)}/", $str, $key);
	if(!empty($key) && !empty($key[1])){
		foreach($key[1] as $v){
			$str	= preg_replace("/{".$v."}/", $info[$v], $str);
		}
	}
	$file	= WWWROOT."/".preg_replace("/http:\/\/.*?\//", "", $str);
	$rs	= file_exists($file);
	if(!$rs){
		$str	= "";
	}
	return $str;
}
function getmictime(){
	//  获取毫秒的时间戳  
	$time = explode ( " ", microtime () );  
	$time = $time [1] . ($time [0] * 1000);  
	$time2 = explode ( ".", $time );  
	$time = $time2 [0];
	return $time;
}
function add_host($v,$host="http://c.interface.at321.cn"){
	if(!preg_match("/http:\/\//", $v)){
		return $host.$v;
	}else{
		return $v;
	}
}

/**
 * @param log $str
 */
function mwtlog($filename='common',$str='',$dataPath=false){
	global $Gimei;
	if(C("MwtLog") || $dataPath || in_array($Gimei, C('ALOWED_IMEI'))){
		$log_path	= APP_PATH.'Runtime/Logs/'.date('y_m_d').$filename.'.log';
		$error	= 0;
		if($dataPath){
			if(!is_dir(APP_PATH.'data') && !mkdir(APP_PATH.'data')){
				$error	= 1;
			}
			if(!is_dir(APP_PATH.'data/') && !mkdir(APP_PATH.'data/')){
				$error	= 1;
			}
			if($error===0){
				$log_path	= APP_PATH.'data/'.date('y_m_d').$filename.'.log';
			}
		}
		if(MODE_NAME != "cli"){
			$ip	= @get_client_ip();
		}else{
			$ip	= "cli_running";
		}
		@log::write(date("Y-m-d H:i:s")." $ip\t".$str."\r\n",$filename,'3',$log_path);
	}
}
/**
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * @return string
 */
function rand_string($len=6,$type='',$addChars='') {
	$str ='';
	switch($type) {
		case 0:
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
			break;
		case 1:
			$chars= str_repeat('0123456789',3);
			break;
		case 2:
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
			break;
		case 3:
			$chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
			break;
		case 4:
			$chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借".$addChars;
			break;
		default :
			// 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
			$chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
			break;
	}
	if($len>10 ) {//位数过长重复字符串一定次数
		$chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
	}
	if($type!=4) {
		$chars   =   str_shuffle($chars);
		$str     =   substr($chars,0,$len);
	}else{
		// 中文随机字
		for($i=0;$i<$len;$i++){
			$str.= msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1);
		}
	}
	return $str;
}

/**
 * phpexcel导出
 * @param unknown_type $data
 * @param unknown_type $title
 * @param unknown_type $needinfo
 * @param unknown_type $filename
 */
function exportexcelV2($data,$title,$needinfo,$filename='report'){
	ini_set ( "max_execution_time", "0");
	ini_set ( 'memory_limit', '512M');
	if($needinfo){
		array_push($title, "姓名");
		array_push($title, "订单详情");
	}

	vendor("PHPExcel.export");
	$export = new  Excel_export();
	$indexX = 1;
	if (!empty($title)){
		if (!empty($title)){
			foreach ($title as $k => $v) {
				$export->setCellValue($indexX,$k,$title[$k]);
			}
		}
	}
	if (!empty($data)){
		$orderinfoM = new UserOrdersInfoV2Model;
		foreach($data as $k=>$match){
			$indexX++;
			$j=0;
			foreach($match as $key => $val){
				$isnum = false;
				if($key=="payprice"||$key=="discount"){
					$isnum = true;
				}
				if($key=="paystats"){
					switch ($val){
						case -1:
							$val = "已删除";
							break;
						case 1:
							$val = "支付完成/缺信息";
							break;
						case 2:
							$val = "支付金额不对";
							break;
						case 3:
							$val = "支付过期";
							break;
						case 5:
							$val = "未支付";
							break;
						case 7:
							$val = "退款成功";
							break;
						case 9:
							$val = "库存不足";
							break;
						case 10:
							$val = "报名成功";
							break;
					}
				}

				if($key=="official_notes" && !empty($val)){
					 if(is_array(json_decode($val))){
				          $val=json_decode($val,true);
				          $count=count($val);
				          $val=$val[$count-1]['content'];
				          //die;
				     }
				}
				if($key=="orderinfo"){
                    $list = $orderinfoM->getOrderInfolist($match['orderid']);
                    $matchinfo  = "";
                    $orderinfo2 = "";
                    $orderinfo1 = "";
                    foreach ($list as $k =>$v){
                        if($v['type']=="套餐"){
                            $goodsM = new GoodsV2Model();
                            $cityinfo = $goodsM->getmeal_info($v['g_pid']);
                            $v['g_name'] = $v['g_name']."(出发城市：".$cityinfo['g_name'].")";
                            $matchinfo= $v['g_name'];
                        }
                        if($v['type']=="附加优质服务"){
                              $orderinfo2 = empty($orderinfo2) ? $v['g_name'] : $orderinfo2."  +  ".$v['g_name'];
                        }
                        if($v['type']=="赛程"){
                              $orderinfo1=$v['g_name'];
                        }

                    }
                    $val = rtrim($matchinfo,"+");
                }
                if($key=="orderinfo1"){
                    $val = empty($orderinfo1) ? '' : $orderinfo1;
                }
                if($key=="orderinfo2"){
                    $val = empty($orderinfo2) ? '' : $orderinfo2;
                }

				$export->setCellValue($indexX,$j,$val,$isnum);
				$j++;
			}

//			if($needinfo){
//				$export->setCellValue($indexX,$j,$match['name']);
//				$list = $orderinfoM->getOrderInfolist($match['orderid']);
//				$matchinfo = "";
//				foreach ($list as $k =>$v){
//					if($v['type']=="套餐"){
//						$goodsM = new GoodsV2Model();
//						$cityinfo = $goodsM->getmeal_info($v['g_pid']);
//						$v['g_name'] = $v['g_name']."(出发城市：".$cityinfo['g_name'].")";
//					}
//					$matchinfo.= $v['type']."(".$v['g_name'].")+";
//				}
//				$matchinfo = rtrim($matchinfo,"+");
//				$export->setCellValue($indexX,$j+1,$matchinfo);
//			}
				
		}
	}

	$export->save($filename);
}

/**
 * 根据HTML代码获取word文档内容
 * 创建一个本质为mht的文档，该函数会分析文件内容并从远程下载页面中的图片资源
 * 该函数依赖于类WordMake
 * 该函数会分析img标签，提取src的属性值。但是，src的属性值必须被引号包围，否则不能提取
 *
 * @param string $content HTML内容
 * @param string $absolutePath 网页的绝对路径。如果HTML内容里的图片路径为相对路径，那么就需要填写这个参数，来让该函数自动填补成绝对路径。这个参数最后需要以/结束
 * @param bool $isEraseLink 是否去掉HTML内容中的链接
 */
function WordMake( $content , $absolutePath = "" , $isEraseLink = true )
{
    import("ORG.Util.Wordmaker");
    $mht = new Wordmaker();
    if ($isEraseLink){
        $content = preg_replace('/<a\s*.*?\s*>(\s*.*?\s*)<\/a>/i' , '$1' , $content);   //去掉链接
    }
    $images = array();
    $files = array();
    $matches = array();
    //这个算法要求src后的属性值必须使用引号括起来
    if ( preg_match_all('/<img[.\n]*?src\s*?=\s*?[\"\'](.*?)[\"\'](.*?)\/>/i',$content ,$matches ) ){
        $arrPath = $matches[1];
        for ( $i=0;$i<count($arrPath);$i++)
        {
            $path = $arrPath[$i];
            $imgPath = trim( $path );
            if ( $imgPath != "" )
            {
                $files[] = $imgPath;
                if( substr($imgPath,0,7) == 'http://')
                {
                    //绝对链接，不加前缀
                }
                else
                {
                    $imgPath = $absolutePath.$imgPath;
                }
                $images[] = $imgPath;
            }
        }
    }
    $mht->AddContents("tmp.html",$mht->GetMimeType("tmp.html"),$content);
    for ( $i=0;$i<count($images);$i++)
    {
        $image = $images[$i];
        if ( @fopen($image , 'r') )
        {
            $imgcontent = @file_get_contents( $image );
            if ( $content )
                $mht->AddContents($files[$i],$mht->GetMimeType($image),$imgcontent);
        }
        else
        {
            echo "file:".$image." not exist!<br />";
        }
    }
    return $mht->GetFile();
}

?>