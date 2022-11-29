<?php

namespace shiyun\model;

trait ModelTraitCheck
{
    /**
     * @action 【验证函数】--价格验证
     * @author ctocode-zhw
     * @version 2017-03-14
     */
    protected function checkPrice($value, $default = 0)
    {
        $data = !empty($default) ? $default : 0;
        $value = trim("{$value}");
        $value = is_numeric($value) ? $value : 0;
        $int_money = is_numeric($data);
        if (is_numeric($value)) {
            $int_money = ($value * 100);
            // $int_money = intval ( $int_money );
            $int_money = (float) ($int_money);
        }
        return $int_money;
    }
    /**
     * @action 【验证函数】--mysql【varchat】验证
     * @author ctocode-zhw
     * @version 2017-03-14
     */
    protected function checkVarchar($value = '', $default = '')
    {
        if (!empty($value)) {
            if (is_string($value)) {
                return trim($value);
            }
            return $value;
        } else {
            return '';
        }
    }
    protected function checkPicture($value = '', $default = '')
    {
        return $this->checkVarchar($value, $default);
    }
    /**
     * @action 【验证函数】--mysql【int】验证
     * @author ctocode-zhw
     * @version 2017-03-14
     */
    protected function checkInt($value = 0, $default = 0)
    {
        $data = !empty($default) ? $default : 0;
        $value = trim("{$value}");
        $return = is_numeric($value) ? (int) ($value) : 0;
        if (empty($return) && !empty($default)) {
            $return = $data;
        }
        return $return;
    }
    /**
     * @action 【验证函数】--mysql【date转int】验证
     * @author ctocode-zhw
     * @version 2017-03-14
     */
    protected function checkDate($value, $default = 0)
    {
        $data = !empty($default) ? $default : 0;
        if (is_numeric($value)) {
            $time = $value;
        } else {
            $value = trim("{$value}");
            $time = strtotime($value);
        }
        $return = $time ? $time : 0;
        if (empty($return) && !empty($default)) {
            $return = $data;
        }
        return $return;
    }
    protected function checkTime($value, $default = 0)
    {
        return $this->checkDate($value, $default);
    }
    //
    protected function checkStrIsComma($strs = null)
    {
        $str = str_replace("，", ",", $strs);
        $str = str_replace(",", ",", $str);
        if (strpos($str, ',') !== false) {
            return false;
        }
        return true;
        // if (preg_match ("/，/", "Welcome to ，hi-docs.com.")) {
        // echo "A match was found.";
        // } else {
        // echo "A match was not found.";
        // }
        // if (strstr($str, '，')) {
        // echo 'exist comma!'; //含有逗号
        // } else {
        // echo 'not exist comma!'; //不含逗号
        // }
    }
    /**
     * @action 判断必须字段是否存在
     * @version 2016-10-20
     * @author ctocode-zhw
     */
    protected function isFieldMust($arrs)
    {
        if (empty($arrs) || empty($this->_fields)) {
            return false;
        }
        foreach ($this->_fields as $key => $val) {
            if (!array_key_exists($key, $arrs) && $val['must'] == 1) { // 必须的字段不在传入的数组中
                return false;
            }
        }
        return true;
    }
    /**
     * @action 判断字段是否超出
     * @version 2016-10-20
     * @author ctocode-zhw
     */
    protected function isFieldOverflow($arrs)
    {
        if (empty($arrs) || empty($this->_fields)) {
            return false;
        }
        foreach ($arrs as $key => $val) {
            if (!array_key_exists($key, $this->_fields)) { // 传入的数组中 超出定义的表字段
                return false;
            }
        }
        return true;
    }
}
