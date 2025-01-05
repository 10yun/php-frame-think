<?php

/**
 * 补零
 * @param $str
 * @param int $length 长度
 * @param bool $before 是否补在前面
 * @return string
 */
function cc_number_zero_Fill($str, $length = 0, $before = true)
{
    if (strlen($str) >= $length) {
        return $str;
    }
    $_str = '';
    for ($i = 0; $i < $length; $i++) {
        $_str .= '0';
    }
    if ($before) {
        $_ret = substr($_str . $str, $length * -1);
    } else {
        $_ret = substr($str . $_str, 0, $length);
    }
    return $_ret;
}
