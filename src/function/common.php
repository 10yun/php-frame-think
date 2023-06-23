<?php

/**
 * 【ctocode】      常用函数 - common相关处理
 * ============================================================================
 * @author       作者         ctocode-zhw
 * @version 	  版本	  v5.7.1.20210514
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */
function dbg_get_evnt($u)
{
    switch ($u) {
        case 'self':
            return isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['ORIG_PATH_INFO']);
            break;
        case 'referer':
            return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            break;
        case 'domain':
            return $_SERVER['SERVER_NAME'];
            break;
        case 'scheme':
            return isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
            break;
        case 'port':
            return isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT'];
            break;
        case 'url':
            if (!empty($_SERVER['REQUEST_URI'])) {
                $url = $_SERVER['REQUEST_URI'];
            } else {
                $url = !empty($_SERVER['argv']) ? $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0] : $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            }
            if (isset($_SERVER['HTTP_HOST'])) {
                return get_evnt('scheme') . $_SERVER['HTTP_HOST'] . (strpos($_SERVER['HTTP_HOST'], ':') === false ? get_evnt('port') : '') . $url;
            }
            return '';
            break;
        case 'browser':
            if (!empty($_SERVER["HTTP_USER_AGENT"])) {
                $ua = $_SERVER["HTTP_USER_AGENT"];
            }
            return $ua;
            break;
        default:
            return '';
            break;
    }
}

function filterStr($str)
{
    global $_glb;
    if ($_glb['web_lang'] == 'utf-8') {
        $str = preg_replace("/[\"\r\n\t\$\\><']/", '', $str);
        if ($str != stripslashes($str)) {
            return '';
        } else {
            return $str;
        }
    } else {
        $rs = '';
        for ($i = 0; isset($str[$i]); $i++) {
            if (ord($str[$i]) > 0x80) {
                if (isset($str[$i + 1]) && ord($str[$i + 1]) > 0x40) {
                    $rs .= $str[$i] . $str[$i + 1];
                    $i++;
                } else {
                    $rs .= ' ';
                }
            } else {
                if (preg_replace("/[^0-9a-z@#\.]/i", $str[$i])) {
                    $rs .= ' ';
                } else {
                    $rs .= $str[$i];
                }
            }
        }
    }
    return $rs;
}

/**
 *  这个函数的功能是将数值转换成json数据存储格式
 *  使用特定function对数组中所有元素做处理
 *  @param  string  &$array     要处理的字符串
 *  @param  string  $function   要执行的函数
 *  @return boolean $apply_to_keys_also     是否也应用到key上
 *  @access public
 *
 */
function ctoArrayRecursive(&$array = [], $function, $apply_to_keys_also = false)
{
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            ctoArrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter--;
}

/**************************************************************
 *
 *  将数组转换为JSON字符串（兼容中文）
 *  @param  array   $array      要转换的数组
 *  @return string      转换得到的json字符串
 *  @access public
 *
 *************************************************************/
function ctoArrayarrToJson($array)
{
    ctoArrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
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
