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
