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