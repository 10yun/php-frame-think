<?php

/**
 * 是否微信
 * @return bool
 */
function __cc_drive_isWechat()
{
    return str_contains(request()->server('HTTP_USER_AGENT'), 'MicroMessenger');
}


/**
 * 获取浏览器类型
 * @return string
 */
function __cc_drive_browser()
{
    $user_agent = request()->server('HTTP_USER_AGENT');
    if (str_contains($user_agent, 'AlipayClient')) {
        return 'alipay';
    } elseif (str_contains($user_agent, 'MicroMessenger')) {
        return 'weixin';
    } else {
        return 'none';
    }
}

/**
 * 获取平台类型
 * @return string
 */
function __cc_drive_platform()
{
    $platform = strtolower(trim(request()->header('sy-client-platform')));
    if (in_array($platform, ['android', 'ios', 'win', 'mac', 'web', 'pc-web'])) {
        return $platform;
    }
    $agent = strtolower(request()->server('HTTP_USER_AGENT'));
    if (str_contains($agent, 'android')) {
        $platform = 'android';
    } elseif (str_contains($agent, 'iphone') || str_contains($agent, 'ipad')) {
        $platform = 'ios';
    } else {
        $platform = 'unknown';
    }
    return $platform;
}
