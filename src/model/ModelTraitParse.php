<?php

namespace shiyun\model;

trait ModelTraitParse
{
    // and fieldName = '%'
    public function whereEqual($fieldName = '', $value = null, $isIsset = false)
    {
    }
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
    protected function parseSqlWhere($d)
    {
    }
    protected function setWhere(array $wArr = [])
    {
        $this->whereArr = $wArr;
        return $this;
    }
    protected function orderBy($byStr)
    {
        if (empty($this->tablePrimary)) {
            return '';
        }
        $order_by = !empty($this->whereArr['orderby']) ? strtolower(trim($this->whereArr['orderby'])) : $this->tablePrimary;
        // $order_by = $this->tablePrimary;
        if ($order_by == 'rand') {
            return " ORDER BY RAND() ";
        }
        $order_sort = !empty($this->whereArr['ordersort']) ? strtoupper(trim($this->whereArr['ordersort'])) : 'DESC';
        $order_sort = in_array($order_sort, array(
            'DESC',
            'ASC'
        )) ? $order_sort : 'DESC';
        return " ORDER BY {$byStr} {$order_sort} ";
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
