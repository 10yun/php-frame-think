<?php

namespace shiyun\model;

use shiyun\extend\RedisCache;

/**
 * model数据加缓存
 */
class ModelCacheRedis extends RedisCache
{
    protected static $instances = [];
    public static function getInstance($key = null)
    {
        if (empty($key)) {
            $key = get_called_class();
        }
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }
        return self::$instances[$key];
    }
}
