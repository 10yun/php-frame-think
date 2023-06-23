<?php

/**
 * 生成指定长度的随机字符串
 * @param int $len 长度
 * @param string $type
 * @return string
 */
function _cc_random_string($len = 8, $type = 'str')
{
    switch ($type) {
        case 'str': {
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
            }
        case 'int': {
                $chars = '0123456789';
                break;
            }
        default: {
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            }
    }
    $chars = str_shuffle($chars);
    return substr($chars, 0, $len);
}
