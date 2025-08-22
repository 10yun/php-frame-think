<?php

/**
 * @title 随机生成
 */


/**
 * @action 生成随机浮点数
 */
function cc_random_float($min, $max)
{
    return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
}
/**
 * 生成随机码【 数字】 
 * @param string $length 长度，默认4
 */
function cc_random_number($length = 4)
{
    $num = "";
    for ($i = 0; $i < $length; $i++) {
        $id = rand(0, 9);
        $num = $num . $id;
    }
    return $num;
}
/**
 * @action 生成随机整数（0123456789）
 * @param int $length 随机字符长度
 */
function cc_random_int($length = 8)
{
    $chars = '0123456789';
    $random_str = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $random_str .= $chars[random_int(0, $max)];
    }
    return $random_str;
}
/**
 * @action 生成随机英文（含大小写）
 * @param int $length 随机字符长度，默认8
 */
function cc_random_letter(int $length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_str = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $random_str .= $chars[random_int(0, $max)];
    }
    return $random_str;
}
/**
 * @action 生成随机英文（小写）
 * @param int $length 随机字符长度
 */
function cc_random_lowercase(int $length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $random_str = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $random_str .= $chars[random_int(0, $max)];
    }
    return $random_str;
}
/**
 * @action 生成随机英文（大写）
 * @param int $length 随机字符长度
 */
function cc_random_uppercase(int $length = 8)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_str = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $random_str .= $chars[random_int(0, $max)];
    }
    return $random_str;
}
/**
 * @action 生成随机字符串（含大小写数字）
 * @param int $length 随机字符长度
 */
function cc_random_lcucnum(int $length = 8, $type = null)
{
    $chars = $type ?: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $random_str = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $random_str .= $chars[random_int(0, $max)];
    }
    return $random_str;
}
/**
 * @action 生成随机字符串（含小写数字）
 * @param int $length 不长于32位
 * @return string 随机字符串
 */
function cc_random_lowernum(int $length = 32)
{
    // 密码字符集，可任意添加你需要的字符
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        // $str .= $chars[random_int(0, strlen($chars) - 1)];
        $str .= substr($chars, random_int(0, strlen($chars) - 1), 1);
    }
    return $str;
}
