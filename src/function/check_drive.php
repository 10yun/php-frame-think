<?php

/**
 * @title 验证设备
 */

/**
 * @action 是否微信
 * @return bool
 */
function cc_is_drive_wechat()
{
    return str_contains(request()->server('HTTP_USER_AGENT'), 'MicroMessenger');
}
/**
 * @action 判断是否微信或者支付宝
 * @return bool|string
 */
function cc_is_weixnOrAlipay()
{
    // 判断是不是微信
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return "WeiXIN";
    }
    // 判断是不是支付宝
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false) {
        return "Alipay:true";
    }
    // 哪个都不是
    return false;
}
/**
 * @action 判断 浏览器访问的内核,是否是手机访问
 * @author ctocode-zhw
 * @version 2017-12-20
 * @link https://www.10yun.com
 * @return boolean
 */
function cc_check_browser()
{
    // 下列几个数组，用^^^分割，减少代码行数

    /* os */
    $mobile_os_str = '';
    $mobile_os_str .= 'Google Wireless Transcoder^^^Windows CE^^^WindowsCE^^^Symbian^^^Android';
    $mobile_os_str .= '^^^armv6l^^^armv5^^^Mobile^^^CentOS^^^mowser^^^AvantGo^^^Opera Mobi';
    $mobile_os_str .= '^^^J2ME/MIDP^^^Smartphone^^^Go.Web^^^Palm^^^iPAQ';
    $mobile_os_arr = explode("^^^", $mobile_os_str);
    /* token */
    $mobile_token_str = '';
    $mobile_token_str .= 'Profile/MIDP^^^Configuration/CLDC-';
    $mobile_token_str .= '^^^160×160^^^176×220^^^240×240^^^240×320^^^320×240';
    $mobile_token_str .= '^^^UP.Browser^^^UP.Link^^^SymbianOS^^^PalmOS^^^PocketPC^^^SonyEricsson^^^Nokia';
    $mobile_token_str .= '^^^BlackBerry^^^Vodafone^^^BenQ^^^Novarra-Vision^^^Iris';
    $mobile_token_str .= '^^^NetFront^^^HTC_^^^Xda_^^^SAMSUNG-SGH^^^Wapaka^^^DoCoMo^^^iPhone^^^iPod';
    $mobile_token_arr = explode("^^^", $mobile_token_str);
    /* agents */
    $mobile_agents_str = '';
    $mobile_agents_str .= '240x320';
    $mobile_agents_str .= '^^^acer^^^acoon^^^acs-^^^abacho^^^airness^^^alcatel^^^amoi^^^android^^^anywhereyougo.com';
    $mobile_agents_str .= '^^^applewebkit/525^^^applewebkit/532^^^asus^^^audio^^^au-mic^^^avantogo';
    $mobile_agents_str .= '^^^becker^^^benq^^^bilbo^^^bird^^^blackberry^^^blazer^^^bleu';
    $mobile_agents_str .= '^^^cdm-^^^compal^^^coolpad^^^danger^^^dbtel^^^dopod';
    $mobile_agents_str .= '^^^elaine^^^eric^^^etouch^^^fly ^^^fly_^^^fly-';
    $mobile_agents_str .= '^^^go.web^^^goodaccess^^^gradiente^^^grundig^^^haier^^^hedy^^^hitachi^^^htc';
    $mobile_agents_str .= '^^^huawei^^^hutchison^^^inno^^^ipad^^^ipaq^^^ipod^^^jbrowser^^^kddi^^^kgt^^^kwc';
    $mobile_agents_str .= '^^^lenovo^^^lg ^^^lg2^^^lg3^^^lg4^^^lg5^^^lg7^^^lg8^^^lg9^^^lg-^^^lge-^^^lge9^^^longcos';
    $mobile_agents_str .= '^^^maemo^^^mercator^^^meridian^^^micromax^^^midp^^^mini^^^mitsu^^^mmm^^^mmp^^^mobi^^^mot-^^^moto';
    $mobile_agents_str .= '^^^nec-^^^netfront^^^newgen^^^nexian^^^nf-browser^^^nintendo^^^nitro^^^nokia^^^nook^^^novarra';
    $mobile_agents_str .= '^^^obigo^^^palm^^^panasonic^^^pantech^^^philips^^^phone^^^pg-^^^playstation^^^pocket^^^pt-';
    $mobile_agents_str .= '^^^qc-^^^qtek^^^rover^^^sagem^^^sama^^^samu^^^sanyo^^^samsung^^^sch-^^^scooter^^^sec-^^^sendo';
    $mobile_agents_str .= '^^^sgh-^^^sharp^^^siemens^^^sie-^^^softbank^^^sony^^^spice^^^sprint^^^spv^^^symbian';
    $mobile_agents_str .= '^^^tablet^^^talkabout^^^tcl-^^^teleca^^^telit^^^tianyu^^^tim-^^^toshiba^^^tsm';
    $mobile_agents_str .= '^^^up.browser^^^utec^^^utstar^^^verykool^^^virgin^^^vk-^^^voda^^^voxtel^^^vx';
    $mobile_agents_str .= '^^^wap^^^wellco^^^wig browser^^^wii^^^windows ce^^^wireless^^^xda^^^xde^^^zte';
    $mobile_agents_arr = explode("^^^", $mobile_agents_str);

    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    $browser_type = 'pc';
    $is_mobile = false;
    foreach ($mobile_agents_arr as $device) {
        if (false !== stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    /* 获取浏览器内核 */
    // $browser_kernel = preg_match ( '|\(.*?\)|', $user_agent, $matches_kernel ) > 0 ? $matches[0] : '';
    /* 获取版本号-内核 */
    // $browser_version = preg_match ( '/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches_version );
    if ($is_mobile == true) {
        if (false !== strpos($user_agent, "MicroMessenger")) { /* 微信浏览器 */
            $browser_type = 'weixin';
        } else {
            $browser_type = 'mobile';
        }
    }
    return $browser_type;

    // 方法2
    $user_agent_commentsblock = preg_match('|\(.*?\)|', $user_agent, $matches) > 0 ? $matches[0] : '';

    $found_mobile = _cc_check_substrs($mobile_os_arr, $user_agent_commentsblock)
        || _cc_check_substrs($mobile_token_arr, $user_agent);
    if ($found_mobile) {
        return true;
    } else {
        return false;
    }
}
function _cc_check_substrs($substrs, $text): bool
{
    foreach ($substrs as $substr) {
        if (false !== strpos($text, $substr)) {
            return true;
        }
    }
    return false;
}

/**
 * @action 获取设备OS类型
 */
function cc_drive_os()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strpos($agent, 'windows nt')) {
        $platform = 'windows';
    } elseif (strpos($agent, 'macintosh')) {
        $platform = 'mac';
    } elseif (strpos($agent, 'ipod')) {
        $platform = 'ipod';
    } elseif (strpos($agent, 'ipad')) {
        $platform = 'ipad';
    } elseif (strpos($agent, 'iphone')) {
        $platform = 'iphone';
    } elseif (strpos($agent, 'android')) {
        $platform = 'android';
    } elseif (strpos($agent, 'unix')) {
        $platform = 'unix';
    } elseif (strpos($agent, 'linux')) {
        $platform = 'linux';
    } else {
        $platform = 'other';
    }
    return $platform;
}
/**
 * @action 获取浏览器类型
 * @return string
 */
function cc_drive_browser()
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
function cc_drive_platform()
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
