<?php

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
 * 获取IP
 * @author zhw ip 
 * @version 2016-03-28
 * @param string $type 类别
 * @param string $param 参数
 * @return boolean <string, unknown>
 */
function ctoIpGet()
{
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
    preg_match("/[\d\.]{7,15}/", $ip, $ips);
    $ip = isset($ips[0]) ? $ips[0] : 'unknown';
    unset($ips);
    return $ip;

    // =============
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif ($_SERVER["HTTP_CLIENT_IP"]) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif ($_SERVER["REMOTE_ADDR"]) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } elseif (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        $ip = "unknown";
    }
    return $ip;

    // =============

    preg_match("/[\d\.]{7,15}/", $ip, $ips);
    $ip = $ips[0] ? $ips[0] : 'unknown';
    return $ip;

    // =============

    $realip = '';
    $unknown = 'unknown';
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($arr as $ip) {
                $ip = trim($ip);
                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } else if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            $realip = $_SERVER['REMOTE_ADDR'];
        } else {
            $realip = $unknown;
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)) {
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)) {
            $realip = getenv("REMOTE_ADDR");
        } else {
            $realip = $unknown;
        }
    }
    $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
    return $realip;

    // =============
}
// 判断IP 是否合法
function ctoIpCheck($ip)
{
    $reg = '/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/';
    // $reg2 = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/';
    // $reg3 = '/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))$/'
    if (!preg_match($reg, $ip)) {
    }
    $arr = explode('.', $ip);
    if (count($arr) != 4) {
        return false;
    } else {
        for ($i = 0; $i < 4; $i++) {
            if (($arr[$i] < '0') || ($arr[$i] > '255')) {
                return false;
            }
        }
    }
    return true;
}
function ctoIpCheckValid($ip)
{
    if (empty($ip)) {
        return false;
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) { // it's valid
        return true;
    }
    return false;
}
/**
 * @action 获取城市
 * @author ctocode-zhw
 * @param string $type 类别
 * @param string $param 参数
 * @return mixed <string, unknown>
 * @version 2017-08-09
 */
function ctoIpCity($ip = '', $type = 'taobao', $param = '')
{
    if (empty($ip)) {
        $ip = ctoIpGet();
    }
    if (ctoIpCheck($ip) !== TRUE || ctoIpCheckValid($ip) !== TRUE) {
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
