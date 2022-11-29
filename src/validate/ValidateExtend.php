<?php

namespace shiyun\validate;

use think\Validate;

/**
 * 
 * 
 * 对原有的 Validate 验证器进行扩展
 * @author ctocode-zhw
 * @return \think\Validate 
 */
class ValidateExtend extends Validate
{
    // 要求：可以包含数字、字母、下划线，并且要同时含有数字和字母，且长度要在8-16位之间
    // preg_match ( "^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z_]{6,20}$", $pwd1 );

    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->rule //追加ID
    //     $this->message  //追加ID
    // }
    /**
     * 扩展设置场景，在需要的时候才配置场景
     * @param string $only_sign 场景标识
     * @param array $only_field 场景验证字段
     * @return \shiyun\validate\ValidateExtend
     */
    public function setScene($only_sign = '', $only_field = [])
    {
        try {
            if (empty($only_sign) || empty($only_field)) {
                return $this;
            }
            if (!is_array($only_field)) {
                return $this;
            }
            $this->scene[$only_sign] = $only_field;
        } catch (\Exception $e) {
        }
        return $this;
    }
    /**
     * 自定义验证规则：字母+下划线+数字，不以数字开头
     */
    protected function checkAlphaDash2($value, $rule, $data = [])
    {
        if (!empty($value)) {
            if (!preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/", $value)) {
                // return false;
                return '只能包含：字母、数字、下划线，且字母开头';
            }
        } else {
            return false;
            return '不能为空';
        }
        return true;
    }

    /**
     * 自定义验证规则：数组是否为空
     */
    protected function checkArrNull($value, $rule, $data = [])
    {
        if (!empty($value)) {
            $value = array_filter($value);
            if (empty($value)) {
                return false;
                return '数组为空';
            }
        } else {
            return false;
            return '数组为空';
        }
        return true;
    }
    /**
     * 存在这个字段就需要验证，目前无法处理
     */
    protected function checkExistRequire($value, $rule, $data = [])
    {
        return true;
        return '存在字段就必填';
        if (!empty($value)) {
            $value = array_filter($value);
            if (empty($value)) {
                return false;
                return '存在字段就必填';
            }
        } else {
            return false;
            return '存在字段就必填';
        }
        return true;
    }
}
