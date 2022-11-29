<?php

namespace shiyun\model;

use think\Model;

class ModelBase extends Model
{
    public static function onBeforeWrite(Model $model)
    {
        self::targetDbTaskStart();
    }

    public static function onBeforeUpdate(Model $model)
    {
        self::targetDbTaskStart();
        // $data = array_merge($model->getData(), [$model->updateTime => date('Y-m-d H:i:s')]);
        // $model->data($data);
    }

    public static function onBeforeInsert(Model $model)
    {
        self::targetDbTaskStart();
        // $data = array_merge($model->getData(), [$model->createTime => date('Y-m-d H:i:s')]);
        // $model->data($data);
    }

    public static function onBeforeRestore(Model $model)
    {
        self::targetDbTaskStart();
    }

    public static function onBeforeDelete(Model $model)
    {
        self::targetDbTaskStart();
    }

    private static function targetDbTaskStart()
    {
        event('dbStartTask');
    }
}
