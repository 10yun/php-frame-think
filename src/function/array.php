<?php


/**
 * 重新指定数组的索引
 * @param array $arr 数组
 * @param string $key 键名
 * @return array
 */
function _cc_array_reindex($arr, $key)
{
    if (!is_array($arr) || empty($arr)) {
        return array();
    }
    $row = array_shift($arr);
    if (!array_key_exists($key, $row)) {
        return array();
    }
    array_unshift($arr, $row);
    $res = array();
    foreach ($arr as $row) {
        $res[$row[$key]] = $row;
    }
    return $res;
}

/**
 * 提取数组某一个key的值
 * @param array $arr
 * @param string $extract_key
 * @return array
 */
function _cc_array_extract($arr, $extract_key)
{
    $res = array();
    if (is_array($arr)) {
        foreach ($arr as $row) {
            $res[] = $row[$extract_key];
        }
    }

    return $res;
}

function _cc_array_ksort($list)
{
    ksort($list);
    foreach ($list as $k => $v) {
        if (is_array($v)) {
            $list[$k] = _cc_array_ksort($v);
        }
    }
    return $list;
}

/**
 * 判断是否二维数组
 * @param $array
 * @return bool
 */
function __cc_arrayIsTwo($array)
{
    if (!is_array($array)) {
        return false;
    }
    $json = __cc_array2json($array);
    return (bool)__cc_strLeftExists($json, '[');
}
function __cc_arrayStringRemoveEmpty($arr)
{
    $newArr = array();
    foreach ($arr as $str) {
        if ($str !== '') {
            $newArr[] = $str;
        }
    }
    return $newArr;
}

function __cc_arrayRemove($nums, $val)
{
    $result = []; // 初始化一个空数组来存储结果  
    foreach ($nums as $num) { // 遍历原始数组  
        if ($num != $val) { // 如果当前元素不等于要移除的值  
            $result[] = $num; // 将当前元素添加到结果数组中  
        }
    }
    return $result; // 返回结果数组  
}

function __cc_arrayDifferenceProcessing($a, $b)
{
    $b_set = array_flip($b); // 将数组 $b 转换为键值对，键是元素值，值是 true  
    $diff = []; // 初始化差集数组  

    foreach ($a as $item) {
        if (!isset($b_set[$item])) { // 如果 $b_set 中不存在该元素  
            $diff[] = $item; // 则将该元素添加到差集数组中  
        }
    }

    return $diff; // 返回差集数组  
}

function __cc_arrayDifferenceAddProcessing($a, $b)
{
    $b_set = array_flip($b); // 将数组 $b 转换为键值对，键是元素值，值是 true  
    $diff = []; // 初始化差集数组  

    foreach ($a as $item) {
        if (!isset($b_set[$item])) { // 如果 $b_set 中不存在该元素  
            $diff[] = $item; // 则将该元素添加到差集数组中  
        }
    }

    return $diff; // 返回差集数组  
}
/**
 * 将字符串转换为数组
 * @param string $data 字符串
 * @param array $default 为空时返回的默认数组
 * @return    array    返回数组格式，如果，data为空，则返回$default
 */
function __cc_string2array($data, $default = [])
{
    if (is_array($data)) {
        return $data ?: $default;
    }
    $data = trim($data);
    if ($data == '') return $default;
    if (str_starts_with(strtolower($data), 'array') && strtolower($data) !== 'array') {
        @ini_set('display_errors', 'on');
        @eval("\$array = $data;");
        @ini_set('display_errors', 'off');
    } else {
        if (str_starts_with($data, '{\\')) {
            $data = stripslashes($data);
        }
        $array = json_decode($data, true);
    }
    return isset($array) && is_array($array) && $data ? $array : $default;
}

/**
 * 将数组转换为字符串
 * @param array $data 数组
 * @param int $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默认为1
 * @return    string    返回字符串，如果，data为空，则返回空
 */
