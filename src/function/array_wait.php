<?php



/**
 * @action 判断多维数组是否存在某个值
 * @author ctocode-zhw
 * @version 2017-07-20
 * @param array $value 需要的值
 * @param array $array 多维数组
 */
function cc_array_inExistStr($value = null, $array = array(), $echo = null)
{
    foreach ($array as $item) {
        if (!is_array($item)) {
            if ($item == $value) {
                return true;
            } else {
                continue;
            }
        }
        if (in_array($value, $item)) {
            return true;
        } else if (cc_array_inExistStr($value, $item)) {
            return true;
        }
    }
    return false;
}
