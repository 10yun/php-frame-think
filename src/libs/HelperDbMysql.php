<?php

namespace shiyun\libs;

class HelperDbMysql
{
    /**
     * 获取 sql 语句里面的 table 表名
     * @param string $sqlString
     * @return mixed|string
     */
    public function getSqlTableName($sqlString = '')
    {
        $sqlString = str_replace('`', '', trim($sqlString)) . ' AND 1=1 ';
        $key = strtolower(substr($sqlString, 0, 6));
        if ($key === 'select') {
            $tmp = explode('where', strtolower(trim($sqlString)));
            $tmp = explode('from', $tmp[0]);
            if (strpos($tmp[1], ',') !== false && !stristr($tmp[1], 'select')) {
                $tmp = explode(',', $tmp[1]);
                foreach ($tmp as $v) {
                    $v = trim($v);
                    if (strpos($v, ' ') !== false) {
                        $tv = explode(' ', $v);
                        $return[] = $tv[0];
                    }
                }
                return $return;
            } else {
                $expression = '/((SELECT.+?FROM)|(LEFT\\s+JOIN|JOIN|LEFT))[\\s`]+?(\\w+)[\\s`]+?/is';
            }
        } else if ($key === 'delete') {
            $expression = '/DELETE\\s+?FROM[\\s`]+?(\\w+)[\\s`]+?/is';
        } else if ($key === 'insert') {
            $expression = '/INSERT\\s+?INTO[\\s`]+?(\\w+)[\\s`]+?/is';
        } else if ($key === 'update') {
            $tmp = explode('set', strtolower(str_replace('`', '', trim($sqlString))));
            $tmp = explode('update', $tmp[0]);
            if (strpos($tmp[1], ',') !== false && !stristr($tmp[1], 'update')) {
                $tmp = explode(',', $tmp[1]);
                foreach ($tmp as $v) {
                    $v = trim($v);
                    if (strpos($v, ' ') !== false) {
                        $tv = explode(' ', $v);
                        $return[] = $tv[0];
                    }
                }
                return $return;
            } else {
                $expression = '/UPDATE[\\s`]+?(\\w+)[\\s`]+?/is';
            }
        }
        $matches = [];
        preg_match_all($expression, $sqlString, $matches);
        return array_unique(array_pop($matches));
    }
    /**
     * @action 获取数据表的字段
     * @author ctocode-zhw
     * @version 2017-07-19
     * @param string $table_name
     * @return string|string[][]
     */
    public function getTableColumn($table_name = '')
    {
        if (empty($table_name)) {
            return '';
        }
        // 表单是否存在
        $is_table = \shiyun\support\Db::query("SHOW TABLES LIKE '{$table_name}'; ");
        if (!empty($is_table[0])) { // 字段是否存在
            $field_array = \shiyun\support\Db::query("DESCRIBE {$table_name} ;");
            $lists = array();
            foreach ($field_array as $val) {
                $rows = array();
                $rows['field'] = $val['Field'];
                if (strpos($val['Type'], 'int') !== FALSE) {
                    $rows['type'] = 'int';
                } elseif (strpos($val['Type'], 'varchar') !== FALSE) {
                    $rows['type'] = 'varchar';
                } elseif (strpos($val['Type'], 'text') !== FALSE) {
                    $rows['type'] = 'text';
                }
                $lists[] = $rows;
            }
            return $lists;
        }
        return '';
    }
}
