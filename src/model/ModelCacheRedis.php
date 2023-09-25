<?php

namespace shiyun\model;

use shiyun\extend\RedisCache;

/**
 * model数据加缓存
 */
class ModelCacheRedis extends RedisCache
{
    use \shiyun\libs\TraitModeInstance;
}
