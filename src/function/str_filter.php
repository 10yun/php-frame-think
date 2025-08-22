<?php

/**
 * @action 去除连续空格 consecutive
 */
function cc_trim_space_lx($str = '')
{
    $str = str_replace("　", ' ', stripslashes($str));
    $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
    return $str;
}
/**
 * @action 去除所有空格 all
 */
function cc_trim_space_all($str = '')
{
    $str = str_replace("　", ' ', stripslashes($str));
    $str = preg_replace("/[\r\n\t ]/", '', $str);
    return $str;
}

/**
 * 清除 空格、换行
 * 清除空格--等一些字符,留下纯文本
 */
function cc_trim_spaceEnter($str = '')
{
    $replace_arr = array(
        " " => "",
        "　" => "",
        "\t" => "",
        "\n" => "",
        "\r" => ""
    );
    $result = str_replace(array_keys($replace_arr), array_values($replace_arr), $str);
    return $result;
}
/**
 * 去除逗号
 */
function cc_filter_comma(string $str = ''): string
{
    $str = preg_replace('/,{2,}/', ',', $str);
    $str = preg_replace('/,$/', '', $str);
    return $str;
}
/**
 * 字符串符号转html
 * 字符串,替换
 * 符号 - html转义符
 * @param string $str
 * @return mixed
 */
function cc_symbolToHtmlcode($str = '')
{
    $replace_arr = array(
        '&' => '&amp;',
        '"' => '&quot;',
        "'" => '&#039;',
        '<' => '&lt;',
        '>' => '&gt;'
    );
    $result = str_replace(array_keys($replace_arr), array_values($replace_arr), $str);
    return $result;
}

// htmlcode 转 字符串符号
// html转义符 - 符号
function cc_htmlcodeToSymbol($str = '')
{
    $replace_arr = array(
        '&nbsp;' => ' ',
        '&amp;' => '&',
        '&quot;' => '"',
        '&#039;' => "'",
        '&ldquo;' => '“',
        '&rdquo;' => '”',
        '&mdash;' => '—',
        '&lt;' => '<',
        '&gt;' => '>',
        '&middot;' => '·',
        '&hellip;' => '…'
    );
    $result = str_replace(array_keys($replace_arr), array_values($replace_arr), $str);
    return $result;
}
