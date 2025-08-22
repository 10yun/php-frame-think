<?php

function ctoStrRandId($length)
{
    date_default_timezone_set('PRC');
    $random_str = date('YmdHis', time());
    $random_str = $random_str . rand(1000, 999999);
    return $random_str;
}
// 验证Hash字符串是否合法
function ctoStrCheckhashv($v, $key)
{
    if (trim($v) == '' || !strpos($v, '-')) {
        return false;
    }
    $str = explode('-', $v);
    if ($str[0] != md5($key . $str[1])) {
        return false;
    }
    return true;
}
/**
 *  出现科学计数法，还原成字符串
 * @author ctocode-zhw
 * @param string $num
 * @return mixed
 */
function ctoStrNumToStr($num)
{
    if (stripos($num, 'e') === false)
        return $num;
    $num = trim(preg_replace('/[=\'"]/', '', $num, 1), '"');
    $result = "";
    while ($num > 0) {
        $v = $num - floor($num / 10) * 10;
        $num = floor($num / 10);
        $result = $v . $result;
    }
    return $result;
}
function ctoValueSum()
{
    $ary = func_get_args();
    $sum = 0;
    foreach ($ary as $int) {
        $sum += intval($int);
    }
    return $sum;
}

function ctoStrSuperReplace($str = '', $type = '')
{
    if (trim($str) == '')
        return '';
    switch ($type) {
        case 'nosql': // 加反斜杠。防止sql，
            $str = addslashes($str);
            break;
        case 'blank': // 删除空白的
            $str = preg_replace("/[\r\n]{1,}/", "\n", $str);
            break;
    }
}


function ctoStrSubstring($str, $len, $dot = '')
{
    $strlen = strlen($str);
    if ($strlen <= $len)
        return $str;

    // 迁移到方法库
    // $str = cc_htmlcodeToSymbol($str);


    $rs = '';
    $web_lang = 'gbk';
    if (strtolower($web_lang) == 'gb2312' || strtolower($web_lang) == 'gbk') {
        $dotlen = strlen($dot);
        $maxi = $len - $dotlen - 1;
        for ($i = 0; $i < $maxi; $i++) {
            $rs .= ord($str[$i]) > 127 ? $str[$i] . $str[++$i] : $str[$i];
        }
    } else {
        $n = $tn = $noc = 0;
        while ($n < $strlen) {
            $t = ord($str[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t < 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $len)
                break;
        }
        if ($noc > $len)
            $n -= $tn;
        if ($dot != '')
            $n -= strlen($dot);
        $rs = substr($str, 0, $n);
    }
    // 迁移到方法库
    $rs = '';
    // $rs = cc_symbolToHtmlcode($str); 
    return $rs . $dot;

    if ($_glb['web_lang'] == 'UTF-8') {
        $str = ctoStrIconv($str, 'UTF-8', 'GBK');
        $strcut = ctoStrIconv($strcut, 'GBK', 'UTF-8');
    }
    return $strcut . ($rdot ? $dot : '');
    return mb_substr($str, 0, $len, 'GBK') . $dot;
    return mb_substr($str, 0, $len, 'UTF-8') . $dot;
}
function ctoStrIconv($str, $in_charset, $out_charset = '')
{
    echo "das";
    exit();
    $in_charset = strtoupper(trim($in_charset));
    $out_charset = strtoupper(trim($out_charset));
    if ($in_charset == 'UTF-8' && ($out_charset == 'GBK' || $out_charset == 'GB2312')) {
        return utf_gbk($str);
    } elseif ($out_charset == 'UTF-8' && ($in_charset == 'GBK' || $in_charset == 'GB2312')) {
        return gbk_utf($str);
    } elseif ($in_charset == 'GBK' && $out_charset == 'BIG5') {
        return big5_gbk($str);
    } elseif ($in_charset == 'BIG5' && $out_charset == 'GBK') {
        return gbk_big5($str);
    } elseif ($in_charset == 'BIG5' && $out_charset == 'UTF-8') {
        return gbk_utf(big5_gbk($str));
    } elseif ($in_charset == 'UNICODE') {
        return un_gbk($str);
    } elseif ($in_charset == 'PINYIN') {
        return gbk_pinyin($str);
    } elseif ($in_charset == 'PY') {
        return gbk_py($str);
    } else {
        return $str;
    }
}

/**
 * 数字(>0)加密变长
 * @author ctocode-zhw
 * @version 2015-07-12
 * @param number $int
 * @param string $type
 * @return number|string
 */
function ctoValueNumEncode($int = 0, $type = 'ENCODE')
{
    if ($type == 'ENCODE') {
        $int = (int) $int;
        if ($int == 0) {
            return 0;
        }
        $len = strlen($int);
        $temp = (300000000 + $int - 19750806) * 2;
        return $temp . $len;
    } else {
        $num = (int) substr($int, 0, strlen($int) - 1);
        $len = (int) substr($int, -1, 1);
        if ($num == 0 || $len == 0) {
            return 0;
        }
        $new = (int) (($num / 2) + 19750806 - 300000000);
        if (strlen($new) == $len) {
            return $new;
        }
    }
    return 0;
}
function ctoValueNumDecode($int = 0, $type = 'DECODE')
{
    return ctoValueNumEncode($int, 'DECODE');
}
