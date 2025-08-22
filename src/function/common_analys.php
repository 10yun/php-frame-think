<?php

/**
 * 
 * ======// 内容转换处理
 * 
 */


/**
 * 轮播图
 */
function analysImgsParse($imgsJson = '',)
{
    $imgsArr = cc_json_decode($imgsJson);
    $newArr = [];
    if (!empty($imgsArr)) {
        foreach ($imgsArr as $key2 => $val2) {
            $items = array();
            if (empty($val2['url'])) {
                continue;
            }
            $items['url'] = analysImgDoTrnas($val2['url']);
            $items['msg'] = $val2['msg'];
            $newArr[] = $items;
        }
    }
    return $newArr;
}
function analysTags(string $tags = '')
{
    return  array_unique(array_filter(array_map('trim', explode(',', $tags))));
}
/**
 * 图片路径转换解析
 * @param string $img 图片路径
 * @param string $default 如果没有，需要填充的自定义默认图
 */
function analysImgDoTrnas($img = '', $default = '')
{
    // $items['link'] = analysImgDoTrnas($val2['url']);
    // $items['link'] = str_replace ( "file///", "file/", $items['link'] );
    // $items['link'] = str_replace ( "file//", "file/", $items['link'] );
    $curr_file_url = syOpenAppsConfig('SDKS_FILE_CDNURL');
    // $curr_file_signatrue = syOpenAppsConfig('GB_OSS_FILE_SIGNATRUE');
    $curr_file_signatrue = '';
    // $is_url = preg_match ( "/^http(s)?:\\/\\/.+/", $val['per_avatar'] );
    if (!empty($img)) {
        if (preg_match('/(http:\/\/)|(https:\/\/)/i', $img)) {
            return $img; // 直接粘贴地址
        } else {
            return $curr_file_url . $img . $curr_file_signatrue;
        }
    } else {
        if (empty($default)) {
            return 'https://10ui.cn/default/default.png';
        } else {
            if (preg_match('/(http:\/\/)|(https:\/\/)/i', $default)) {
                return $default; // 直接粘贴地址
            } else {
                return 'https://10ui.cn/' . $default;
            }
        }
    }
}
/**
 * 状态码 转 字符串说明
 * @param string $arr
 * @param string $value
 * @param string $default
 * @return string
 */
function analysArrToStr($arr = '', $value = '', $default = '')
{
    if (empty($arr) || !is_array($arr)) {
        return $default;
    }
    if (!empty($value)) {
        if (is_numeric($value)) {
        }
        // reset ( $arr );
    }
    $value = !empty($value) ? $value : 0;
    $arr_key = array_keys($arr);
    if (in_array($value, $arr_key)) {
        return !empty($arr[$value]) ? $arr[$value] : '';
    }
    return $default;
}

/**
 * 时间戳转日期
 * @param string $arr
 * @param string $value
 */
function analysTimeToDate($value = 0, $format = '')
{
    $format = !empty($format) ? $format : 'Y-m-d H:i';
    return $value > 0 ? date($format, $value) : '';
}
function analysJsonToArray($str)
{
    if (is_string($str))
        $str = json_decode($str, true);

    if (is_string($str))
        $str = analysJsonToArray($str);

    $arr = array();
    foreach ($str as $k => $v) {
        if (is_object($v) || is_array($v))
            $arr[$k] = analysJsonToArray($v);
        else
            $arr[$k] = $v;
    }
    return $arr;
}
// json 转 array
function cc_json_decode($str = '', $flag = true)
{
    $arr = '';
    try {
        $arr = analysJsonToArray($str, $flag);
    } catch (\Exception $exception) {
        // 用异常 处理 旧数据
        $arr = analysMbUnserialize($str);
    }
    return $arr;
}

/**
 *  表情数据时的处理
 * @param string $msg 需要解析的数据
 * @param $type 类型，1将数据编码encode
 * @return null|string|string[]
 */
function analysEmojiEn($msg)
{
    $msg = preg_replace_callback('/[\xf0-\xf7].{3}/', function ($r) {
        return '@E' . base64_encode($r[0]);
    }, $msg);
    return $msg;
}
/**
 *  表情数据时的处理
 * @param string $msg 需要解析的数据
 * @param $type 类型，2将数据解码 decode
 * @return null|string|string[]
 */
function analysEmojiDe($msg)
{
    $msg = preg_replace_callback('/@E(.{6}==)/', function ($r) {
        return base64_decode($r[1]);
    }, $msg);
    return $msg;
}

function analysMbUnserialize($str)
{
    if (empty($str)) {
        return '';
    }
    try {
        $str2 = unserialize($str);
    } catch (\Exception $exception) {
        $str2 = preg_replace_callback('#s:(\d+):"(.*?)";#s', function ($match) {
            return 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
        }, $str);
    }
    return $str2;

    $data = trim($str);
    if ('N;' == $str)
        return true;
    if (!preg_match('/^([adObis]):/', $str, $badions))
        return false;
    switch ($badions[1]) {
        case 'a':
        case 'O':
        case 's':
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $str))
                return true;
            break;
        case 'b':
        case 'i':
        case 'd':
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $str))
                return true;
            break;
    }
    return false;
}

/**
 * 计算两点地理坐标之间的距离
 * @param float $longitude1 起点经度
 * @param float $latitude1 起点纬度
 * @param float $longitude2 终点经度 
 * @param float $latitude2 终点纬度
 * @param float   $unit    单位 1:米 2:公里
 * @param float   $decimal  精度 保留小数位数
 * @return float
 */
function analysDistance(float $longitude1 = 0, float $latitude1 = 0, float $longitude2 = 0, float $latitude2 = 0, float $unit = 2, float $decimal = 2)
{
    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI / 180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if ($unit == 2) {
        // 距离米转为公里
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);
}
/**
 * @action 金额 小数 转换 int
 * @version 2016-08-08 
 */
function ctoValueMoneyEn($money = null)
{
    if (empty($money)) {
        return 0;
    }
    if (is_numeric($money)) {
        $int_money = ($money * 100);
        // $int_money = intval ( $int_money );
        $int_money = (float) ($int_money);
    } else {
        $int_money = 0;
    }
    return $int_money;
}
/**
 * @action int 转换 金额小数
 * @author zhw
 * @version 2016-08-08
 */
function ctoValueMoneyDe($money = null)
{
    if (empty($money)) {
        return '0.00';
    }
    if (is_numeric($money)) {
        $int_money = $money / 100;
        $int_money = sprintf("%.2f", $int_money);
    } else {
        $int_money = 0;
    }
    return $int_money;
}
/**
 * 转换金额,防止金额为空的时候
 * @param string $arr
 * @param string $value
 * @param string $default
 * @return string
 */
function analysMoneyDe($data = array(), $key = '')
{
    $money = 0;
    if (!empty($data[$key])) {
        $money = $data[$key];
    }
    return ctoValueMoneyDe($money);
}
