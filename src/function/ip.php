<?php

use shiyun\support\Cache;
use Carbon\Carbon;

/**
 * 【ctocode】      核心文件
 * ============================================================================
 * @author       作者         ctocode-zhw
 * @version 	  版本	  v5.7.1.20210514
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

/**
 * 获取IP地址
 * @author zhw ip 
 * @version 2016-03-28
 * @param string $type 类别
 * @param string $param 参数
 * @return string
 */
function cc_ip_getAddr()
{
    $ip = '';
    if (!isset($ip)) {
        if (getenv('HTTP_CLIENT_IP') and strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) and $_SERVER['HTTP_CLIENT_IP'] and strcasecmp($_SERVER['HTTP_CLIENT_IP'], 'unknown')) {
            $onlineip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (getenv('HTTP_X_FORWARDED_FOR') and strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and $_SERVER['HTTP_X_FORWARDED_FOR'] and strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown')) {
            $onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (getenv('REMOTE_ADDR') and strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR'] and strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        } elseif (request()->header('X-Real-IP')) {
            $onlineip = request()->header('X-Real-IP');
        } else {
            $onlineip = '0,0,0,0';
        }
        preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $onlineip, $match);
        $ip = $match ? ($match[0] ?: 'unknown') : '';
    }
    return $ip;

    $ip = '';
    if (!empty($_SERVER["HTTP_CDN_SRC_IP"])) {
        $ip = $_SERVER["HTTP_CDN_SRC_IP"];
    } elseif (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if (!empty($_SERVER["REMOTE_ADDR"])) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } else {
        $ip = '';
    }
    /**
     * 获取真实有效IP
     */
    $realip = '';
    $ipArr = [];
    $ipArr = explode(',', $ip);
    foreach ($ipArr as $ipItem) {
        $ipItem = trim($ipItem);
        if ($ipItem != 'unknown' && cc_is_ip_extranet($ipItem)) {
            $realip = $ipItem;
            break;
        }
    }
    return $realip;
    /**
     * 获取一个IP
     */
    preg_match("/[\d\.]{7,15}/", $ip, $ips);
    $ip = isset($ips[0]) ? $ips[0] : 'unknown';
    unset($ips);
    return $ip;
}

/**
 * @action 获取城市
 * @author ctocode-zhw
 * @param string $type 类别
 * @param string $param 参数
 * @return mixed <string, unknown>
 * @version 2017-08-09
 */
function cc_ip_getCity($ip = '', $type = 'taobao', $param = '')
{
    if (empty($ip)) {
        $ip = cc_ip_getAddr();
    }
    if (cc_is_ip_valid($ip) !== TRUE || cc_is_ip_extranet($ip) !== TRUE) {
        return array(
            'ip' => $ip,
            'info' => '未知_本地'
        );
    }
    switch ($type) {
        case 'sina': // 获取新浪api
            $apiUrl = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=';
            $apiRe = @file_get_contents($apiUrl . $ip);
            if (!empty($apiRe)) {
                $jsonMatches = array();
                preg_match('#\{.+?\}#', $apiRe, $jsonMatches);
                if (isset($jsonMatches[0])) {
                    $apiReData = json_decode($jsonMatches[0], true);
                    if (isset($apiReData['ret']) && $apiReData['ret'] == 1) {
                        $apiReData['ip'] = $ip;
                        $apiReData['info'] = $apiReData['country'] . '_' . $apiReData['province'] . '_' . $apiReData['city'];
                        return $apiReData;
                    }
                }
            }
            break;
        case 'taobao': // 获取淘宝接口
            $apiUrl = 'http://ip.taobao.com/service/getIpInfo.php?ip=';
            $apiRe = @file_get_contents($apiUrl . $ip);
            $apiRe = json_decode($apiRe, true);
            $apiReData = array();
            if (!empty($apiRe['data'])) {
                $apiReData = $apiRe['data'];
                $apiReData['ip'] = $ip;
                $apiReData['info'] = $apiReData['country'] . '_' . $apiReData['region'] . '_' . $apiReData['city'] . '_' . $apiReData['isp'];
                return $apiReData;
            }
            break;
    }
    return array(
        'ip' => $ip,
        'info' => '未知_本地'
    );
}
/**
 * 取ip前3段
 * @param $ip
 * @return mixed|string
 */
function __cc_ip_getIp3Pre($ip = '')
{
    preg_match("/(\d{1,3}\.\d{1,3}\.\d{1,3})\.\d{1,3}/", $ip, $match);
    if ($match) {
        return $match[1];
    } else {
        return "";
    }
}

