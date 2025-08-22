<?php

// 密码只能为数字和字母的组合
function cc_check_str_num_letter($str = null)
{
    $regex = "/^(w){4,20}$/";
    return preg_match($regex, $str) ? true : false;
}
// 验证是否以字母开头
function cc_str_strat_letter($str = null)
{
    $regex = "/^[a-za-z]{1}([a-za-z0-9]|[._]){3,19}$/";
    return preg_match($regex, $str) ? true : false;
}
/**
 * @action 是否包含中文
 * @param string $str 内容
 * @return bool  是否包含
 */
function cc_is_chStr($str = '')
{
    $pattern = '/[^\x00-\x80]/';
    $pattern = '/[\x7f-\xff]/';
    if (preg_match($pattern, $str)) {
        return true;
    } else {
        return false;
    }
}
