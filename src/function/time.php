<?php

/**
 * 时间
 */
/**
 * 【ctocode】      常用函数 - time相关处理
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
 * 判断是否日期字符串
 */
function cc_is_DateFormat($date, $format = 'Y-m-d'): bool
{
    $d = \DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
/**
 * 判断是否日期
 */
function cc_is_DateTime(string $dateTime = ''): bool
{
    if (empty($dateTime)) {
        return false;
    }
    $ret = strtotime($dateTime);
    return $ret !== false && $ret != -1;
}
/**
 * 1、Unix时间戳转日期
 * 时间转日期
 */
function _cc_time_to_date($format = "Y-m-d H:i:s", $unixtime = 0, $timezone = 'PRC')
{
    // DateTime类的bug，加入@可以将Unix时间戳作为参数传入
    $datetime = new \DateTime("@$unixtime");
    $datetime->setTimezone(new \DateTimeZone($timezone));
    return $datetime->format($format);
}


/**
 * @author zhw   @action 计算时间差
 * @param string $start_time 传递进来的开始时间
 * @return string 返回差值
 */
function _cc_time_diff_str($start_time = null, $end_time = null, $format = 'Y-m-d')
{
    date_default_timezone_set('PRC');
    /* 根据时间戳,秒数来判断 */
    $currTime = time();
    $endTime = !empty($end_time) ? $end_time : $currTime;

    $deviation = $endTime - $start_time;
    if ($deviation < 0 || $deviation > 3600) {
        if (date('Y', $endTime) != date('Y', $start_time)) {
            return date('Y-m-d', $start_time);
        }
        return date('m-d H:i', $start_time);
    }
    if ($deviation < 30) {
        return ' 刚刚';
    } elseif ($deviation < 60) {
        return $deviation . ' 秒钟前';
    } else {
        return floor($deviation / 60) . ' 分钟前';
    }
    // 方法1
    // $startdate = $start_time;
    $startdate = date('Y-m-d H:i:s', $start_time);
    $enddate = date('Y-m-d H:i:s', time());
    // 差距-天
    $time_date = floor((strtotime($enddate) - strtotime($startdate)) / 86400);
    if ($time_date <= 0) {
        // 差距-小时
        $time_hour = floor((strtotime($enddate) - strtotime($startdate)) % 86400 / 3600);
        // 差距-分钟
        $time_minute = floor((strtotime($enddate) - strtotime($startdate)) % 86400 / 60);
        // 差距-秒
        $time_second = floor((strtotime($enddate) - strtotime($startdate)) % 86400 % 60);

        if ($time_hour <= 0) {
            if ($time_minute < 1) {
                if ($time_second < 30) {
                    return "刚刚";
                } else if ($time_second < 60) {
                    return $time_second . " 秒前";
                }
            } elseif ($time_minute <= 60) {
                return $time_minute . " 分钟前";
            }
        } else if ($time_hour > 0 && $time_hour < 1) {
            return $time_hour . " 小时前";
        } else {
            return date("m-d H:i", strtotime($startdate)) . "";
        }
    } else if ($time_date > 0 && $time_date < 365) {
        /**
         * 1年内
         */
        return date("m-d H:i", strtotime($startdate)) . "";
    } else {
        return date("Y-m-d", strtotime($startdate)) . "";
    }
    // echo $time_date . "天<br>";
    // echo $time_hour . "时<br>";
    // echo $time_minute . "分钟<br>";
    // echo $time_second . "秒<br>";
}
function _cc_time_differ_full($begin_time, $end_time)
{
    $currTime = time();
    // 赋值
    $begin_time = !empty($begin_time) ? $begin_time : $currTime;
    $end_time = !empty($end_time) ? $end_time : $currTime;
    // 判断大小，对换位置
    $startTime = $begin_time > $end_time ? $end_time : $begin_time;
    $endTime = $begin_time > $end_time ? $begin_time : $end_time;
    // 相差时间
    $diffTime = $endTime - $startTime;
    // 计算天数
    $days = intval($diffTime / 86400);
    // 计算小时数
    $remain = $diffTime % 86400;
    $hours = intval($remain / 3600);
    // 计算分钟数
    $remain = $remain % 3600;
    $mins = intval($remain / 60);
    // 计算秒数
    $secs = $remain % 60;
    $res = array(
        "day" => $days,
        "hour" => $hours,
        "min" => $mins,
        "sec" => $secs
    );
    $return_differ = '';
    if ($days > 0) {
        $return_differ .= $days . '天';
    }
    if ($hours > 0) {
        $return_differ .= $hours . '时';
    }
    if ($mins > 0) {
        $return_differ .= $mins . '分';
    }
    return $return_differ;
}

/**
 * 时间转毫秒时间戳
 * @param $time
 * @return float|int
 */
function __cc_strtotimeM($time)
{
    if (str_contains($time, '.')) {
        list($t, $m) = explode(".", $time);
        if (is_string($t)) {
            $t = strtotime($t);
        }
        $time = $t . str_pad($m, 3, "0", STR_PAD_LEFT);
    }
    if (is_numeric($time)) {
        return (int) str_pad($time, 13, "0");
    } else {
        return strtotime($time) * 1000;
    }
}
/**
 * 秒 （转） 年、天、时、分、秒
 * @param $time
 * @return array|bool
 */
function __cc_sec2time($time)
{
    if (is_numeric($time)) {
        $value = array(
            "years" => 0,
            "days" => 0,
            "hours" => 0,
            "minutes" => 0,
            "seconds" => 0,
        );
        if ($time >= 86400) {
            $value["days"] = floor($time / 86400);
            $time = ($time % 86400);
        }
        if ($time >= 3600) {
            $value["hours"] = floor($time / 3600);
            $time = ($time % 3600);
        }
        if ($time >= 60) {
            $value["minutes"] = floor($time / 60);
            $time = ($time % 60);
        }
        $value["seconds"] = floor($time);
        return (array)$value;
    } else {
        return (bool)FALSE;
    }
}
/**
 * 年、天、时、分、秒 （转） 秒
 * @param $value
 * @return int
 */
function __cc_time2sec($value)
{
    $time = intval($value["seconds"]);
    $time += intval($value["minutes"] * 60);
    $time += intval($value["hours"] * 3600);
    $time += intval($value["days"] * 86400);
    $time += intval($value["years"] * 31536000);
    return $time;
}


/**
 * 获取(时间戳转)今天是星期几，只返回（几）
 * @param string|number $unixTime
 * @return string
 */
function __cc_getTimeWeek($unixTime = '')
{
    $unixTime = is_numeric($unixTime) ? $unixTime : time();
    $weekarray = ['日', '一', '二', '三', '四', '五', '六'];
    return $weekarray[date('w', $unixTime)];
}

/**
 * 获取(时间戳转)现在时间段：深夜、凌晨、早晨、上午.....
 * @param string|number $unixTime
 * @return string
 */
function __cc_getTimeDayeSegment($unixTime = '')
{
    $unixTime = is_numeric($unixTime) ? $unixTime : time();
    $H = date('H', $unixTime);
    if ($H >= 19) {
        return '晚上';
    } elseif ($H >= 18) {
        return '傍晚';
    } elseif ($H >= 13) {
        return '下午';
    } elseif ($H >= 12) {
        return '中午';
    } elseif ($H >= 8) {
        return '上午';
    } elseif ($H >= 5) {
        return '早晨';
    } elseif ($H >= 1) {
        return '凌晨';
    } elseif ($H >= 0) {
        return '深夜';
    } else {
        return '';
    }
}

/**
 * 时间差(不够1个小时算一个小时)
 * @param int $s 开始时间戳
 * @param int $e 结束时间戳
 * @return string
 */
function __cc_timeDiff($s, $e)
{
    $time = $e - $s;
    $days = 0;
    if ($time >= 86400) { // 如果大于1天
        $days = (int)($time / 86400);
        $time = $time % 86400; // 计算天后剩余的毫秒数
    }
    $hours = 0;
    if ($time >= 3600) { // 如果大于1小时
        $hours = (int)($time / 3600);
        $time = $time % 3600; // 计算小时后剩余的毫秒数
    }
    $minutes = ceil($time / 60); // 剩下的毫秒数都算作分
    $daysStr = $days > 0 ? $days . '天' : '';
    $hoursStr = ($hours > 0 || ($days > 0 && $minutes > 0)) ? $hours . '时' : '';
    $minuteStr = ($minutes > 0) ? $minutes . '分' : '';
    return $daysStr . $hoursStr . $minuteStr;
}

/**
 * 时间秒数格式化
 * @param int $time 时间秒数
 * @return string
 */
function __cc_timeFormat($time)
{
    $days = 0;
    if ($time >= 86400) { // 如果大于1天
        $days = (int)($time / 86400);
        $time = $time % 86400; // 计算天后剩余的毫秒数
    }
    $hours = 0;
    if ($time >= 3600) { // 如果大于1小时
        $hours = (int)($time / 3600);
        $time = $time % 3600; // 计算小时后剩余的毫秒数
    }
    $minutes = ceil($time / 60); // 剩下的毫秒数都算作分
    $daysStr = $days > 0 ? $days . '天' : '';
    $hoursStr = ($hours > 0 || ($days > 0 && $minutes > 0)) ? $hours . '时' : '';
    $minuteStr = ($minutes > 0) ? $minutes . '分' : '';
    return $daysStr . $hoursStr . $minuteStr;
}
/**
 * 获取毫秒时间戳
 * @return float
 */
function __cc_msecTime()
{
    list($msec, $sec) = explode(' ', microtime());
    $time = explode(".", $sec . ($msec * 1000));
    return $time[0];
}

/**
 * 小时转天/小时
 * @param $hour
 * @return string
 */
function __cc_forumHourDay($hour)
{
    $hour = intval($hour);
    if ($hour > 24) {
        $day = floor($hour / 24);
        $hour -= $day * 24;
        return $day . '天' . $hour . '小时';
    }
    return $hour . '小时';
}

/**
 * 时间格式化
 * @param $date
 * @return false|string
 */
function __cc_forumDate($date)
{
    $dur = time() - $date;
    if ($date > strtotime(date("Y-m-d"))) {
        //今天
        if ($dur < 60) {
            return max($dur, 1) . '秒前';
        } elseif ($dur < 3600) {
            return floor($dur / 60) . '分钟前';
        } elseif ($dur < 86400) {
            return floor($dur / 3600) . '小时前';
        } else {
            return date("H:i", $date);
        }
    } elseif ($date > strtotime(date("Y-m-d", strtotime("-1 day")))) {
        //昨天
        return '昨天';
    } elseif ($date > strtotime(date("Y-m-d", strtotime("-2 day")))) {
        //前天
        return '前天';
    } elseif ($dur > 86400) {
        //x天前
        return floor($dur / 86400) . '天前';
    }
    return date("Y-m-d", $date);
}

/**
 * 获取时间戳今天的第一秒时间戳
 * @param $time
 * @return false|int
 */
function __cc_dayTimeF($time)
{
    return strtotime(date("Y-m-d 00:00:00", __cc_isNumber($time) ? $time : strtotime($time)));
}

/**
 * 获取时间戳今天的最后一秒时间戳
 * @param $time
 * @return false|int
 */
function __cc_dayTimeE($time)
{
    return strtotime(date("Y-m-d 23:59:59", __cc_isNumber($time) ? $time : strtotime($time)));
}

/**
 * 获取当前是本月第几个星期
 * @return float
 */
function getMonthWeek()
{
    $time = strtotime(date("Y-m-01"));
    $w = date('w', $time);
    $j = date("j");
    return ceil(($j . $w) / 7);
}
