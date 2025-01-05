<?php

use shiyun\support\Cache;
use shiyun\support\Request;
use app\exceptions\ApiException;

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
 * 获取主地址
 * @return string   如：http://127.0.0.1:8080
 */
function __base_getSchemeAndHost()
{
    $scheme = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    return $scheme . ($_SERVER['HTTP_HOST'] ?? '');
}

/**
 * 相对路径补全
 * @param string|array $str
 * @return string|array
 */
function __base_fillUrl($str = '')
{
    if (is_array($str)) {
        foreach ($str as $key => $item) {
            $str[$key] = __base_fillUrl($item);
        }
        return $str;
    }
    if (empty($str)) {
        return $str;
    }
    if (
        str_starts_with($str, "//") ||
        str_starts_with($str, "http://") ||
        str_starts_with($str, "https://") ||
        str_starts_with($str, "ftp://") ||
        str_starts_with($str, "/") ||
        str_starts_with(str_replace(' ', '', $str), "data:image/")
    ) {
        return $str;
    } else {
        // if ($_A['__fill_url_remote_url'] === true) {
        return "{{RemoteURL}}" . $str;
        // }
        try {
            return url($str);
        } catch (\Throwable) {
            return __base_getSchemeAndHost() . "/" . $str;
        }
    }
}
/**
 * 打散字符串，只留为数字的项
 * @param $delimiter
 * @param $string
 * @param bool $reInt 是否格式化值
 * @return array
 */
function __base_explodeInt($delimiter, $string = null, $reInt = true)
{
    if ($string == null) {
        $string = $delimiter;
        $delimiter = ',';
    }
    $array = is_array($string) ? $string : explode($delimiter, $string);
    return __cc_arrayRetainInt($array, $reInt);
}
/**
 * 是否错误
 * @param $param
 * @return bool
 */
function __base_isError($param)
{
    return !isset($param['ret']) || intval($param['ret']) <= 0;
}
/**
 * 获取时间戳
 * @param bool $refresh
 * @return int
 */
function base_static_time($refresh = false)
{
    global $_A;
    if (!isset($_A["__static_time"]) || $refresh === true) {
        $_A["__static_time"] = time();
    }
    return $_A["__static_time"];
}
/**
 * 获取版本号
 * @return string
 */
function base_getVersion()
{
    return '1.0.0';
}
/**
 * 如果header没有则通过input读取
 * @param $key
 * @return mixed|string
 */
function base__headerOrInput($key)
{
    $key1 = Request::header($key);
    $key2 = Request::get($key);
    return !empty($key1) ? $key1 : $key2;
}
/**
 * 获取客户端版本号
 * @return string
 */
function base_getClientVersion()
{
    return base__headerOrInput('version') ?? $version3 = '0.0.1';;
}

/**
 * 检查客户端版本
 * @param string $min 最小版本
 * @return void
 */
function base__checkClientVersion($min)
{
    if (!base__judgeClientVersion($min)) {
        throw new ApiException('当前版本 (v' . base_getClientVersion() . ') 过低，最低版本要求 (v' . $min . ')。');
    }
}
/**
 * 判断客户端版本
 * @param $min // 最小版本（满足此版本返回true）
 * @param null $clientVersion
 * @return bool
 */
function base__judgeClientVersion($min, $clientVersion = null)
{
    return !version_compare($clientVersion ?: base_getClientVersion(), $min, '<');
}
