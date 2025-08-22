<?php

/**
 * @title 验证 IP
 */

/**
 * @action 判断是否是有效的IP地址（IPv4 或 IPv6）
 * @param string $ip
 * @return bool
 */
function cc_is_ip_valid($ip = '')
{
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

/**
 * @action 判断是否是有效的IPv4地址
 * @param string $ip
 * @return bool
 */
function cc_is_ip_ipv4($ip = '')
{
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
}

/**
 * @action 判断是否是有效的IPv6地址
 * @param string $ip
 * @return bool
 */
function cc_is_ip_ipv6($ip = '')
{
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
}

/**
 * @action 判断是否是外网IP（非私有地址）
 * @param string $ip
 * @return bool
 */
function cc_is_ip_extranet($ip = '')
{
    // 判断IP格式是否合法
    if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }
    // 判断是否是内网IP
    return !cc_is_ip_internal($ip);
}

/**
 * @action 判断是否是内网IP（私有地址）
 * @param string $ip
 * @return bool
 */
function cc_is_ip_internal($ip = '')
{
    // 验证是否为有效的IPv4地址
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }
    // 将IP地址转为长整型
    $ipLong = ip2long($ip);
    if ($ipLong === false) {
        return false;
    }
    // 内网IP的范围
    $privateRanges = [
        ['start' => '10.0.0.0', 'end' => '10.255.255.255'], // A类网预留ip的网络地址
        ['start' => '172.16.0.0', 'end' => '172.31.255.255'], // B类网预留ip的网络地址
        ['start' => '192.168.0.0', 'end' => '192.168.255.255'], // C类网预留ip的网络地址
        ['start' => '169.254.0.0', 'end' => '169.254.255.255'],
        ['start' => '127.0.0.0', 'end' => '127.255.255.255'] // 127.x.x.x
    ];
    // 检查IP是否在任何一个内网范围内
    foreach ($privateRanges as $range) {
        if ($ipLong >= ip2long($range['start']) && $ipLong <= ip2long($range['end'])) {
            return true;
        }
    }
    return false;
}
/**
 * @action 判断字符串是否IP获取子掩码IP
 * CIDR（Classless Inter-Domain Routing）
 * @param string $cidr
 * @return bool
 */
function cc_is_ip_cidr($cidr = '')
{
    // 检查是否包含斜杠 (CIDR格式)
    if (str_contains($cidr, '/')) {
        list($cidr, $netmask) = explode('/', $cidr, 2);
        // 校验子网掩码范围是否正确
        if ($netmask < 0 || $netmask > 32 || trim($netmask) === '') {
            return false;
        }
    }
    // 验证IP部分是否是有效的IPv4地址
    return filter_var($cidr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
}
