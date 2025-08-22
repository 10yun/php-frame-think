<?php

/**
 * @title 日期时间
 */

/**
 * @action 判断 时间是否符合某个格式
 */
function cc_date_check_format(string $value, string $format = 'Y-m-d'): bool
{
    $date = DateTime::createFromFormat($format, $value);
    return $date && $date->format($format) === $value;
}
/**
 * @action判断某个时间（年/月/日/时分秒）是否完全包含在指定范围内
 *
 * @param string|DateTimeInterface $input 时间值（支持年/月/日/时间/DateTime）
 * - 可为 "2020"、"2020-07"、"2020-07-25"、"2020-07-25 12:00:00"
 * @param string|DateTimeInterface $startDate 开始时间
 * @param string|DateTimeInterface $endDate 结束时间
 * @param string $timezone 时区（默认 Asia/Shanghai）
 * @return bool
 */
function cc_date_time_in_scope(
    $input,
    $startDate,
    $endDate,
    string $timezone = 'Asia/Shanghai'
): bool {
    $tz = new DateTimeZone($timezone);

    // 范围起止时间
    $start = ($startDate instanceof DateTimeInterface) ? $startDate : new DateTime($startDate, $tz);
    $end = ($endDate instanceof DateTimeInterface) ? $endDate : new DateTime($endDate, $tz);

    // 解析 input 到 inputStart 和 inputEnd
    if ($input instanceof DateTimeInterface) {
        $inputStart = $input;
        $inputEnd   = $input;
    } elseif (is_string($input)) {
        if (preg_match('/^\d{4}$/', $input)) {
            // 年份：2025
            $inputStart = new DateTime($input . '-01-01 00:00:00', $tz);
            $inputEnd   = new DateTime($input . '-12-31 23:59:59', $tz);
        } elseif (preg_match('/^\d{4}-\d{2}$/', $input)) {
            // 月份：2025-07
            $inputStart = new DateTime($input . '-01 00:00:00', $tz);
            $inputEnd   = (clone $inputStart)->modify('first day of next month')->modify('-1 second');
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
            // 日期：2025-07-25
            $inputStart = new DateTime($input . ' 00:00:00', $tz);
            $inputEnd   = new DateTime($input . ' 23:59:59', $tz);
        } else {
            // 其他格式：当作时间点
            try {
                $point = new DateTime($input, $tz);
                $inputStart = $point;
                $inputEnd   = $point;
            } catch (Exception $e) {
                return false;
            }
        }
    } else {
        return false;
    }
    // 完全包含判定
    return $inputStart >= $start && $inputEnd <= $end;
}


/**
 * @action 判断某个时间（年、年月、日期、完整时间）与目标时间范围是否有交集
 *
 * @param string|DateTimeInterface $inputDate 
 * - 可为 "2020"、"2020-07"、"2020-07-25"、"2020-07-25 12:00:00"、DateTime
 * @param string|DateTimeInterface $startDate 范围开始时间
 * @param string|DateTimeInterface $endDate 范围结束时间
 * @param string $timezone 默认 Asia/Shanghai
 * @return bool 是否有交集
 */
function cc_date_overlap_scope(
    $inputDate,
    $startDate,
    $endDate,
    string $timezone = 'Asia/Shanghai'
): bool {
    $tz = new DateTimeZone($timezone);

    // 范围起止
    $start = ($startDate instanceof DateTimeInterface) ? $startDate : new DateTime($startDate, $tz);
    $end = ($endDate instanceof DateTimeInterface) ? $endDate : new DateTime($endDate, $tz);

    // 统一处理输入时间
    if ($inputDate instanceof DateTimeInterface) {
        // 精确时间：直接判断
        return $inputDate >= $start && $inputDate <= $end;
    }

    // 字符串类型
    $normalized = trim((string)$inputDate);
    $patternMap = [
        '/^\d{4}$/' => 'Y',                  // 年
        '/^\d{4}-\d{2}$/' => 'Y-m',          // 年月
        '/^\d{4}-\d{2}-\d{2}$/' => 'Y-m-d',  // 年月日
        '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/' => 'Y-m-d H:i:s' // 精确时间
    ];

    $rangeStart = null;
    $rangeEnd = null;

    foreach ($patternMap as $pattern => $format) {
        if (preg_match($pattern, $normalized)) {
            try {
                if ($format === 'Y') {
                    $rangeStart = new DateTime($normalized . '-01-01 00:00:00', $tz);
                    $rangeEnd = (clone $rangeStart)->modify('first day of January next year')->modify('-1 second');
                } elseif ($format === 'Y-m') {
                    $rangeStart = new DateTime($normalized . '-01 00:00:00', $tz);
                    $rangeEnd = (clone $rangeStart)->modify('first day of next month')->modify('-1 second');
                } elseif ($format === 'Y-m-d') {
                    $rangeStart = new DateTime($normalized . ' 00:00:00', $tz);
                    $rangeEnd = new DateTime($normalized . ' 23:59:59', $tz);
                } else {
                    // 精确时间
                    $dt = new DateTime($normalized, $tz);
                    return $dt >= $start && $dt <= $end;
                }
            } catch (Exception $e) {
                return false;
            }
            break;
        }
    }

    // 未匹配任何格式
    if (!$rangeStart || !$rangeEnd) {
        return false;
    }

    // 判断是否与目标时间段有交集
    return $rangeEnd >= $start && $rangeStart <= $end;
}



