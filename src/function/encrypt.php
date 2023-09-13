<?php


// echo ctoValueAuthcode ( 'ucneter', 'ENCODE' );
// echo ctoValueAuthcode ( '9d7dvoyZfZqITJseC9i9pWPxuWZkSQkfKLyAdxSJjsQHJUBh' );
/**
 * @param string $string 原文或者密文
 * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
 * @param string $key 密钥
 * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
 * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
 *
 * @example
 *  $a = authcode('abc', 'ENCODE', 'key');
 *  $b = authcode($a, 'DECODE', 'key');  // $b(abc)
 *
 *  $a = authcode('abc', 'ENCODE', 'key', 3600);
 *  $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
 */
// string(字符串) operation(DECODE-解密 其他-加密) key(混淆字符) expiry(过期时间) fixed(加密结果是否固定)
function ctoValueAuthcode($string, $operation = 'DECODE', $key = '', $expiry = 3600, $fixed = false)
{
    $ckey_length = $fixed ? 0 : 4;
    // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥

    $key = md5($key ? $key : 'default_key'); // 这里可以填写默认key值
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';

    $jiami_leng1 = 255;
    $jiami_leng2 = 256;

    $box = range(0, $jiami_leng1);

    $rndkey = array();
    for ($i = 0; $i <= $jiami_leng1; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < $jiami_leng2; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % $jiami_leng2;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % $jiami_leng2;
        $j = ($j + $box[$a]) % $jiami_leng2;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % $jiami_leng2]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
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
