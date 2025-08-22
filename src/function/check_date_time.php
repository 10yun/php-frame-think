<?php

/**
 * @title 验证 日期时间
 */

/**
 * 检测日期格式
 * @param string $str 需要检测的字符串
 * @return bool
 */
function cc_is_date($str = '')
{
    $strArr = explode('-', $str);
    if (empty($strArr) || count($strArr) != 3) {
        return false;
    } else {
        list($year, $month, $day) = $strArr;
        if (checkdate(intval($month), intval($day), intval($year))) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 检测时间格式
 * @param string $str 需要检测的字符串
 * @return bool
 */
function cc_is_time($str = '')
{
    $strArr = explode(':', $str);
    $count = count($strArr);
    if ($count < 2 || $count > 3) {
        return false;
    }
    $hour = $strArr[0];
    if ($hour < 0 || $hour > 23) {
        return false;
    }
    $minute = $strArr[1];
    if ($minute < 0 || $minute > 59) {
        return false;
    }
    if ($count == 3) {
        $second = $strArr[2];
        if ($second < 0 || $second > 59) {
            return false;
        }
    }
    return true;
}

/**
 * 检测 日期格式 或 时间格式
 * @param string $str 需要检测的字符串
 * @return bool
 */
function __cc_isDateOrTime($str = '')
{
    return __cc_isDate($str) || __cc_isTime($str);
}

/**
 * 判断是否日期字符串
 */
function cc_is_DateFormat($date, $format = 'Y-m-d'): bool
{
    $d = \DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * 判断是否是日期格式
 */
function cc_is_date_format2($date)
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
function cc_is_timestamp($timestamp)
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
