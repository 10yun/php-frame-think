<?php

/**
 * 检查是否为内网ip
 * @param $ip
 * @return int
 */
function _cc_check_intranet($ip)
{
    $addrArr = array(
        '10.0.0.0|10.255.255.255',
        '172.16.0.0|172.31.255.255',
        '192.168.0.0|192.168.255.255',
        '169.254.0.0|169.254.255.255',
        '127.0.0.0|127.255.255.255'
    );
    $longIp = ip2long($ip);
    if ($longIp != -1) {
        foreach ($addrArr as $addr) {
            list($start, $end) = explode('|', $addr);
            if ($longIp >= ip2long($start) && $longIp <= ip2long($end))
                return true;
        }
    }
    return false;
}

/**
 * 判断是否是日期格式
 */
function _cc_check_date_format($date)
{
    // 匹配日期格式
    if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
        // 检测是否为日期
        if (checkdate($parts[2], $parts[3], $parts[1])) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 检测是否是时间戳
 */
function _cc_check_timestamp($timestamp)
{
    if (!is_int($timestamp)) {
        return false;
    }
    if (strtotime(date('Y-m-d H:i:s', $timestamp)) === $timestamp) {
        return true;
    } else {
        return false;
    }
}