function __cc_array2string($data, $isformdata = 1)
{
    if ($data == '' || empty($data)) return '';
    if ($isformdata) $data = __cc_newStripslashes($data);
    if (version_compare(PHP_VERSION, '5.3.0', '<')) {
        return __cc_newAddslashes(json_encode($data));
    } else {
        return __cc_newAddslashes(json_encode($data, JSON_FORCE_OBJECT));
    }
}
/**
 * json字符串转换成array
 * @param $string
 * @return array|mixed
 */
function __cc_json2array($string)
{
    if (is_array($string)) {
        return $string;
    }
    try {
        $array = json_decode($string, true);
        return is_array($array) ? $array : [];
    } catch (\Throwable) {
        return [];
    }
}

/**
 * array转换成功json字符串
 * @param $array
 * @param int $options
 * @return string
 */
function __cc_array2json($array, $options = 0)
{
    if (!is_array($array)) {
        return $array;
    }
    try {
        return json_encode($array, $options);
    } catch (\Throwable) {
        return '';
    }
}
/**
 * 数组拼接字符串（前后也加上）
 * @param $glue
 * @param $pieces
 * @param $around
 * @return string
 */
function __cc_arrayImplode($glue = "", $pieces = null, $around = true)
{
    if ($pieces == null) {
        $pieces = $glue;
        $glue = ',';
    }
    $pieces = array_values(array_filter(array_unique($pieces)));
    $string = implode($glue, $pieces);
    if ($around && $string) {
        $string = ",{$string},";
    }
    return $string;
}

/**
 * 数组只保留数字的
 * @param $array
 * @param bool $reInt 是否格式化值
 * @return array
 */
function __cc_arrayRetainInt($array, $reInt = false)
{
    if (!is_array($array)) {
        return $array;
    }
    foreach ($array as $k => $v) {
        if (!is_numeric($v)) {
            unset($array[$k]);
        } elseif ($reInt === true) {
            $array[$k] = intval($v);
        }
    }
    return array_values($array);
}

/**
 * 多维 array_values
 * @param $array
 * @param string $keyName
 * @param string $valName
 * @return array
 */
function array_values_recursive($array, $keyName = 'key', $valName = 'item')
{
    if (is_array($array) && count($array) > 0) {
        $temp = [];
        foreach ($array as $key => $value) {
            $continue = false;
            if (is_array($value) && count($value) > 0) {
                $continue = true;
                foreach ($value as $item) {
                    if (!is_array($item)) {
                        $continue = false;
                        break;
                    }
                }
            }
            $temp[] = [
                $keyName => $key,
                $valName => $continue ? array_values_recursive($value, $keyName, $valName) : $value,
            ];
        }
        return $temp;
    }
    return $array;
}

/**
 * 多维数组字母转下划线格式
 * @param $array
 * @return array
 */
function __cc_arrayKeyToUnderline($array)
{
    $newArray = [];
    foreach ($array as $key => $value) {
        //如果是数组，递归调用
        if (is_array($value)) {
            $value = __cc_arrayKeyToUnderline($value);
        }
        $newKey = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $key));
        $newArray[$newKey] = $value;
    }
    return $newArray;
}
/**
 * 多维数组字母转驼峰格式
 *
 * @param [type] $array
 * @return array
 */
function __cc_arrayKeyToCamel($array)
{
    $newArray = [];
    foreach ($array as $key => $value) {
        //如果是数组，递归调用
        if (is_array($value)) {
            $value = __cc_arrayKeyToCamel($value);
        }
        $newKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
        $newArray[$newKey] = $value;
    }
    return $newArray;
}
/**
 * 获取数组的第几个值
 * @param $arr
 * @param int $i
 * @return array
 */
function __cc_getArray($arr, $i = 1)
{
    $array = [];
    $j = 1;
    foreach ($arr as $item) {
        $array[] = $item;
        if ($i >= $j) {
            break;
        }
        $j++;
    }
    return $array;
}
