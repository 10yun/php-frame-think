<?php

/**
 * 【ctocode】      字符串
 * ============================================================================
 * @author       作者         ctocode-zhw
 * @version 	  版本	  v5.7.1.20210514
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

/**
 * 生产  $length 位随机码  函数
 * @param $length
 * @param bool|false $numeric
 * @return string 生成指定长度的唯一随机字符串并返回
 *
 */
function ctoStrRandNS($length = 6, $numeric = false, $exper = '')
{
    // PHP_VERSION < '4.2.0' ? mt_srand ( ( double ) microtime () * 1000000 ) : mt_srand ();
    $sign = microtime() . $_SERVER['DOCUMENT_ROOT'];
    // $sign = $exper . print_r ( $_SERVER, 1 ) . microtime ();
    $seed = base_convert(md5($sign), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}
function ctoStrNumberEncode($s)
{
    preg_match_all('/([a-z]+)|([0-9]+)|([^0-9a-z]+)/i', $s, $t);
    foreach ($t[0] as $v) {
        foreach (str_split($v, 1) as $c)
            $r[] = (ord($c) > 127 ? 1255 : 999) - ord($c);
    }
    return implode('', $r);
}
function ctoStrNumberDecode($s)
{
    preg_match_all('/1?\d{3}/', $s, $t);
    $r = '';
    foreach ($t[0] as $v)
        // 旧的 $val{0}
        $r .= chr(($v[0] == 1 ? 1255 : 999) - $v);
    return $r;
}
/*
 * 数字加密
 */
function ctoStrNumberEncode2($tex, $key = null)
{
    // 1、md5 密钥key     -》 A 
    // 2、正则去除 md5 里面的数子 -》 A->B
    // 3、取2位  -》 B -> C
    // 4、C 加密md5 => D
    // 
    $key = $key ? $key : "test";
    $md5str = preg_replace('|[0-9/]+|', '', md5($key));
    $key = substr($md5str, 0, 2);
    $rand_key = md5($key);

    // 6.1 获取【需要加密的数字】 的长度
    // 6.2  【需要加密的数字】 和 D 进行  ^ 异或计算 
    // 循环 【需要加密的数字】 每位数字 和 
    $texlen = strlen($tex);
    $reslutstr = "";

    for ($i = 0; $i < $texlen; $i++) {
        // var_dump($tex[$i], $rand_key[$i % 32], ("{$tex[$i]}" ^ "{$rand_key[$i % 32]}"));
        // 这边的异或计算，是2个字符串的 计算，不是单纯数组 
        // java 那边要 (char)(a ^ b)；
        $reslutstr .= ($tex[$i] ^ $rand_key[$i % 32]);
    }
    // 7  md5 异或的值
    $reslutstr = trim(base64_encode($reslutstr), "==");
    $reslutstr = $key . substr(md5($reslutstr), 0, 3) . $reslutstr;
    return $reslutstr;
}
/*
 * 解密
 */
function ctoStrNumberDecode2($tex)
{
    $key = substr($tex, 0, 2);
    $tex = substr($tex, 2);
    $verity_str = substr($tex, 0, 3);
    $tex = substr($tex, 3);
    if ($verity_str != substr(md5($tex), 0, 3)) {
        // 完整性验证失败
        return false;
    }
    $tex = base64_decode($tex);
    $texlen = strlen($tex);
    $reslutstr = "";
    $rand_key = md5($key);
    for ($i = 0; $i < $texlen; $i++) {
        $reslutstr .= $tex[$i] ^ $rand_key[$i % 32];
    }
    return $reslutstr;
}

/**
 *  手机号中间位数用****代替
 * @author ctocode-zwj
 * @param string $phone
 * @return mixed
 */
function ctoStrHideTel($phone, $num = 4)
{
    $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); // 固定电话
    if ($IsWhat == 1) {
        return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1' . str_repeat('*', $num) . '$2', $phone);
    } else {
        return preg_replace('/(1[3|4|5|6|7|8|9]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1' . str_repeat('*', $num) . '$2', $phone);
    }
}
/**
 * 截取
 * 中文字符串截取 长度指字节数 字母一字节 汉字两字节
 * @author ctocode
 * @version 2016-03-28
 * @param string $str 需要截取的数组
 * @param int $max_length 截取的长度
 * @param string $istags 是否去除html标签
 * @return mixed
 */
function ctoStrStrcut($str, $max_length, $istags = true)
{
    /* 去除html 标签,并且 截取 一段文字 */
    if ($istags != false) {
        $content = strip_tags($str);
    }
    // 按照字节来划分(不会出现乱码)
    $str = mb_strcut($content, 0, $max_length, 'utf-8');
    // $str = mb_substr ( $str, 0, $max_length, 'utf-8' );// 函数2
    return $str;
}
