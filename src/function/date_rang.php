<?php

/**
 * 获取指定日期段内每一天的日期
 * @param  String|Date  $startDate 开始日期
 * @param  String|Date  $endDate   结束日期
 * @return Array
 */
function cc_date_range_everyday($startDate = '', $endDate = '')
{
    $starTimestamp = strtotime($startDate);
    $endTimestamp = strtotime($endDate);
    // 计算日期段内有多少天
    $days = ($endTimestamp - $starTimestamp) / 86400 + 1;
    // 保存每天日期
    $date = array();
    for ($i = 0; $i < $days; $i++) {
        $date[] = date('Y-m-d', $starTimestamp + (86400 * $i));
    }
    return $date;
}
/**
 * 获取日期区间
 */
function cc_date_ranges_everyday($date_arr)
{
    // 日期配置
    $weekname = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
    // 星期日排到末位
    if (empty($week)) {
        $week = 7;
    }
    $head_arr = [];
    foreach ($date_arr as $val) {
        // 
        $day_str = date("d", strtotime($val));
        $month_day_str = date("m-d", strtotime($val));
        $month_day_str2 = date("m月d日", strtotime($val));
        $xingqi = Date("w", strtotime($val));
        $head_arr['day'][] = $day_str . "日";
        $head_arr['month_day'][] = $month_day_str;
        $head_arr['month_day2'][] = $month_day_str2;
        $head_arr['week'][] = $weekname[$xingqi];
    }
    return $head_arr;
}
/**
 * 获取日期，获取本月所有天数
 */
function cc_date_month_everyday($curr_date)
{
    // 验证日期有效性
    $timestamp = strtotime($curr_date);
    if ($timestamp === false) {
        throw new InvalidArgumentException("无效的日期格式: {$curr_date}");
    }

    // 日期配置
    $weekname = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
    //星期日排到末位
    if (empty($week)) {
        $week = 7;
    }
    // 获取当前月份的总天数（如8月返回31）
    $day_num = date("t", $timestamp); // 直接使用已验证的时间戳
    $head_arr = [];
    for ($i = 1; $i <= $day_num; $i++) {
        // 生成每一天的日期（如 "2023-08-01"）
        $for_date = Date("Y-m-{$i}", strtotime($curr_date));
        // 获取该日期是星期几（0=周日，1=周一，...，6=周六）
        $xingqi = Date("w", strtotime($for_date));
        // 存储“X日”格式的日期
        $head_arr['day'][] = $i . "日";
        // 存储对应的星期名称
        $head_arr['week'][] = $weekname[$xingqi];
    }
    return $head_arr;
}
