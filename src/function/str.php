<?php

/**
 * 截取字符串
 */
function _cc_str_substr(string $string, int $start, int $length = null)
{
    return \think\helper\Str::substr($string, $start, $length);
}
/**
 * 下划线转驼峰(首字母大写)
 *
 * @param  string $value
 * @return string
 */
function _cc_str_studly(string $value)
{
    return \think\helper\Str::studly($value);
}
