<?php

/**
 * 【ctocode】      常用函数 - security 相关处理
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
 * @todo 只保留字母、英文、数字、下划线、破折号、@
 * @author ctocode-zhw
 * @param  
 */
function ctoSecurityChsDash($chars, $encoding = 'utf8')
{
    $pattern = ($encoding == 'utf8') ? '/[\x{4e00}-\x{9fa5}a-zA-Z0-9\@\_\-]/u' : '/[\x80-\xFF]/';
    preg_match_all($pattern, $chars, $result);
    $temp = join('', $result[0]);
    return $temp;
}

/**
 * @todo 递归转义数组
 * @author ctocode-zwj
 * @param mixed $value 待转义变量，数组/字符串
 */
function ctoSecurityAddslashes($value)
{
    if (is_string($value)) {
        return addslashes($value);
    }
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            if (is_string($v)) {
                $value[$k] = ctoSecurityAddslashes($v);
            } else if (is_array($v)) { // 再加判断,如果是数组,调用自身,再转
                $value[$k] = ctoSecurityAddslashes($v);
            }
        }
    }
    return $value;
}
/**
 * @todo 递归过滤XSS
 * @author ctocode-zwj
 * @param mixed $value 待过滤变量，数组/字符串
 */
function ctoSecurityRemoveXss($value)
{
    if (is_string($value)) {
        return ctoSecurityDelXSS($value);
    }
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            if (is_string($v)) {
                $value[$k] = ctoSecurityRemoveXss($v);
            } else if (is_array($v)) { // 再加判断,如果是数组,调用自身,再转
                $value[$k] = ctoSecurityRemoveXss($v);
            }
        }
    }
    return $value;
}

/**
 * 安全过滤函数---只对字符串过滤，不能过滤数组里面的字符串
 *php防XSS攻击通用过滤.
 * @author ctocode-zwj
 * @param string $val 过滤字符串
 * @return string
 */
function ctoSecurityDelXSS($val)
{
    // $val = preg_replace ( '/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val );
    $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val);
        // with a ;
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val);
    }
    $ra1 = array(
        'javascript',
        'vbscript',
        // 'expression',
        'applet',
        'meta',
        'xml',
        'blink',
        // 'link',
        // 'style',
        'script',
        // 'embed',
        'object',
        'iframe',
        'frame',
        'frameset',
        'ilayer',
        // 'layer',
        'bgsound'
        // 'title',
        // 'base'
    );
    $ra2_str_arr = array(
        'onabort__onactivate__onafterprint__onafterupdate',
        'onbeforeactivate__onbeforecopy__onbeforecut__onbeforedeactivate__onbeforeeditfocus__onbeforepaste__onbeforeprint__onbeforeunload__onbeforeupdate__onblur__onbounce',
        'oncellchange__onchange__onclick__oncontextmenu__oncontrolselect__oncopy__oncut',
        'ondataavailable__ondatasetchanged__ondatasetcomplete__ondblclick__ondeactivate__ondrag__ondragend__ondragenter__ondragleave__ondragover__ondragstart__ondrop',
        'onerror__onerrorupdate',
        'onfilterchange__onfinish__onfocus__onfocusin__onfocusout',
        'onhelp',
        'onkeydown__onkeypress__onkeyup',
        'onlayoutcomplete__onload__onlosecapture',
        'onmousedown__onmouseenter__onmouseleave__onmousemove__onmouseout__onmouseover__onmouseup__onmousewheel__onmove__onmoveend__onmovestart',
        'onpaste__onpropertychange',
        'onreadystatechange__onreset__onresize__onresizeend__onresizestart__onrowenter__onrowexit__onrowsdelete__onrowsinserted',
        'onscroll__onselect__onselectionchange__onselectstart__onstart__onstop__onsubmit__onunload'
    );
    $ra2_str_arr = implode("__", $ra2_str_arr);
    $ra2 = explode("__", $ra2_str_arr);
    $ra = array_merge($ra1, $ra2);

    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            // $replacement = substr ( $ra[$i], 0, 2 ) . '<x>' . substr ( $ra[$i], 2 );
            $replacement = substr($ra[$i], 0, 2) . ' ' . substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    return $val;
}
/*
 * 功能：用来过滤字符串和字符串数组，防止被挂马和sql注入
 * 参数$data，待过滤的字符串或字符串数组，
 * $force为true，忽略get_magic_quotes_gpc
 */
function ctoSecurityIn($data, $force = false)
{
    if (is_string($data)) {
        $data = trim(htmlspecialchars($data)); // 防止被挂马，跨站攻击
        if (($force == true) || (!get_magic_quotes_gpc())) {
            $data = addslashes($data); // 防止sql注入
        }
        return $data;
    } else if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = ctoSecurityIn($value, $force);
        }
        return $data;
    } else {
        return $data;
    }
}
// 用来还原字符串和字符串数组，把已经转义的字符还原回来
function ctoSecurityOut($data)
{
    if (is_string($data)) {
        return $data = stripslashes($data);
    } else if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = ctoSecurityOut($value);
        }
        return $data;
    } else {
        return $data;
    }
}
// 表单令牌加密
function input_hashcode($s = 0)
{
    $code = authcode('CTOCODE/POST/CODE', 'ENCODE', 'YouDianYiSi', 3600);
    return $s == 1 ? $code : '<input type="hidden" name="SyScode" id="SyScode" value="' . $code . '"/>';
}
function check_hashcode($s = '')
{
    if (trim($s) != '') {
        return authcode($s, 'DECODE', 'YouDianYiSi') == 'CTOCODE/POST/CODE' ? true : false;
    }
    $v = isset($_POST['SyScode']) ? $_POST['SyScode'] : '';
    if ($v == '' || !locationpost()) {
        return false;
    }
    return authcode($v, 'DECODE', 'YouDianYiSi') == 'BAIYU/POST/CODE' ? true : false;
}

/**
 * stripslashes() 函数删除由 addslashes() 函数添加的反斜杠。
 * 提示：该函数可用于清理从数据库中或者从 HTML 表单中取回的数据。
 * @param string $str
 * @return string
 */
function deldanger($str = '')
{
    if (trim($str) == '')
        return '';
    $str = stripslashes($str);
    $str = DelXSS($str);
    $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
    $str = preg_replace("/script/i", 'ｓｃｒｉｐｔ', $str);
    $str = preg_replace("/<[/]{0,1}(link|meta|ifr|fra)[^>]*>/i", '', $str);
    return addslashes($str);
}
