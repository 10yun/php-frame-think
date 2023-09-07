<?php

/**
 * 生成随机浮点数
 */
function _cc_random_float($min, $max)
{
    return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
}

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
 * 多数数组 替换
 * @param string $search 需要替换的关键
 * @param string $replace 替换成什么
 * @param mixed $array
 */
function ctoArrayStrReplace($search = '', $replace = '', &$array)
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
