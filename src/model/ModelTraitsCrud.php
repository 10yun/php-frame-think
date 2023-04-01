<?php

namespace shiyun\model;

use shiyun\support\Db;
use shiyun\model\exception\ModelException;

trait ModelTraitsCrud
{
    public function sqlRead($sql = '')
    {
        if (empty($sql)) {
            return '';
        }
        if (empty($sql)) {
            return false;
        }
        $sql_result =  Db::query($sql);
        return $sql_result;
    }
    public function whereDelete($wsql = [])
    {
        return $this->destroy(function ($query) use ($wsql) {
            $query->where($wsql);
        });
    }
    /**
     * 存在即更新
     */
    public function insertOrUpdate(array $requestData = [])
    {
        $modelClass = get_class($this);
        if (empty($this->tableName)) {
            throw new ModelException("模型[ {$modelClass} ]配置错误：表名未配置 ");
        }
        if (empty($this->tablePrimary)) {
            throw new ModelException("模型[ {$modelClass} ]配置错误：主键未配置 ");
        }
        if (empty($requestData) || !is_array($requestData)) {
            throw new ModelException("模型[ {$modelClass} ]配置错误：参数错误 ");
        }
        // 主键不存在的时候，插入
        $_pk_id = null;
        if (empty($requestData[$this->tablePrimary])) {
            $saveResult = $this->strict(false)
                ->insertGetId($requestData);
            $_pk_id = $saveResult;
        } else {
            $isCunzai = $this->where([
                "{$this->tablePrimary}" => $requestData[$this->tablePrimary] ?? 0
            ])->find();
            if (empty($isCunzai)) {
                $saveResult = $this->strict(false)
                    ->duplicate([
                        "{$this->tablePrimary}" => $requestData[$this->tablePrimary] ?? 0
                    ])
                    ->insert($requestData);
            } else {
                $saveResult = $this->strict(false)
                    ->duplicate([
                        "{$this->tablePrimary}" => $requestData[$this->tablePrimary] ?? 0
                    ])
                    ->save($requestData);
            }
            $_pk_id  = $requestData[$this->tablePrimary];
            // dd($saveResult, $requestData);
        }
        // dd('-----,----123', $saveResult, $this->tablePrimary, $_pk_id);
        if (isset($saveResult)) {
            return true;
            return [
                'status' => 200,
                'id' => $_pk_id ?? 0,
                'msg' => '更新成功'
            ];
        } else {
            return false;
        }
    }
    public function getRowData($wsql = [])
    {
        return $this->getListData($wsql, 'row');
    }
    public function getListData($wsql = [], $type = '')
    {
        // $query =
        // 其他搜索条件
        if ($type == 'row') {
            $sql_result = $this->exDb->find($wsql[$this->pk] ?? 0);
            $data['data'] = $this->exParseData($sql_result, $type);
        } else {
            $sql_result = $this->exLimit($wsql)
                ->order($this->pk, 'desc')
                ->select();
            $data['data'] = $this->exParseData($sql_result, $type);
            $data['total'] = $this->exDb->count($this->pk);
        }
        return $data;
    }
    /**
     * 过滤字段
     */
    public function filterFieldSql($arr = [], $qianzhui = '')
    {
        $filterArr = [];
        $columnArr = Db::getFields($this->tabConnect);
        $columnKey =  array_keys($columnArr);
        if (!empty($arr)) {
            if (is_string($arr)) {
                $filterArr[] = $arr;
            } else {
                $filterArr = $arr;
            }
            foreach ($filterArr as $key => $val) {
                foreach ($columnKey as $k2 => $v2) {
                    if ($v2 == $val) unset($columnKey[$k2]);
                }
            }
        }
        $last_sql = "";
        $last_sql = $qianzhui . implode(",{$qianzhui}", $columnKey);
        return $last_sql;
    }
    // 解析列表
    protected function exParseData($data = null, $type = '')
    {
        $parse_data = array();
        foreach ($data as $key => $val) {
            $parse_data[$key] = (array) $val->getData();
            ksort($parse_data[$key]);
        }
        return $parse_data;
    }
    protected function exLimit($wsql = [])
    {
        $page = !empty($wsql['page']) ? $wsql['page'] : 1;
        $page = is_numeric($page) ? intval($page) : 1;
        $pagesize = !empty($wsql['pagesize']) ? $wsql['pagesize'] : 10;
        $pagesize = $pagesize == 'all' ? $pagesize : intval($pagesize);
        $last = [
            'page' => ($page - 1) * $pagesize,
            'pagesize' => $pagesize
        ];
        $this->exDb->limit($last['page'], $last['pagesize']);
        return $this->exDb;
    }
}
