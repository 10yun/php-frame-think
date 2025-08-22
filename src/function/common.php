<?php

/**
 * @todo 循环分多次
 * @author ctocode-zwj
 * @version 2018/08/31
 */
function ctoArraOper($array, $num = 1000)
{
    $count = count($array) / $num;
    for ($i = 0; $i < $count; $i++) {
        $return_arr[$i] = array_slice($array, $num * $i, $num);
    }
    return $return_arr;
}
/**
 *  手机号中间位数用****代替
 * @author ctocode-zwj
 * @param string $phone
 * @return mixed
 */
function cc_str_hide_mobile($phone, $num = 4)
{
    $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); // 固定电话
    if ($IsWhat == 1) {
        return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1' . str_repeat('*', $num) . '$2', $phone);
    } else {
        return preg_replace('/(1[3|4|5|6|7|8|9]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1' . str_repeat('*', $num) . '$2', $phone);
    }
}

/**
 * 多数数组 替换
 * @param string $search 需要替换的关键
 * @param string $replace 替换成什么
 * @param mixed $array
 */
function ctoArrayStrReplace($search = '', $replace = '', &$array = [])
{
    $array = str_replace($search, $replace, $array);
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                ctoArrayStrReplace($search = '', $replace = '', $array[$key]);
            }
        }
    }
}

/**
 * TODO 转换：tree 转 arr
 * @action  解析 树形数组 ,
 * @param array $data 传递进来的数组
 * @param array $return_arr 引用数组
 * @param string $is_self  是否 把自己克隆到   引用数组里
 * @param string $tree_key  递归树形标识
 * @remarks  使用方法：ctoArrayTreeDe($data, $arr , TRUE,'tree');
 * 缺点：只能传递单一树形
 * 需要完善：传入自定义保存字段
 */
function ctoArrayTreeDe($data, &$return_arr = NULL, $is_self = FALSE, $tree_key = NULL)
{
    if (empty($tree_key)) {
        $tree_key = 'tree';
    }
    if ($is_self == TRUE) {
        $_clone = array();
        $_clone = $data;
        unset($_clone[$tree_key]);
        $return_arr[] = $_clone;
    }
    if (!empty($data[$tree_key])) {
        foreach ($data[$tree_key] as $k => $v) {
            ctoArrayTreeDe($v, $return_arr, $is_self, $tree_key);
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
