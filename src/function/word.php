<?php

/**
 * 阿拉伯数字转化为中文
 * @param $num
 * @return string
 */
function __cc_chinaNum($num)
{
    $china = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
    $arr = str_split($num);
    $txt = '';
    for ($i = 0; $i < count($arr); $i++) {
        $txt .= $china[$arr[$i]];
    }
    return $txt;
}

/**
 * 阿拉伯数字转化为中文（用于星期，七改成日）
 * @param $num
 * @return string
 */
function __cc_chinaNumZ($num)
{
    return str_replace("七", "日", __cc_chinaNum($num));
}

/**
 * 用户名、邮箱、手机帐号、银行卡号中间字符串以*隐藏
 * @param $str
 * @return string
 */
function __cc_cardFormat($str)
{
    if (strpos($str, '@')) {
        $email_array = explode("@", $str);
        $prevfix = substr($str, 0, strlen($email_array[0]) < 4 ? 1 : 3); //邮箱前缀
        $count = 0;
        $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
        return $prevfix . $str;
    }
    if (__cc_isMobile($str)) {
        return substr($str, 0, 3) . "****" . substr($str, -4);
    }
    $pattern = '/([\d]{4})([\d]{4})([\d]{4})([\d]{4})([\d]*)?/i';
    if (preg_match($pattern, $str)) {
        return preg_replace($pattern, '$1 **** **** **** $5', $str);
    }
    $pattern = '/([\d]{4})([\d]{4})([\d]{4})([\d]*)?/i';
    if (preg_match($pattern, $str)) {
        return preg_replace($pattern, '$1 **** **** $4', $str);
    }
    $pattern = '/([\d]{4})([\d]{4})([\d]*)?/i';
    if (preg_match($pattern, $str)) {
        return preg_replace($pattern, '$1 **** $3', $str);
    }
    return substr($str, 0, 3) . "***" . substr($str, -1);
}

/**
 * 字节转格式
 * @param $bytes
 * @return string
 */
function __cc_readableBytes($bytes)
{
    $i = floor(log($bytes) / log(1024));
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
}
/**
 * 去除emoji表情
 * @param $str
 * @return string|string[]|null
 */
function __cc_filterEmoji($str)
{
    return preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str
    );
}
