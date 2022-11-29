<?php

namespace shiyun\model;

use think\facade\Db;

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

        if ($this->_autoAnalys) {
            // 自动解析
            $anaObj = new ModelAnalysParse();
            return $anaObj->analysField($sql_result,  $this->_tableFields ?? []);
        } else {
            return $sql_result;
        }
    }
    public function insertItemData(array $requestData = [])
    {
        $pk = $this->tablePrimary;
        $time = time();
        // if (empty($requestData[$this->tablePrimary])) {
        //     $saveResult = $this->strict(false)
        //         ->insertGetId($requestData);
        //     $this->$pk  = $saveResult;
        // }
        $lastId = $this->exDb->strict(false)->insertGetId($requestData);
        if (isset($lastId)) {
            return [
                'status' => 200,
                'id' => $lastId,
                'msg' => '新增成功'
            ];
        }
    }
    public function mCrudDel(array $requestData = [])
    {
        if (empty($this->tableName) || empty($this->tablePrimary)) {
            return array(
                'status' => 404,
                'error' => 'C-m -' . get_class($this),
                'msg' => '表单配置错误'
            );
        }
        if (!empty($requestData['id'])) {
            $del_result = $this->strict(false)->where([
                $this->tablePrimary => $requestData['id']
            ])->delete();
        } else if (!empty($requestData['ids'])) {
            $del_result = $this->strict(false)->whereIn($this->tablePrimary,  $requestData['ids'])->delete();
        } else if (!empty($requestData[$this->tablePrimary])) {
            $del_result = $this->strict(false)->where([
                $this->tablePrimary => $requestData[$this->tablePrimary]
            ])->delete();
        }
        if (isset($del_result)) {
            return [
                'status' => 200,
                'msg' => '删除成功'
            ];
        }
    }
    public function insertOrUpdate(array $requestData = [], $in_or_up = false)
    {

        if (empty($this->tableName) || empty($this->tablePrimary)) {
            return array(
                'status' => 404,
                'error' => 'C-m -' . get_class($this),
                'msg' => '表单配置错误'
            );
        }
        if (empty($requestData) || !is_array($requestData) || empty($this->tablePrimary)) {
            return [
                'status' => '404',
                'error' => '主键、数组、参数',
                'msg' => '参数配置错误～'
            ];
        }
        // 主键不存在的时候，插入
        $_pk_id = null;
        $time = time();
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
        // dd('-----,----123', $saveResult, $this->tablePrimary, $this->$pk);
        if (isset($saveResult)) {
            return [
                'status' => 200,
                'id' => $_pk_id ?? 0,
                'msg' => '更新成功'
            ];
        }
    }
    /**
     * 新增或者更新
     */
    public function insertOrUpdate2($requestData = null, $in_or_up = false)
    {
        $parseResult = $this->parseRequestData($requestData);
        if ($parseResult['state'] != 'success') {
            return array(
                'status' => 404,
                'msg' => $parseResult['msg'] . '有误~'
            );
        }
        $parseData = array();
        $parseData = $parseResult['data'];
        if (!empty($parseData[$this->tablePrimary])) {
            if ($in_or_up) {
                // ->fetchSql ()

            } else {
                // ->fetchSql ()
                $saveResult = $this->where($this->tablePrimary, $parseData[$this->tablePrimary])
                    ->strict(false)
                    ->update($parseData);
            }
        } else {
        }
    }
    /**
     * @action 根据数据表模型解析form提交的数据
     * @version 2018-08-12 01:22
     * @author ctocode-zhw 
     * @return array 
     */
    public function parseRequestData($transmitData = null)
    {
        $requestData = array();
        if (!empty($transmitData)) {
            $requestData = $transmitData;
        } else {
            $requestData = request()->param();
        }
        // if($this->isFieldOverflow ( $requestData ) == false || $this->isFieldMust ( $requestData ) == false)
        // {
        // return false;
        // }
        $parseResult = array();
        $parseState = true;
        $parseMsg = '';
        foreach ($this->_tableFields as $key => $val) {
            if (in_array($key, array_keys($requestData))) {
                $field_check_func = 'check' . $val['type']; // 字段类型,验证函数
                $field_default = isset($val['default']) ? $val['default'] : null;
                $field_is_null = isset($val['null']) ? $val['null'] : null;
                // 当 未传递 $requestData[$key] 的时候
                if (!isset($requestData[$key])) {
                    if (!empty($requestData[$this->tablePrimary])) {
                        continue;
                    }
                    $form_val = '';
                } else {
                    // null 为传递值未定义的字段
                    if ($requestData[$key] !== null) {
                        $form_val = $requestData[$key]; // 传递值
                    }
                }
                // 验证方法
                if (method_exists($this, $field_check_func)) {
                    if (!empty($field_default)) {
                        $parseResult[$key] = $this->$field_check_func($form_val, $field_default);
                    } else {
                        $parseResult[$key] = $this->$field_check_func($form_val);
                    }
                } else { // 不符合的时候，默认值
                    $parseResult[$key] = $form_val;
                }
                if ((!empty($field_is_null) && $field_is_null == 'no') && empty($form_val)) {
                    $parseState = false;
                    $parseMsg = $val['comment'];
                    break;
                }
            }
        }
        if ($parseState === true) {
            return array(
                'state' => 'success',
                'data' => $parseResult
            );
        } else {
            return array(
                'state' => 'error',
                'msg' => $parseMsg
            );
        }
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
    public function getListData($wsql = [], $type = '')
    {
        $this->exWhere($wsql);
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
     * 删除
     * @param string $delType  默认 soft 软删除，可选 hard 硬删除
     * @return number[]|string[]|number[]|string[]
     */
    public function delete2($requestData = null, $delRemark = '软删除，作废了', $delType = 'hard')
    {
        if (empty($requestData)) {
            return array(
                'status' => 404,
                'msg' => '删除条件有误~'
            );
        }
        $tObj = Db::table($this->table);

        if (!empty($requestData['ids'])) {
            $ids = trim($requestData['ids']);
            $idsArr = explode(",", $ids);
            foreach ($idsArr as $key => $val) {
                if (!is_numeric($val)) {
                    unset($idsArr[$key]);
                }
            }
            $ids = implode(",", $idsArr);
            $tObj->whereIn($this->tablePrimary, $ids);
            $return_ids = $ids;
        } else if (!empty($requestData['id']) && is_numeric($requestData['id'])) {
            $tObj->where($this->tablePrimary, $requestData['id']);
            $return_ids = $requestData['id'];
        }
        if (is_array($requestData)) {
            $req_key_arr = array_keys($requestData);
            foreach ($this->_tableFields as $key => $val) {
                if (in_array($key, $req_key_arr)) {
                    if (empty($requestData[$key])) {
                        continue;
                    }
                    if (is_numeric($requestData[$key])) {
                        $tObj->where($key, $requestData[$key]);
                    } elseif (is_string($requestData[$key])) {

                        $tObj->where($key, $requestData[$key]);
                    } else {
                        $tObj->where($key, $requestData[$key]);
                    }
                }
            }
            $return_ids = '';
        }
        try {
            if ($delType == 'hard') {
                $sql_result = $tObj->delete();
            } elseif ($delType == 'soft') {
                $time = time();
                $sql_result = $tObj->update([
                    'del_time' => $time,
                    'del_remarks' => $delRemark
                ]);
            }
        } catch (\Exception $e) {
            return array(
                'status' => 500,
                'msg' => 'lqsym 操作错误',
                'msg2' => $e->getMessage(),
                'data' => ''
            );
        }
        if ($sql_result === FALSE) {
            return array(
                'status' => 404,
                'msg' => '删除信息有误~'
            );
        }
        return array(
            'status' => 200,
            'id' => $return_ids,
            'msg' => '删除信息成功~'
        );
    }
}
