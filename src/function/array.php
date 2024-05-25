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
