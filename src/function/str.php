<?php

/**
 *
 * 截取字符串
 * @param string $string 字符串
 * @param int $length 截取长度
 * @param int $start 何处开始
 * @param string $dot 超出尾部添加
 * @param string $charset 默认编码
 * @return string
 */
function __base_cutStr($string, $length, $start = 0, $dot = '', $charset = 'utf-8')
{
    if (strtolower($charset) == 'utf-8') {
        if (__base_getStrlen($string) <= $length) return $string;
        $strcut = __base_utf8Substr($string, $length, $start);
        return $strcut . $dot;
    } else {
        $length = $length * 2;
        if (strlen($string) <= $length) return $string;
        $strcut = '';
        for ($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }
    return $strcut . $dot;
}

/**
 * PHP获取字符串中英文混合长度
 * @param string $str 字符串
 * @param string $charset 编码
 * @return float            返回长度，1中文=1位，2英文=1位
 */
function __base_getStrlen($str, $charset = 'utf-8')
{
    if (strtolower($charset) == 'utf-8') {
        $str = iconv('utf-8', 'GBK//IGNORE', $str);
    }
    $num = strlen($str);
    $cnNum = 0;
    for ($i = 0; $i < $num; $i++) {
        if (ord(substr($str, $i + 1, 1)) > 127) {
            $cnNum++;
            $i++;
        }
    }
    $enNum = $num - ($cnNum * 2);
    $number = ($enNum / 2) + $cnNum;
    return ceil($number);
}

/**
 * PHP截取UTF-8字符串，解决半字符问题。
 * @param string $str 源字符串
 * @param int $len 左边的子串的长度
 * @param int $start 何处开始
 * @return string           取出的字符串, 当$len小于等于0时, 会返回整个字符串
 */
function __base_utf8Substr($str, $len, $start = 0)
{
    $len = $len * 2;
    $new_str = [];
    for ($i = 0; $i < $len; $i++) {
        $temp_str = substr($str, 0, 1);
        if (ord($temp_str) > 127) {
            $i++;
            if ($i < $len) {
                $new_str[] = substr($str, 0, 3);
                $str = substr($str, 3);
            }
        } else {
            $new_str[] = substr($str, 0, 1);
            $str = substr($str, 1);
        }
    }
    return join(array_slice($new_str, $start));
}
/**
 * 自定义替换次数
 * @param $search
 * @param $replace
 * @param $subject
 * @param int $limit
 * @return string|string[]|null
 */
function __cc_strReplaceLimit($search, $replace, $subject, $limit = -1)
{
    if (is_array($search)) {
        foreach ($search as $k => $v) {
            $search[$k] = '`' . preg_quote($v, '`') . '`';
        }
    } else {
        $search = '`' . preg_quote($search, '`') . '`';
    }
    return preg_replace($search, $replace, $subject, $limit);
}
/**
 * 截取
 * 中文字符串截取 长度指字节数 字母一字节 汉字两字节
 * @author ctocode
 * @version 2016-03-28
 * @param string $str 需要截取的数组
 * @param int $maxLength 截取的长度
 * @param string $isTags 是否去除html标签
 * @return mixed
 */
function cc_str_strcut(string $str, int $maxLength, $isTags = true)
{
    /* 去除html 标签,并且 截取 一段文字 */
    if ($isTags != false) {
        $content = strip_tags($str);
    }
    // 按照字节来划分(不会出现乱码)
    $str = mb_strcut($content, 0, $maxLength, 'utf-8');
    // $str = mb_substr ( $str, 0, $maxLength, 'utf-8' );// 函数2
    return $str;
}
/**
 * 截取指定字符串
 * @param $str
 * @param string $ta
 * @param string $tb
 * @return string
 */
function cc_str_strcut_middle($str, $ta = '', $tb = '')
{
    if ($ta && str_contains($str, $ta)) {
        $str = substr($str, strpos($str, $ta) + strlen($ta));
    }
    if ($tb && str_contains($str, $tb)) {
        $str = substr($str, 0, strpos($str, $tb));
    }
    return $str;
}
/**
 * 替换所有空格
 * @param $str
 * @return mixed
 */
function cc_str_remove_space($str)
{
    $str = str_replace("  ", " ", $str);
    if (str_contains($str, "  ")) {
        return cc_str_remove_space($str);
    }
    return $str;
}
/**
 * @action 补零
 * @param string $str 补零
 * @param int $length 长度
 * @param int $before 是否补在前面
 * @return bool|string
 */
function cc_str_zero_fill($str, $length = 0, $before = true)
{
    if (strlen($str) >= $length) {
        return $str;
    }
    $_str = '';
    for ($i = 0; $i < $length; $i++) {
        $_str .= '0';
    }
    if ($before) {
        $_ret = substr($_str . $str, $length * -1);
    } else {
        $_ret = substr($str . $_str, 0, $length);
    }
    return $_ret;
}

/**
 * 判断字符串存在(包含)
 * @param string $string
 * @param string $find
 * @return bool
 */
function cc_str_exists($string, $find)
{
    if (!is_string($string) || !is_string($find)) {
        return false;
    }
    return str_contains($string, $find);
}

/**
 * 判断字符串开头包含
 * @param string $string //原字符串
 * @param string $find //判断字符串
 * @param bool|false $lower //是否不区分大小写
 * @return bool
 */
function cc_str_left_exists($string, $find, $lower = false)
{
    if (!is_string($string) || !is_string($find)) {
        return false;
    }
    if ($lower) {
        $string = strtolower($string);
        $find = strtolower($find);
    }
    return str_starts_with($string, $find);
}
/**
 * 删除开头指定字符串
 * @param $string
 * @param $find
 * @param bool $lower
 * @return string
 */
function cc_str_left_del($string, $find, $lower = false)
{
    if (cc_str_left_exists($string, $find, $lower)) {
        $string = substr($string, strlen($find));
    }
    return $string ?: '';
}


/**
 * 判断字符串结尾包含
 * @param string $string //原字符串
 * @param string $find //判断字符串
 * @param bool|false $lower //是否不区分大小写
 * @return int
 */
function cc_str_right_exists($string, $find, $lower = false)
{
    if (!is_string($string) || !is_string($find)) {
        return false;
    }
    if ($lower) {
        $string = strtolower($string);
        $find = strtolower($find);
    }
    return str_ends_with($string, $find);
}
/**
 * 删除结尾指定字符串
 * @param $string
 * @param $find
 * @param bool $lower
 * @return string
 */
function cc_str_right_del($string, $find, $lower = false)
{
    if (cc_str_right_exists($string, $find, $lower)) {
        $string = substr($string, 0, strlen($find) * -1);
    }
    return $string;
}
