<?php

/**
 * @title 验证 类型
 */

/**
 * @action 正则判断是否纯数字
 * 
 * 验证数字
 * @param $str
 * @return bool
 */
function cc_is_type_number($str = '')
{
    $regex1 = "/^\d+$/";
    // 必须为不为0开头的纯数字,请重新填写
    $regex2 = "/^(0|[1-9][0-9]*)$/";
    return preg_match($regex1, $str) ? true : false;
}
/**
 * @action 判断字符串是否为 Json 格式
 * @param  string  $data  Json 字符串
 * @param  bool    $assoc 是否返回关联数组。默认返回对象
 * @return array|bool|object 成功返回转换后的对象或数组，失败返回 false
 */
function cc_is_type_json($data = '', $assoc = false)
{
    $data = json_decode($data, $assoc);
    if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
        return true;
    }
    return false;
}

function cc_is_type_json2($json_str, $flag = true)
{
    $json_str = str_replace('＼＼', '', $json_str);
    $out_arr = array();
    preg_match('/{.*}/', $json_str, $out_arr);
    // dd($out_arr);
    if (!empty($out_arr)) {
        $result = json_decode($out_arr[0], $flag);
        return true;
    } else {
        return false;
    }
    return true;
}

/**
 * @action 验证
 */
function cc_check_all($type = '', $str = '')
{
    $preg_rule = [
        // 验证用户名是否以字母开头
        'user_name' => "/^[a-za-z]{1}([a-za-z0-9]|[._]){3,19}$/",
        // 验证密码只能为数字和字母的组合
        'password' => "/^(w){4,20}$/"
    ];
    if (empty($preg_rule[$type])) {
        throw new \Exception(' cc_check_all type 不存在');
    }
    return preg_match($preg_rule[$type], $str) ? true : false;
}
