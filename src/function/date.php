<?php

/**
 * 计算两个日期之间相差的天数
 */
function _cc_date_diff_days($a, $b)
{
    return round(abs(strtotime($a) - strtotime($b)) / 86400);
}

/**
 * 解析开始结束时间
 * @author ctocode
 * @version 2021-09-24
 * @param string $str 需要获取的间隔类型
 * @param string $type 类型：time时间戳，date时间格式
 * @return array 
 */
function _cc_date_to_interval($str = '', $type = 'time')
{
    $curr_time = time();

    $beginTime = 0;
    $endTime = 0;
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
    }
    if ($type == 'time' && !empty($time_begin) && !empty($time_end)) {
        // if (_cc_check_timestamp($time_begin) && _cc_check_timestamp($time_end)) {
        //     return array($time_begin, $time_end);
        // }
        return array($time_begin, $time_end);
    } else if ($type == 'date' && !empty($time_begin) && !empty($time_end)) {
        $beginDate = date('Y-m-d 00:00:00', $time_begin);
        $endDate = Date("Y-m-d 23:59:59", $time_end);
        return array($beginDate, $endDate);
    }
    return array($beginTime, $endTime);
}
