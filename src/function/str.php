<?php

/**
 * 替换所有空格
 * @param $str
 * @return mixed
 */
function __cc_strRemoveSpace($str)
{
    $str = str_replace("  ", " ", $str);
    if (str_contains($str, "  ")) {
        return __cc_strRemoveSpace($str);
    }
    return $str;
}

/**
 * @param string $str 补零
 * @param int $length
 * @param int $after
 * @return bool|string
 */
function __cc_strZeroFill($str, $length = 0, $after = 1)
{
    if (strlen($str) >= $length) {
        return $str;
    }
    $_str = '';
    for ($i = 0; $i < $length; $i++) {
        $_str .= '0';
    }
    if ($after) {
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
function __cc_strExists($string, $find)
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
function __cc_strLeftExists($string, $find, $lower = false)
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
function __cc_strLeftDelete($string, $find, $lower = false)
{
    if (__cc_strLeftExists($string, $find, $lower)) {
        $string = substr($string, strlen($find));
    }
    return $string ?: '';
}

/**
 * 截取指定字符串
 * @param $str
 * @param string $ta
 * @param string $tb
 * @return string
 */
function __cc_getMiddle($str, $ta = '', $tb = '')
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
 * 判断字符串结尾包含
 * @param string $string //原字符串
 * @param string $find //判断字符串
 * @param bool|false $lower //是否不区分大小写
 * @return int
 */
function __cc_rightExists($string, $find, $lower = false)
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
function __cc_rightDelete($string, $find, $lower = false)
{
    if (__cc_rightExists($string, $find, $lower)) {
        $string = substr($string, 0, strlen($find) * -1);
    }
    return $string;
}