/**
 * 是否是中国IP：-1错误、1是、0否
 * @param string $ip
 * @return int
 */
function cc_is_ip_china($ip = '')
{
    if (empty($ip)) {
        $ip = cc_ip_getAddr();
    }
    $cacheKey = "isCnIp::" . md5($ip);
    //
    $result = Cache::remember($cacheKey, \Carbon\Carbon::now()->addMinutes(10), function () use ($ip) {
        $file = dirname(__FILE__) . '/IpAddr/all_cn.txt';
        if (!file_exists($file)) {
            return -1;
        }
        $in = false;
        $myFile = fopen($file, "r");
        $i = 0;
        while (!feof($myFile)) {
            $i++;
            $cidr = trim(fgets($myFile));
            if (__cc_ipInRange($ip, $cidr)) {
                $in = true;
                break;
            }
        }
        fclose($myFile);
        return $in ? 1 : 0;
    });
    if ($result === -1) {
        Cache::forget($cacheKey);
    }
    //
    return intval($result);
}

/**
 * 验证IP地址范围
 * $range 支持多种写法
 *  - Wildcard： 1.2.3.*
 *  - CIRD：1.2.3/24 或者 1.2.3.4/255.255.255.0
 *  - Start-End: 1.2.3.0-1.2.3.255
 * @param $ip
 * @param $range
 * @return bool
 */
function __cc_ipInRange($ip = '', $range = '')
{
    if (substr_count($ip, '.') == 3 && $ip == $range) {
        return true;
    }
    if (str_contains($range, '/')) {
        list($range, $netmask) = explode('/', $range, 2);
        if (str_contains($netmask, '.')) {
            $netmask = str_replace('*', '0', $netmask);
            $netmask_dec = ip2long($netmask);
            return ((ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec));
        } else {
            $x = explode('.', $range);
            while (count($x) < 4) {
                $x[] = '0';
            }
            list($a, $b, $c, $d) = $x;
            $range = sprintf("%u.%u.%u.%u", empty($a) ? '0' : $a, empty($b) ? '0' : $b, empty($c) ? '0' : $c, empty($d) ? '0' : $d);
            $range_dec = ip2long($range);
            $ip_dec = ip2long($ip);
            $wildcard_dec = pow(2, (32 - $netmask)) - 1;
            $netmask_dec = ~$wildcard_dec;
            return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
        }
    } else {
        if (str_contains($range, '*')) {
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = "$lower-$upper";
        }
        if (str_contains($range, '-')) {
            list($lower, $upper) = explode('-', $range, 2);
            $lower_dec = (float)sprintf("%u", ip2long($lower));
            $upper_dec = (float)sprintf("%u", ip2long($upper));
            $ip_dec = (float)sprintf("%u", ip2long($ip));
            return (($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec));
        }
        return false;
    }
}
/** 
 * 使用PHP检测能否ping通IP或域名 
 * @param string $address 
 * @return boolean 
 */
// ping域名
// var_dump ( pingAddress ( 'baidu.com' ) );
// ping IP
// var_dump ( pingAddress ( '45.33.36.121' ) );
function ctoIpPingAddress($address)
{
    $status = -1;
    if (strcasecmp(PHP_OS, 'WINNT') === 0 || PATH_SEPARATOR == ';') { // Windows 服务器下
        $pingresult = exec("ping -n 1 {$address}", $outcome, $status);

        // // windows IP地址 $ip = '127.0.0.1';
        // exec ( "ping $ip -n 4", $info );
        // if(count ( $info ) < 10)
        // {
        // return '服务器无法连接';
        // }
        // // 获取ping的时间
        // $str = $info[count ( $info ) - 1];
        // return substr ( $str, strripos ( $str, '=' ) + 1 );
    } elseif (strcasecmp(PHP_OS, 'Linux') === 0 || PATH_SEPARATOR == ':') { // Linux 服务器下
        $pingresult = exec("ping -c 1 {$address}", $outcome, $status);

        // // linux IP地址 $ip = '127.0.0.1';
        // exec ( "ping $ip -c 4", $info );
        // if(count ( $info ) < 9)
        // {
        // return '服务器无法连接';
        // }
        // // 获取ping的时间
        // $str = $info[count ( $info ) - 1];
        // return round ( substr ( $str, strpos ( $str, '/', strpos ( $str, '=' ) ) + 1, 4 ) );
    }
    if (0 == $status) {
        $status = true;
    } else {
        $status = false;
    }
    return $status;
}
