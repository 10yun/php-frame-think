<?php

namespace shiyun\model;

trait ModelTraitParse
{
    public function whereInFromArr($arr = [])
    {
        if (empty($arr) || !is_array($arr)) {
            return '';
        }
        $arr = array_unique($arr);
        $output = "'" . implode("','", $arr) . "'";
        return $output;
    }
    /**
     * 字符串转引号。如：  "xxx-aaa,xxx-bbb" 变成 'xxx-aaa','xxx-bbb'
     */
    public function whereInFromStr($str = '')
    {
        if (empty($str)) {
            return '';
        }
        $str = trim($str);
        if (str_contains($str, ",")) {
            // 使用explode()函数按逗号分割字符串  
            $parts = explode(',', $str);
            // 使用implode()函数将分割后的数组元素用带有引号的逗号重新组合  
            $output = "'" . implode("','", $parts) . "'";
            return $output;
        }
        return '';
    }
    // and fieldName = '%'
    public function whereEqual($fieldName = '', $value = null, $isIsset = false) {}
    public function whereStr($sql = '', $value = null, $isIsset = false)
    {
        if (empty($sql)) {
            return '';
        }
        if ($isIsset) {
            if (isset($value)) {
                return sprintf(" $sql ", $value ?? '');
            }
        } else {
            if (!empty($value)) {
                return sprintf(" $sql ", $value ?? '');
            }
        }
    }
    public function whereItem($key, $val = '') {}
    // 小于
    public function appendWhereLT($key, $type = '') {}
    // 小于等于
    public function appendWhereLE($key, $type = '') {}
    // 等于
    public function appendWhereEQ($key, $val = '', $check = 'empty') {}
    // 不等于
    public function appendWhereNE($key, $type = '') {}
    // 大于等于
    public function appendWhereGE($key, $type = '') {}
    // 大于
    public function appendWhereGT($key, $type = '') {}
    // OR
    public function appendWhereOR($key, $type = '') {}
    protected $whereArr = [];
    /**
     * @action SQL语句验证函数,防注入 防CC、至强抗DDoS 等
     * @author ctocode-zhw
     * @version 2017-03-14
     */
    protected function sqlVerify($sql = null)
    {
        return $sql;
    }
    protected function parseSqlWhere($d) {}
    protected function setWhere(array $wArr = [])
    {
        $this->whereArr = $wArr;
        return $this;
    }
    protected function parseSqlOrderBy($wsql = [])
    {
        if (!empty($wsql['orderBy'])) {
        }
    }
    // 分页
    protected function parseSqlLimit($wsql = [])
    {
        $page = !empty($wsql['page']) ? $wsql['page'] : 1;
        $page = is_numeric($page) ? intval($page) : 1;
        $pagesize = !empty($wsql['pagesize']) ? $wsql['pagesize'] : 10;
        $pagesize = $pagesize == 'all' ? $pagesize : intval($pagesize);
        return $pagesize == 'all' ? ' ' : ' LIMIT ' . ($page - 1) * $pagesize . ",{$pagesize} ";
    }
}
