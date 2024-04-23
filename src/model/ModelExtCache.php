<?php

namespace shiyun\model;

use think\Model;
use shiyun\model\exception\ModelCacheException;

/**
 * model数据加缓存
 */
class ModelExtCache
{
    use \shiyun\libs\TraitModeInstance;

    protected $cacheLevel = 1;
    protected $cacheGroup = '_cache_';
    protected $cacheTime = 60 * 60 * 24 * 3;
    protected $cacheObj = null;
    protected $instancesKey = null;
    protected $filePath = null;
    public function __construct()
    {
        $className = get_called_class();
        $reflector = new \ReflectionClass($className);
        $fileName = $reflector->getFileName();
        $this->filePath = $filePath = dirname($fileName);
        $this->instancesKey = md5($filePath);
    }
    /**
     * 缓存
     * @param $diyKey key
     * @param needField 需要的字段
     */
    public function sCacheGet(int $needId = null, array $needField = [])
    {
        if (empty($needId)) {
            $className = (new \ReflectionClass($this))->getShortName();
            throw new ModelCacheException(" {$className} needId 参数不能为空");
        }
        $modelRedis = ModelCacheRedis::getMapInstance($this->instancesKey);
        $modelRedis->setLevel($this->cacheLevel)
            ->setTime($this->cacheTime)
            ->setGroup($this->cacheGroup)
            ->setKey($needId);

        $cacheData = $modelRedis->getCache();
        // 不存在的时候，查找数据库，写入缓存
        if (empty($cacheData)) {
            $cacheData = $this->getCurrCacheData($needId);
            $modelRedis->setData($cacheData)->saveCache();
        }
        // 处理需要的字段
        $needCacheArr = [];
        if (!empty($needField)) {
            // foreach ($cacheData as $key => $val) {
            foreach ($needField as $need_name) {
                // if ($need_name === $key) {
                //     $needCacheArr[$key] =  $val;
                // }
                $needCacheArr[$need_name] = $cacheData[$need_name] ?? '';
            }
            // }
            return $needCacheArr;
        }
        return $cacheData;
    }
    /**
     * 设置缓存
     */
    public function sCacheSet(int $needId = null, array|string|int $data = null)
    {
        if (empty($needId)) {
            $className = (new \ReflectionClass($this))->getShortName();
            throw new ModelCacheException(" {$className} needId 参数不能为空");
        }
        if (empty($data)) {
            $className = (new \ReflectionClass($this))->getShortName();
            throw new ModelCacheException(" {$className} data 参数不能为空");
        }
        $modelRedis = ModelCacheRedis::getMapInstance($this->instancesKey);
        $modelRedis->setLevel($this->cacheLevel)
            ->setTime($this->cacheTime)
            ->setGroup($this->cacheGroup)
            ->setKey($needId)
            ->setData($data)
            ->saveCache();
    }
    /**
     * 刷新一条缓存
     */
    public function sCacheRefresh(int $needId = null)
    {
        if (empty($needId)) {
            $className = (new \ReflectionClass($this))->getShortName();
            throw new ModelCacheException(" {$className} needId 参数不能为空");
        }
        $modelRedis = ModelCacheRedis::getMapInstance($this->instancesKey);
        $modelRedis->setLevel($this->cacheLevel)
            ->setTime($this->cacheTime)
            ->setGroup($this->cacheGroup)
            ->setKey($needId);

        $cacheData = [];
        if (empty($cacheData)) {
            $cacheData = $this->getCurrCacheData($needId);
            $modelRedis->setData($cacheData)->saveCache();
        }
        return $cacheData;
    }
    protected function getCurrCacheData(int $needId = null)
    {
        throw new ModelCacheException("  请实现 getCurrCacheData");
        return [];
        $modelObj = loadAddonsCurrModel($this->filePath);
        if (!method_exists($modelObj, 'getCurrCacheData')) {
            $modelName = (new \ReflectionClass($modelObj))->getShortName();
        }
        $infoData = [];
        $infoData = $modelObj->getCurrCacheData($needId);
        if (!empty($infoData)) {
            if (is_object($infoData)) {
                $infoData = $infoData->toArray();
            }
        }
        return $infoData;
    }
}
