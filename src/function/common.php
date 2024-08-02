<?php

/**
 * xml的key做处理
 */
function _cc_parse_xml_key($string = '')
{
    // 使用正则表达式匹配驼峰命名规则
    //  if (preg_match('/^(?:[A-Z][a-z]+|[a-z]+)(?:[A-Z][a-z]+)*$/', $string)) {
    // }
    // 驼峰转下划线
    $str = \think\helper\Str::snake($string);
    // 下划线转驼峰
    $str = \think\helper\Str::studly($str);
    return $str;
}

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
 * 生产  $length 位随机码  函数
 * @param $length
 * @param bool|false $numeric
 * @return string 生成指定长度的唯一随机字符串并返回
 *
 */
function ctoStrRandNS($length = 6, $numeric = false, $exper = '')
{
    // PHP_VERSION < '4.2.0' ? mt_srand ( ( double ) microtime () * 1000000 ) : mt_srand ();
    $sign = microtime() . $_SERVER['DOCUMENT_ROOT'];
    // $sign = $exper . print_r ( $_SERVER, 1 ) . microtime ();
    $seed = base_convert(md5($sign), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 *  手机号中间位数用****代替
 * @author ctocode-zwj
 * @param string $phone
 * @return mixed
 */
function ctoStrHideTel($phone, $num = 4)
{
    $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); // 固定电话
    if ($IsWhat == 1) {
        return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1' . str_repeat('*', $num) . '$2', $phone);
    } else {
        return preg_replace('/(1[3|4|5|6|7|8|9]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1' . str_repeat('*', $num) . '$2', $phone);
    }
}
/**
 * 截取
 * 中文字符串截取 长度指字节数 字母一字节 汉字两字节
 * @author ctocode
 * @version 2016-03-28
 * @param string $str 需要截取的数组
 * @param int $max_length 截取的长度
 * @param string $istags 是否去除html标签
 * @return mixed
 */
function ctoStrStrcut($str, $max_length, $istags = true)
{
    /* 去除html 标签,并且 截取 一段文字 */
    if ($istags != false) {
        $content = strip_tags($str);
    }
    // 按照字节来划分(不会出现乱码)
    $str = mb_strcut($content, 0, $max_length, 'utf-8');
    // $str = mb_substr ( $str, 0, $max_length, 'utf-8' );// 函数2
    return $str;
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
