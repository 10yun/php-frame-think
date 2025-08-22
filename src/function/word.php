<?php

/**
 * 阿拉伯数字转化为中文
 * @param $num
 * @return string
 */
function __cc_chinaNum($num)
{
    $china = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
    $arr = str_split($num);
    $txt = '';
    for ($i = 0; $i < count($arr); $i++) {
        $txt .= $china[$arr[$i]];
    }
    return $txt;
}

/**
 * 阿拉伯数字转化为中文（用于星期，七改成日）
 * @param $num
 * @return string
 */
function __cc_chinaNumZ($num)
{
    return str_replace("七", "日", __cc_chinaNum($num));
}

/**
 * 用户名、邮箱、手机帐号、银行卡号中间字符串以*隐藏
 * @param $str
 * @return string
 */
function __cc_cardFormat($str)
{
    if (strpos($str, '@')) {
        $email_array = explode("@", $str);
        $prevfix = substr($str, 0, strlen($email_array[0]) < 4 ? 1 : 3); //邮箱前缀
        $count = 0;
        $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
        return $prevfix . $str;
    }
    if (cc_is_mobile($str)) {
        return substr($str, 0, 3) . "****" . substr($str, -4);
    }
    $pattern = '/([\d]{4})([\d]{4})([\d]{4})([\d]{4})([\d]*)?/i';
    if (preg_match($pattern, $str)) {
        return preg_replace($pattern, '$1 **** **** **** $5', $str);
    }
    $pattern = '/([\d]{4})([\d]{4})([\d]{4})([\d]*)?/i';
    if (preg_match($pattern, $str)) {
        return preg_replace($pattern, '$1 **** **** $4', $str);
    }
    $pattern = '/([\d]{4})([\d]{4})([\d]*)?/i';
    if (preg_match($pattern, $str)) {
        return preg_replace($pattern, '$1 **** $3', $str);
    }
    return substr($str, 0, 3) . "***" . substr($str, -1);
}

/**
 * 字节转格式
 * @param $bytes
 * @return string
 */
function __cc_readableBytes($bytes)
{
    $i = floor(log($bytes) / log(1024));
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
}
/**
 * 去除emoji表情
 * @param $str
 * @return string|string[]|null
 */
function __cc_filterEmoji($str)
{
    return preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str
    );
}

function cc_random_china()
{
    $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书";
}