/**
 * @action 计算两个日期之间相差的天数
 */
function cc_date_diff_days($a, $b)
{
    return round(abs(strtotime($a) - strtotime($b)) / 86400);
}

/**
 * @action 解析开始结束时间
 * @author ctocode
 * @version 2021-09-24
 * @param string $str 需要获取的间隔类型
 * @param string $type 类型：time时间戳，date时间格式
 * @return array 
 */
function cc_date_to_interval($str = '', $type = 'time')
{
    $curr_time = time();

    switch ($str) {
        case '今天':
        case '今日':
        case 'today':
            /**
             * 今日
             */
            $time_begin = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $time_end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            break;
        /**
             * 昨日
             */
        case '昨日':
        case '昨天':
        case 'yesterday':
            $time_begin = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
            $time_end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
            break;
        /**
             * 最近七天
             */
        case '最近七天':
        case 'lately7':
            $time_begin = strtotime('-7 days');
            $time_end = time();
            break;
        /**
             * 最近三十天
             */
        case '最近三十天':
        case 'lately30':
            $time_begin = strtotime('-30 days');
            $time_end = time();
            break;
        /**
             * 上周
             */
        case '上周':
            $time_begin = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
            $time_end = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));
            break;
        /**
             * 本周
             * 【本周】 本周的第一天到本周的最后一天
             */
        case '本周':
        case 'this_week':
            // //判断当天是星期几，0表星期天，1表星期一，6表星期六
            // $w_day = date("w", time());
            // //php处理当前星期时间点上，根据当天是否为星期一区别对待
            // if ($w_day == '1') {
            //     $cflag = '+0';
            //     $lflag = '-1';
            // } else {
            //     $cflag = '-1';
            //     $lflag = '-2';
            // }
            // //本周一零点的时间戳
            // $frist = strtotime(date('Y-m-d', strtotime("$cflag week Monday", time())));

            // $time_begin = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y'));
            // $time_end = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('Y'));


            $time_begin = strtotime(date('Y-m-d', ($curr_time - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)));
            $time_end = $time_begin + 7 * 24 * 3600 - 1;
            break;
        /**
             * 本月
             * 【本月】 本月的第一天到本月的最后一天
             */
        case '本月':
        case 'this_month':
        case 'month':
            // $time_begin = mktime(00, 00, 00, date('m', strtotime(date('Y-m'))), 01);
            // $time_end = mktime(23, 59, 59, date('m', strtotime(date('Y-m'))) + 1, 00);
            $time_begin = mktime(0, 0, 0, date('m'), 1, date('Y'));
            $time_end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));


            // $startdate = date('Y-m-01 00:00:00');
            // $startdate = date('Y-m-01 00:00:00', time());
            // $startdate = date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));

            // $end_date = date('Y-m-d', strtotime("$startdate +1 month -1 day"));
            // $end_month = date('Y-m-d 23:59:59', strtotime("$startdate +1 month -1 day"));

            // $end_time = strtotime($startdate) + 86400 - 1;
            // $end_time = strtotime($startdate . ' 23:59:59');


            break;
        /**
             * 上月
             * 【上个月】  上个月的第一天到上个月的最后一天
             */
        case '上月':
        case 'last_month':
            // $time_begin = mktime(00, 00, 00, date('m', strtotime(date('Y-m'))) - 1, 01);
            // $time_end = mktime(23, 59, 59, date('m', strtotime(date('Y-m'))) + 1 - 1, 00);
            $time_begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
            $time_end = mktime(23, 59, 59, date('m') - 1, date('t'), date('Y'));
            break;
        /**
             * 【近3月】最近三个月
             */
        case '近3月':
        case '近三月':
        case 'three_month':
            $time_begin =  mktime(23, 59, 59, date('m') - 3, date('t'), date('Y'));
            $time_end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
            break;
        /**
             * 本季
             */
        case '本季':
        case 'this_quarter':
            $season = ceil((date('n')) / 3); //当月是第几季度
            // $time_begin = mktime(0, 0, 0, $season * 3 - 2, 1, date('Y'));
            // $time_end = mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y'));

            $time_begin = mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'));
            $time_end = mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y'));
            break;
        /**
             * 上季
             */
        case '上季':
            $season = ceil((date('n')) / 3); //当月是第几季度
            // $time_begin = mktime(0, 0, 0, $season * 3 - 5, 1, date('Y'));
            // $time_end = mktime(23, 59, 59, $season * 3 - 3, date('t', mktime(0, 0, 0, $season * 3 - 3, 1, date("Y"))), date('Y'));
            $time_begin = mktime(0, 0, 0, $season * 3 - 3 - 3 + 1, 1, date('Y'));
            $time_end = mktime(23, 59, 59, $season * 3 - 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y'));
            break;
        /**
             * 今年、本年
             */
        case '本年':
        case '今年':
        case 'year':
            $time_begin = mktime(0, 0, 0, 1, 1, date('Y', time()));
            $time_end = mktime(23, 59, 59, 12, 31, date('Y', time()));
            break;
        /**
             * 去年
             */
        case '去年':
            $time_begin = mktime(0, 0, 0, 1, 1, date('Y', time()) - 1);
            $time_end = mktime(23, 59, 59, 12, 31, date('Y', time()) - 1);
            break;
        // 历史 
        case '自定义':
        case 'history':
        case 'history':
            $startdate = ctoRequest('startdate');
            $enddate = ctoRequest('enddate');
            if (!empty($startdate)) {
                $date['startdate'] = strtotime($startdate . ' 00:00:00');
                $startdate = !empty($startdate) ? $startdate . ' 00:00:00' : $startdate;
                $startdate = strtotime('2015-01-01 00:00:00');
                $startdate = strtotime($startdate);
            }
            if (!empty($enddate)) {
                $date['enddate'] = strtotime($enddate . ' 23:59:59');
                $enddate = !empty($enddate) ? $enddate . ' 23:59:59' : $enddate;
                $y = date('Y', time());
                $enddate = strtotime($y . '-12-31 23:59:59');
                $enddate = strtotime($enddate) + 86400 - 1;
            }

            break;
    }
    if ($type == 'time' && !empty($time_begin) && !empty($time_end)) {
        // if (cc_is_timestamp($time_begin) && cc_is_timestamp($time_end)) {
        //     return array($time_begin, $time_end);
        // }
        // return array($time_begin, $time_end);
        return array(
            'time_begin' => $time_begin,
            'time_end' => $time_end,
        );
    } else if ($type == 'date' && !empty($time_begin) && !empty($time_end)) {
        $beginDate = date('Y-m-d 00:00:00', $time_begin);
        $endDate = Date("Y-m-d 23:59:59", $time_end);
        return array(
            'date_begin' => $beginDate,
            'date_end' => $endDate,
        );
    }
    return array(
        'time_begin' => 0,
        'time_end' => 0,
    );
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
function cc_time_diff_str($start_time = null, $end_time = null, $format = 'Y-m-d')
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
function cc_time_differ_full($begin_time, $end_time)
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
function cc_time_to_timeM($time)
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
    return strtotime(date("Y-m-d 00:00:00", cc_is_type_number($time) ? $time : strtotime($time)));
}

