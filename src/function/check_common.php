<?php

/**
 * @title 验证 常用
 */


/**
 * @action 检测手机号码格式，验证手机号码
 * @version 2016-10-17
 * @author ctocode-zhw
 * @link https://www.10yun.com
 * @param string|int $str 需要检测的字符串
 * @return boolean
 */
function cc_is_mobile($str = null): bool
{
    // $regex = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,1,3,5,6,7,8]{1}\d{8}$|^18[\d]{9}$#';
    // $regex ="/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/"
    // $regex = "/^1[34578]{1}\d{9}$/";
    // $regex = '/^((0\d{2,3}-\d{7,8})|(\d{7,8})|(1[35847]\d{9}))$/';
    // '/^0?(13|14|15|17|18)[0-9]{9}$/'
    // $regex = "/^1([3456789])\d{9}$/";
    $regex = '/^1[3|4|5|6|7|8|9][0-9]\d{4,8}$/';
    if (strlen($str) != 11) {
        return false;
    }
    if (!is_numeric($str)) {
        return false;
    }
    return preg_match($regex, $str) ? true : false;
}
/**
 * @action 检测邮箱格式
 * @author ctocode-zhw
 * @version 2017-06-21
 * @param string $str 邮箱
 * @return bool
 */
function cc_check_email(string $str = ''): bool
{
    return filter_var($str, FILTER_VALIDATE_EMAIL) ? true : false;

    // $regex = "/^[a-z0-9]+[.a-z0-9_-]*@[a-z0-9]+[.a-z0-9_-]*\.[a-z0-9]+$/i"
    // $regex = "/[a-za-z0-9]+@[a-za-z0-9]+.[a-z]{2,4}/"
    $regex = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[-_a-z0-9][-_a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,})$/i';
    return preg_match($regex, $str) ? true : false;
}

/**
 * @action 身份证验证
 * @param string|int $str 
 * @return bool 
 */
function cc_check_idcard(string|int $str = ''): bool
{
    $regex = '/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/';
    return preg_match($regex, $str) ? true : false;
}

/**
 * 判断身份证是否正确
 * @param $id
 * @return bool
 */
function __cc_isIdcard($id = '')
{
    $id = strtoupper($id);
    $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = array();
    if (!preg_match($regx, $id)) {
        return FALSE;
    }
    if (15 == strlen($id)) {
        //检查15位
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
        @preg_match($regx, $id, $arr_split);
        //检查生日日期是否正确
        $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) {
            return FALSE;
        } else {
            return TRUE;
        }
    } else {
        //检查18位
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        //检查生日日期是否正确
        if (!strtotime($dtm_birth)) {
            return FALSE;
        } else {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $sign = 0;
            for ($i = 0; $i < 17; $i++) {
                $b = (int)$id[$i];
                $w = $arr_int[$i];
                $sign += $b * $w;
            }
            $n = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id, 17, 1)) {
                return FALSE;
            } else {
                return TRUE;
            }
        }
    }
}
/**
 * @action 判断是否域名格式,检测域名格式
 * 
 * @param string $str
 * @return bool
 */
function cc_is_domain_url($str = ''): bool
{
    $regex = "/^(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
    return preg_match($regex, $str) ? true : false;
    $regex = "/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
    // $regex = "^http://[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$";
    // $regex = "/^(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
    return preg_match($regex, $str) ? true : false;
}
/**
 * 正则判断是否MAC地址
 * @param $str
 * @return bool
 */
function cc_is_mac_address($str = '')
{
    if (preg_match("/^[A-Fa-f\d]{2}:[A-Fa-f\d]{2}:[A-Fa-f\d]{2}:[A-Fa-f\d]{2}:[A-Fa-f\d]{2}:[A-Fa-f\d]{2}$/", $str)) {
        return true;
    } else {
        return false;
    }
}
