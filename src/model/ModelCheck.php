<?php

namespace shiyun\model;

use think\Model;
use think\facade\Db;

class ModelCheck extends Model
{
    protected $table = '';
    protected $pk = '';
    protected $check_exist_field = [];
    protected $errMsg = '';
    public function getError()
    {
        return $this->errMsg;
    }
    /**
     * 验证数据是否存在
     */
    public function hasExistOnlyData($params = [], $isPKCheck = false)
    {
        if (
            !empty($this->table)
            && !empty($this->pk)
            && !empty($this->check_exist_field)
        ) {
            $currRole = SyOpenAppsAuth('syOpenAppRole');
            $wsqlRole = [];
            if (!empty($currRole)) {
                if ($currRole == 'org-business') {
                    $wsqlRole['business_id'] = syOpenAccess('business_id');
                }
            }
            foreach ($this->check_exist_field as $field_key => $tip_msg) {
                $wsql = [];
                $wsql = $wsqlRole;
                if (!empty($params[$field_key])) {
                    $wsql[$field_key] = $params[$field_key];
                    if ($isPKCheck) {
                        $existInfo = $this->where($wsql)->where([
                            ["{$this->pk}", '<>', $params['id']],
                        ])->field("{$field_key}")->find();
                        //->fetchSql()
                    } else {
                        $existInfo = $this->where($wsql)->field("{$field_key}")->find();
                    }
                    if (!empty($existInfo[$field_key])) {
                        $this->errMsg = $tip_msg;
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
