<?php

/**
 * 获取字符串的长度
 *
 * @param  string $value
 * @return int
 */
function cc_str_length(string $value): int
{
    return mb_strlen($value);
}
/**
 * 字符串转大写
 *
 * @param  string $value
 * @return string
 */
function cc_str_upper(string $value): string
{
    return mb_strtoupper($value, 'UTF-8');
}
/**
 * 字符串转小写
 *
 * @param  string $value
 * @return string
 */
function cc_str_lower(string $value): string
{
    return mb_strtolower($value, 'UTF-8');
}
/**
 * 下划线转驼峰(首字母大写)
 *
 * @param  string $value
 * @return string
 */
function cc_str_xhx_ucwords(string $value): string
{
    $value = ucwords(str_replace(['-', '_'], ' ', $value));
    return  str_replace(' ', '', $value);
}
/**
 * 下划线转驼峰(首字母小写)
 *
 * @param  string $value
 * @return string
 */
function cc_str_xhx_camel(string $value): string
{
    return lcfirst(cc_str_xhx_ucwords($value));
}
/**
 * 驼峰转下划线
 *
 * @param  string $value
 * @param  string $delimiter
 * @return string
 */
function cc_str_tf_snake(string $value, string $delimiter = '_'): string
{
    $key = $value;
    if (!ctype_lower($value)) {
        $value = preg_replace('/\s+/u', '', ucwords($value));

        $value = cc_str_lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
    }
    return $value;
}
/**
 * 截取字符串
 *
 * @param  string   $string
 * @param  int      $start
 * @param  int|null $length
 * @return string
 */
function cc_str_substr(string $string, int $start, int|null $length = null): string
{
    return mb_substr($string, $start, $length, 'UTF-8');
}
/**
 * 检查字符串是否以某些字符串开头
 *
 * @param  string       $haystack 
 * @param  string|array $needles
 * @return bool
 */
function cc_str_startsWith(string $haystack, $needles): bool
{
    foreach ((array) $needles as $needle) {
        if ('' != $needle && mb_strpos($haystack, $needle) === 0) {
            return true;
        }
    }
    return false;
}
