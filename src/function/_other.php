<?php

/**
 * 检测日期格式
 * @param string $str 需要检测的字符串
 * @return bool
 */
function __cc_isDate($str = '')
{
    $strArr = explode('-', $str);
    if (empty($strArr) || count($strArr) != 3) {
        return false;
    } else {
        list($year, $month, $day) = $strArr;
        if (checkdate(intval($month), intval($day), intval($year))) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 检测时间格式
 * @param string $str 需要检测的字符串
 * @return bool
 */
function __cc_isTime($str = '')
{
    $strArr = explode(':', $str);
    $count = count($strArr);
    if ($count < 2 || $count > 3) {
        return false;
    }
    $hour = $strArr[0];
    if ($hour < 0 || $hour > 23) {
        return false;
    }
    $minute = $strArr[1];
    if ($minute < 0 || $minute > 59) {
        return false;
    }
    if ($count == 3) {
        $second = $strArr[2];
        if ($second < 0 || $second > 59) {
            return false;
        }
    }
    return true;
}

/**
 * 检测 日期格式 或 时间格式
 * @param string $str 需要检测的字符串
 * @return bool
 */
function __cc_isDateOrTime($str = '')
{
    return __cc_isDate($str) || __cc_isTime($str);
}

/**
 * 检测手机号码格式
 * @param string $str 需要检测的字符串
 * @return bool
 */
function __cc_isMobile($str = '')
{
    if (preg_match("/^1([3456789])\d{9}$/", $str)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检测邮箱格式
 * @param $str
 * @return bool
 */
function __cc_isEmail($str = '')
{
    if (filter_var($str, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 正则判断是否纯数字
 * @param $str
 * @return bool
 */
function __cc_isNumber($str = '')
{
    if (preg_match("/^\d+$/", $str)) {
        return true;
    } else {
        return false;
    }
}
/**
 * 正则判断是否MAC地址
 * @param $str
 * @return bool
 */
function __cc_isMac($str = '')
{
    if (preg_match("/^[A-Fa-f\d]{2}:[A-Fa-f\d]{2}:[A-Fa-f\d]{2}:[A-Fa-f\d]{2}:[A-Fa-f\d]{2}:[A-Fa-f\d]{2}$/", $str)) {
        return true;
    } else {
        return false;
    }
}
/**
 * 判断身份证是否正确
 * @param $id
 * @return bool
 */
function __cc_isIdcard($id = '')
{
    $id = strtoupper($id);
    $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = array();
    if (!preg_match($regx, $id)) {
        return FALSE;
    }
    if (15 == strlen($id)) {
        //检查15位
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
        @preg_match($regx, $id, $arr_split);
        //检查生日日期是否正确
        $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) {
            return FALSE;
        } else {
            return TRUE;
        }
    } else {
        //检查18位
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        //检查生日日期是否正确
        if (!strtotime($dtm_birth)) {
            return FALSE;
        } else {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $sign = 0;
            for ($i = 0; $i < 17; $i++) {
                $b = (int)$id[$i];
                $w = $arr_int[$i];
                $sign += $b * $w;
            }
            $n = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id, 17, 1)) {
                return FALSE;
            } else {
                return TRUE;
            }
        }
    }
}

/**
 * 保留两位小数点
 * @param $str
 * @param bool $float
 * @return float
 */
function __cc_twoFloat($str, $float = false)
{
    $str = sprintf("%.2f", floatval($str));
    if ($float === true) {
        $str = floatval($str);
    }
    return $str;
}

/**
 * 判断两个地址域名是否相同
 * @param string $var1
 * @param string|array $var2
 * @return bool
 */
function __cc_hostContrast($var1, $var2)
{
    $arr1 = parse_url($var1);
    $host1 = $arr1['host'] ?? $var1;
    //
    $host2 = [];
    foreach (is_array($var2) ? $var2 : [$var2] as $url) {
        $arr2 = parse_url($url);
        $host2[] = $arr2['host'] ?? $url;
    }
    return in_array($host1, $host2);
}

/**
 * 随机字符串
 * @param int $length 随机字符长度
 * @param string $type
 * @return string 1数字、2大小写字母、21小写字母、22大写字母、默认全部;
 */
function __cc_generatePassword($length = 8, $type = '')
{
    // 密码字符集，可任意添加你需要的字符
    switch ($type) {
        case '1':
            $chars = '0123456789';
            break;
        case '2':
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case '21':
            $chars = 'abcdefghijklmnopqrstuvwxyz';
            break;
        case '22':
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        default:
            $chars = $type ?: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            break;
    }
    $passwordstr = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $passwordstr .= $chars[mt_rand(0, $max)];
    }
    return $passwordstr;
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param array|string $string 需要处理的字符串或数组
 * @return array|int|string
 */
function __cc_newStripslashes($string)
{
    if (is_numeric($string)) {
        return $string;
    } elseif (!is_array($string)) {
        return stripslashes($string);
    }
    foreach ($string as $key => $val) $string[$key] = __cc_newStripslashes($val);
    return $string;
}

/**
 * 返回经addslashes处理过的字符串或数组
 * @param array|string $string 需要处理的字符串或数组
 * @return array|int|string
 */
function __cc_newAddslashes($string)
{
    if (is_numeric($string)) {
        return $string;
    } elseif (!is_array($string)) {
        return addslashes($string);
    }
    foreach ($string as $key => $val) $string[$key] = __cc_newAddslashes($val);
    return $string;
}

/**
 * 返回经trim处理过的字符串或数组
 * @param $string
 * @return array|string
 */
function __cc_newTrim($string)
{
    if (!is_array($string)) return trim($string);
    foreach ($string as $key => $val) $string[$key] = __cc_newTrim($val);
    return $string;
}

/**
 * 返回经intval处理过的字符串或数组
 * @param $string
 * @return array|int
 */
function __cc_newIntval($string)
{
    if (!is_array($string)) return intval($string);
    foreach ($string as $key => $val) $string[$key] = __cc_newIntval($val);
    return $string;
}

/**
 * 地址后拼接参数
 * @param $url
 * @param $parames
 * @return mixed|string
 */
function __cc_urlAddparameter($url, $parames)
{
    if ($parames && is_array($parames)) {
        $array = [];
        foreach ($parames as $key => $val) {
            $array[] = $key . "=" . $val;
        }
        if ($array) {
            $query = implode("&", $array);
            if (str_contains($url, "?")) {
                $url .= "&" . $query;
            } else {
                $url .= "?" . $query;
            }
        }
    }
    return $url;
}
