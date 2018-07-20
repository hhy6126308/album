<?php
function debug_point ($data) {
    if (gettype($data) == 'array') {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    } else {
        var_dump($data);
    }
}

function safe_string ($string, $default = '') {
    if (gettype($string) != 'string') return false;
    $string = htmlspecialchars(addslashes(trim($string)));
    return !$string && $default ? $default : $string;
}

/**
 * 订单号生成
 * @param unknown_type $uid
 * @param unknown_type $matchid
 * @return string
 */
function createOrderId($uid,$matchid){
    $date = date("ymdHis");
    return strRepeats($matchid,3,0).$date.strRepeats($uid,3,0).rand(100,999);
}

/**
 * 字符串补全
 * @param unknown_type $str
 * @param unknown_type $len
 * @param unknown_type $repeat
 * @return string
 */
function strRepeats($str,$len,$repeat){
    $time = $len-strlen($str);
    return str_repeat($repeat,$time).$str;
}

//时间测试函数
function get_microtime(){
    list($usec, $sec) = explode(" ", microtime());
    return $GLOBALS['time_start'] = ((float)$usec + (float)$sec);
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