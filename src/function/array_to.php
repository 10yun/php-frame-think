<?php

/**
 * 对象转数组
 */
function cc_object_to_array($array)
{
    if (is_object($array)) {
        $array = (array) $array;
        // $array = get_object_vars($array);
    } else if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = cc_object_to_array($value);
        }
    }
    return $array;
}
/**
 * 多维数组字母转下划线格式
 * @param $array
 * @return array
 */
function cc_array_key_to_underline($array)
{
    $newArray = [];
    foreach ($array as $key => $value) {
        //如果是数组，递归调用
        if (is_array($value)) {
            $value = cc_array_key_to_underline($value);
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
function cc_array_key_to_camel($array)
{
    $newArray = [];
    foreach ($array as $key => $value) {
        //如果是数组，递归调用
        if (is_array($value)) {
            $value = cc_array_key_to_camel($value);
        }
        $newKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
        $newArray[$newKey] = $value;
    }
    return $newArray;
}
