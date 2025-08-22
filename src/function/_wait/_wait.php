<?php

/**
 * 阵列数组
 * @param $keys
 * @param $src
 * @param bool $default
 * @return array
 */
function arrayElements($keys, $src, $default = FALSE)
{
    $return = [];
    if (!is_array($keys)) {
        $keys = array($keys);
    }
    foreach ($keys as $key) {
        if (isset($src[$key])) {
            $return[$key] = $src[$key];
        } else {
            $return[$key] = $default;
        }
    }
    return $return;
}

/**
 * 将数组转换为字符串 (格式化)
 * @param array $data 数组
 * @param int $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默认为1
 * @return    string    返回字符串，如果，data为空，则返回空
 */
function array2string_discard($data, $isformdata = 1)
{
    if ($data == '' || empty($data)) return '';
    if ($isformdata) $data = __cc_newStripslashes($data);
    return var_export($data, TRUE);
}

/**
 * 重MD5加密
 * @param $text
 * @param string $pass
 * @return string
 */
function md52($text, $pass = '')
{
    $_text = md5($text) . $pass;
    return md5($_text);
}

/**
 * 数字每4位加一空格
 * @param $str
 * @param string $interval
 * @return string
 */
function fourFormat($str = '', $interval = " ")
{
    if (!is_numeric($str)) return $str;
    //
    $text = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $text .= $str[$i];
        if ($i % 4 == 3) {
            $text .= $interval;
        }
    }
    return $text;
}

/**
 * 叠加数组或对象
 * @param object|array $array
 * @param array $over
 * @return object|array
 */
function array_over(&$array, $over = [])
{
    if (is_array($over)) {
        foreach ($over as $key => $val) {
            if (is_array($array)) {
                $array[$key] = $val;
            }
            if (is_object($array)) {
                $array->$key = $val;
            }
        }
    }
    return $array;
}
/**
 * 获取数组第一个值
 * @param $array
 * @return mixed
 */
function arrayFirst($array)
{
    $val = '';
    if (is_array($array)) {
        foreach ($array as $item) {
            $val = $item;
            break;
        }
    }
    return $val;
}

/**
 * 获取数组最后一个值
 * @param $array
 * @return mixed
 */
function arrayLast($array)
{
    $val = '';
    if (is_array($array)) {
        foreach (array_reverse($array) as $item) {
            $val = $item;
            break;
        }
    }
    return $val;
}
/**
 * array转xml
 * @param $data
 * @param string $root 根节点
 * @return string
 */
function array2xml($data, $root = '<xml>')
{
    $str = "";
    if ($root) $str .= $root;
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $child = array2xml($val, false);
            $str .= "<$key>$child</$key>";
        } else {
            $str .= "<$key><![CDATA[$val]]></$key>";
        }
    }
    if ($root) $str .= '</xml>';
    return $str;
}
/**
 * xml转json
 * @param string $source 传的是文件，还是xml的string的判断
 * @return string
 */
function xml2json($source)
{
    if (is_file($source)) {
        $xml_array = @simplexml_load_file($source);
    } else {
        $xml_array = @simplexml_load_string($source, NULL, LIBXML_NOCDATA);
    }
    return json_encode($xml_array);
}

/**
 * 格式化内容图片地址
 * @param $content
 * @return mixed
 */
function formatContentImg($content)
{
    $pattern = '/<img(.*?)src=("|\')(.*?)\2/is';
    if (preg_match($pattern, $content)) {
        preg_match_all($pattern, $content, $matchs);
        foreach ($matchs[3] as $index => $value) {
            if (!(str_starts_with($value, "http://") ||
                str_starts_with($value, "https://") ||
                str_starts_with($value, "ftp://") ||
                str_starts_with(str_replace(' ', '', $value), "data:image/")
            )) {
                if (str_starts_with($value, "//")) {
                    $value = "http:" . $value;
                } elseif (str_starts_with($value, "/")) {
                    $value = substr($value, 1);
                }
                $newValue = "<img" . $matchs[1][$index] . "src=" . $matchs[2][$index] . __base_fillUrl($value) . $matchs[2][$index];
                $content = str_replace($matchs[0][$index], $newValue, $content);
            }
        }
    }
    return $content;
}