/**
 * 获取时间戳今天的最后一秒时间戳
 * @param $time
 * @return false|int
 */
function __cc_dayTimeE($time)
{
    return strtotime(date("Y-m-d 23:59:59", cc_is_type_number($time) ? $time : strtotime($time)));
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
// 生日转年龄
function cc_birthday_to_age($birthday = null)
{
    $age = strtotime($birthday);
    if ($age === false) {
        return false;
    }
    list($y1, $m1, $d1) = explode("-", date("Y-m-d", $age));
    $now = strtotime("now");
    list($y2, $m2, $d2) = explode("-", date("Y-m-d", $now));
    $age = $y2 - $y1;
    if ((int)($m2 . $d2) < (int)($m1 . $d1))
        $age -= 1;
    return $age;

    // list($year, $month, $day) = explode("-", $birthday);
    // $year_diff = date("Y") - $year;
    // $month_diff = date("m") - $month;
    // $day_diff  = date("d") - $day;
    // if ($day_diff < 0 || $month_diff < 0)
    //     $year_diff--;
    // return $year_diff;
}
/**
 * @action 时间转时长
 * 时间换算时长
 */
function cc_date_to_duration($date1, $date2 = null, $type = 'year')
{
    if (empty($date2)) {
        $date2 = date('Y-m-d', time());
    }
    $d1 = explode('-', $date1);
    $d2 = explode('-', $date2);
    if (strtotime($date1) - strtotime($date2) > 0) {
        $monthsFromYear   = abs($d1[0] - $d2[0]) * 12;
        $monthsFromMonth  = $d1[1] - $d2[1];
    } else {
        $monthsFromYear   = abs($d2[0] - $d1[0]) * 12;
        $monthsFromMonth  = $d2[1] - $d1[1];
    }
    $monthsLast = $monthsFromYear + $monthsFromMonth;
    $resStr = '';
    switch ($type) {
        case 'month':
            $resStr = $monthsLast;
            break;
        case 'year':
        default:
            $resStr = $monthsLast / 12;
            $resStr = number_format($resStr, 1);
            break;
    }
    return $resStr;
}
