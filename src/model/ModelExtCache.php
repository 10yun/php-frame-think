<?php

namespace shiyun\model;

use think\Model;

/**
 * model数据加缓存
 */
class ModelExtCache extends Model
{
    protected static $instances = [];
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }
        return self::$instances[$class];
    }
}
