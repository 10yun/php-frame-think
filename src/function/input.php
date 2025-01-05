<?php

/**
 * 【ctocode】      常用函数 - input相关处理 , value相关处理
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
 * @action 获取输入参数 自动判断get或者post,支持过滤和默认值
 *  验证 接受表单的name 的值是否符合sql字段中的类型，
 *  自动赋予默认值
 *  重新封装tp I方法，并且完善
 * @使用方法:
 * <code>
 * 	checkRequest('form_name','string'); 获取id参数
 * 	checkRequest('form_name','int'); 获取id参数
 * 	checkRequest('name',''); 获取$_POST['name']
 * </code>
 * @version 2016-10-08
 * @author ctocode-zhw
 * @copyright ctocode 
 * @param string $name 字段表单name,变量的名称 支持指定类型
 * @param mixed $type 字段类型
 * @param mixed $default 你希望返回的默认的值
 * @return mixed
 */
function ctoRequest($name, $type = 'string', $default = '')
{
    $param = $_REQUEST;
    if (isset($param[$name]) && is_array($param[$name])) {
        if (isset($param[$name])) {
            $return_val = $param[$name];
        } else {
            return null;
        }
    } else {
        $return_val = isset($param[$name]) ? $_REQUEST[$name] : '';
    }
    // if(empty ( $return_val ) && ! isset ( $return_val )){
    if (empty($return_val)) {
        $input = @file_get_contents('php://input');
        $input = json_decode($input, 1);
        $return_val = isset($input[$name]) ? $input[$name] : '';
    }
    if (!empty($type)) {
        $return_val = ctoValueCheck($return_val, $type, $default);
    }
    return $return_val;
}
// 基础 值 验证函数 2016-10-10
function ctoValueCheck($value, $type = 'string', $default = null)
{
    switch ($type) {
        case 'int':
            $data = !empty($default) ? $default : 0;
            $value = trim("{$value}");
            $return = is_numeric($value) ? intval($value) : 0;
            if (empty($return) && !empty($default)) {
                $return = $data;
            }
            break;
        case 'string_code': // 允许包含 代码，目前只支持 js、php、html
            break;
        case 'string_js': // 允许包含 js 代码
            break;
        case 'string_chsDash':
            // 只保留字母、数字、下划线、破折号、@
            $data = !empty($default) ? $default : '';
            $value = trim("{$value}");
            $return = ctoSecurityChsDash($value);
            break;
        case 'string':
            // addslashes 转义字符,默认开启
            // removeXss 过滤xss攻击，默认开启
            $data = !empty($default) ? $default : '';
            $value = trim("{$value}");
            // 过滤转义字符
            $value = ctoSecurityAddslashes($value);
            // 过滤XSS
            $value = ctoSecurityRemoveXss($value);
            $return = !empty($value) ? $value : $data;
            break;
        case 'date':
            $data = isset($default) ? $default : time();
            $value = strtotime($value);
            $return = $value ? $value : $data;
            break;
        case 'float':
            $return = is_float($value) ? $value : 0;
            break;
        case 'double':
            $return = is_double($value) ? $value : 0;
            break;
        case 'arr':
        case 'array':
            $return = $value;
            if (is_array($value)) {
                // 如果是数组的话，要递归处理
            }
            break;
        case 'null':
            $return = $value;
            break;
        case 'price':
            $return = $value;
            break;
        default:
            $return = ctoSecurityAddslashes($value);
            break;
    }
    return $return;
}
