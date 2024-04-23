<?php

use shiyun\support\Db;

/**
 * 数据库
 */
class DbExtend
{
    /**
     * 检查当前表是否存在
     * @return bool 返回检查结果，存在返回True，失败返回False
     */
    protected function checkTable($tableName)
    {
        return in_array($tableName, $this->getTables());
    }
    /**
     * 获取当前数据库的数据表列表
     * @return array 返回获取到的数据表列表数组
     */
    protected function getTables()
    {
        $tables = $data = array();
        $sth = Db::query("SELECT `name` FROM `sqlite_master` WHERE `type` = 'table' UNION ALL SELECT `name` FROM `sqlite_temp_master`");
        if (!empty($sth)) {
            while ($row = $sth->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $tables[] = $row[0];
            }
            unset($sth, $row);
        }
        return $tables;
    }
    /**
     * @action 解析表单sql语句，更新update，或插入insert
     * 返回重新编写好的sql语句
     * @author Name zhw Email 343196936@qq.com Data 2016年3月28日
     * @param string $table 数据表名
     * @param array $data 更新或插入的数据
     * @param string $where_sql 查询条件
     * @param $in_or_up  存在即更新。必须还有唯一主键
     * @return string 返回重新编写好的sql语句
     */
    function parseSQL($table = '', $data = array(), $where_sql = '', $in_or_up = false)
    {
        if ($table == '' || !is_array($data)) {
            return '';
        }
        if ($where_sql != '') {
            $field_update = '';
            foreach ($data as $k => $v) {
                $field_update .= ($field_update == '' ? '' : ',') . "`$k`='$v'";
            }
            $sql = "UPDATE $table SET $field_update WHERE $where_sql;";
        } else {
            $field_key = $field_val = '';
            foreach ($data as $k => $v) {
                $field_key .= ($field_key == '' ? '' : ',') . "`$k`";
                if (is_int($v)) {
                    $field_val .= ($field_val == '' ? '' : ',') . "$v";
                } else {
                    $field_val .= ($field_val == '' ? '' : ',') . "'$v'";
                }
            }
            if ($field_key != '' && $field_val != '') {
                $sql = "INSERT INTO $table ($field_key) VALUES ($field_val)";
            }

            if ($in_or_up == true) {
                $in_or_up_arr = array();
                foreach ($data as $k => $v) {
                    $in_or_up_arr[] = "`$k`='$v'";
                }
                $in_or_up_arr = join(",", $in_or_up_arr);
                $sql .= "   ON DUPLICATE KEY UPDATE " . $in_or_up_arr;
            }
        }
        return $sql;
    }
}
