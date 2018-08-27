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
 * 获取微信Access_token
 */
function getWxaqrcode($path)
{
    //header('content-type:text/html;charset=utf-8');
    //header('content-type:image/png');
    $tapi = 'https://api.weixin.qq.com/cgi-bin/token?appid=%s&secret=%s&grant_type=client_credential';
    $aapi = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=%s';
    $APPID = 'wx8c69d85ada607cee';
    $APPSECRET = '79875eee38b1f5f4bf4657d223744020';
    $tUrl = sprintf($tapi,$APPID,$APPSECRET);
    $WXres = json_decode(\getData($tUrl),true);
    if(isset($WXres['access_token'])){
        $aUrl = sprintf($aapi,$WXres['access_token']);
        $data = json_encode(['path' => $path, 'width' => 430]);
        $aqrcode = \getData($aUrl, false, '', false, $data);

        //文件保存目录路径
        $save_path = C('SavePicPath');
        //文件保存目录URL
        $save_url = C('SavePicUrl');
        if (!file_exists($save_path)) {
            mkdir($save_path);
        }
        $save_path = realpath($save_path).'/';

        $ymd = date("Ymd");
        $save_path .= $ymd . "/";
        $save_url .= '/'.$ymd . "/";
        if (!file_exists($save_path)) {
            mkdir($save_path);
        }
        //新文件名
        $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.png';
        //移动文件
        $file_path = $save_path . $new_file_name;
        $file_url = $save_url . $new_file_name;
        if(file_put_contents($file_path, base64_decode($aqrcode))){
            return $file_url;
        }
    }

    return false;
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
