<?php

/**
 * 
 * ====== 计算
 * 
 */
/**加法 */
function analysCalcJia($num1 = 0, $num2 = 0, $length = 2, $is_45 = true)
{
    if ($is_45) {
        $length_practical = $length + 1;
        $fee = bcadd($num1, $num2, $length_practical);
        return sprintf("%.{$length}f", round($fee, $length));
    }
    return bcadd($num1, $num2, $length);
}
/**减法 */
function analysCalcJian($num1 = 0, $num2 = 0, $length = 2, $is_45 = true)
{
    if ($is_45) {
        $length_practical = $length + 1;
        $fee = bcsub($num1, $num2, $length_practical);
        return sprintf("%.{$length}f", round($fee, $length));
    }
    return bcsub($num1, $num2, $length);
}
/**乘法 */
function analysCalcCheng($num1 = 0, $num2 = 0, $length = 2, $is_45 = true)
{
    if ($is_45) {
        $length_practical = $length + 1;
        $fee = bcmul($num1, $num2, $length_practical);
        return sprintf("%.{$length}f", round($fee, $length));
    }
    return bcmul($num1, $num2, $length);
}
/**除法 */
function analysCalcChu($num1 = 0, $num2 = 0, $length = 2, $is_45 = true)
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
/**求余 */
function analysCalcYushu($num1, $num2 = 0, $length = 2)
{
    $num2 = floatval($num2);
    if (empty($num2)) {
        return 0;
    }
    return bcmod($num1, $num2);
}
/** 百分比 */
function analysCalcBfb($num1 = 0, $num2 = 0, $length = 4, $is_45 = true)
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


// 精确到小数点2位  bccomp【0表示 相同】 【1 表示 num1大】 【-1 表示 num2 大 或 其他】

/**大于 */
function analysCalcDaYu($num1, $num2, $length = 2)
{
    $jiguo =  bccomp($num1, $num2, $length);
    if ($jiguo === 1) {
        return true;
    }
    return false;
}
/**大于等于 */
function analysCalcDYDY($num1, $num2, $length = 2)
{
    $jiguo =  bccomp($num1, $num2, $length);
    if ($jiguo === 1 || $jiguo === 0) {
        return true;
    }
    return false;
}
/**小于 */
function analysCalcXiaoYu($num1, $num2, $length = 2)
{
    $jiguo =  bccomp($num1, $num2, $length);
    if ($jiguo === -1) {
        return true;
    }
    return false;
}
/**小于等于 */
function analysCalcXYDY($num1, $num2, $length = 2)
{
    $jiguo =  bccomp($num1, $num2, $length);
    if ($jiguo === -1 || $jiguo === 0) {
        return true;
    }
    return false;
}
