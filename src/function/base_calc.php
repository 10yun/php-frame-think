<?php

/**
 * @title 计算
 */

//全局设置所有bc数学函数的未设定情况下的小数点保留位数.
// bcscale(3);

/**
 * @action 加法 
 */
function cc_calc_add($num1 = 0, $num2 = 0, $length = 2, $is_45 = true)
{
    if ($is_45) {
        $length_practical = $length + 1;
        $fee = bcadd($num1, $num2, $length_practical);
        return sprintf("%.{$length}f", round($fee, $length));
    }
    return bcadd($num1, $num2, $length);
}
/**
 * @action 减法 
 */
function cc_calc_jian($num1 = 0, $num2 = 0, $length = 2, $is_45 = true)
{
    if ($is_45) {
        $length_practical = $length + 1;
        $fee = bcsub($num1, $num2, $length_practical);
        return sprintf("%.{$length}f", round($fee, $length));
    }
    return bcsub($num1, $num2, $length);
}
/**
 * @action 乘法 
 */
function cc_calc_cheng($num1 = 0, $num2 = 0, $length = 2, $is_45 = true)
{
    if ($is_45) {
        $length_practical = $length + 1;
        $fee = bcmul($num1, $num2, $length_practical);
        return sprintf("%.{$length}f", round($fee, $length));
    }
    return bcmul($num1, $num2, $length);
}
/**
 * @action 除法 
 */
function cc_calc_chu($num1 = 0, $num2 = 0, $length = 2, $is_45 = true)
{
    $num2 = floatval($num2);
    if (empty($num2)) {
        return 0;
    }
    if ($is_45) {
        $length_practical = $length + 1;
        $fee = bcdiv($num1, $num2, $length_practical);
        return sprintf("%.{$length}f", round($fee, $length));
    }
    return bcdiv($num1, $num2, $length);
}
/**
 * @action 求余 
 */
function cc_calc_yushu($num1, $num2 = 0, $length = 2)
{
    $num2 = floatval($num2);
    if (empty($num2)) {
        return 0;
    }
    return bcmod($num1, $num2);
}
/**
 * @action 百分比 
 */
function cc_calc_bfb($num1 = 0, $num2 = 0, $length = 4, $is_45 = true)
{
    $num2 = floatval($num2);
    if (empty($num2)) {
        return 0;
    }
    if ($is_45) {
        $length_practical = $length + 1;
        $fee = bcdiv($num1, $num2, $length_practical);
        return sprintf("%.{$length}f", round($fee, $length));
    }
    return bcdiv($num1, $num2, $length);
}
/**
 * @action 大于
 * @desc 精确到小数点2位  bccomp【0表示 相同】 【1 表示 num1大】 【-1 表示 num2 大 或 其他】
 */
function cc_calc_dayu($num1, $num2, $length = 2)
{
    $jiguo =  bccomp($num1, $num2, $length);
    if ($jiguo === 1) {
        return true;
    }
    return false;
}
/**
 * @action 大于等于
 */
function cc_calc_dydy($num1, $num2, $length = 2)
{
    $jiguo =  bccomp($num1, $num2, $length);
    if ($jiguo === 1 || $jiguo === 0) {
        return true;
    }
    return false;
}
/**
 * @action 小于
 */
function cc_calc_xiaoyu($num1, $num2, $length = 2)
{
    $jiguo =  bccomp($num1, $num2, $length);
    if ($jiguo === -1) {
        return true;
    }
    return false;
}
/**
 * @action 小于等于
 */
function cc_calc_xydy($num1, $num2, $length = 2)
{
    $jiguo =  bccomp($num1, $num2, $length);
    if ($jiguo === -1 || $jiguo === 0) {
        return true;
    }
    return false;
}
