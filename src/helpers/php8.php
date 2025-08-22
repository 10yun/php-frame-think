<?php

/**
 * 兼容 php8.0 - php8.4
 */
if (!function_exists('json_validate')) {
    /**
     * 判断是否是json字符串
     * 校验json字符串
     * @param string $str 
     * @return bool
     */
    function json_validate($str = '')
    {
        if (empty($str)) {
            return false;
        }
        try {
            //校验json格式
            json_decode($str, true);
            return JSON_ERROR_NONE === json_last_error();
        } catch (\Exception $e) {
            return false;
        }
    }
}
