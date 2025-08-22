<?php

namespace shiyun\extend;

use shiyun\support\Cache;

class RedisCache
{
    protected $redis_store = 'CACHE_STORES_RD2';
    protected $redis_handle = null;


    protected $cache_data;
    protected $cache_key;
    protected $cache_token = '';
    // 
    protected $key_level = 0;
    // tag
    protected $key_tags = '';
    // 分组
    protected $key_group = '';
    // 最后一位key
    protected $key_flag = '';
    protected $key_time;

    public function __construct()
    {
        $this->redis_handle = Cache::store($this->redis_store)->handler();
    }
    /**
     * 设置驱动
     */
    public function setStore($store = '')
    {
        $this->redis_store = $store;
        return $this;
    }
    /**
     * 设置等级
     */
    public function setLevel($level = 0)
    {
        $this->key_level =  $level;
        return $this;
    }
    /**
     * 设置组别
     */
    public function setGroup($group)
    {
        $this->key_group = $group;
        return $this;
    }
    /**
     * 设置最终key
     * @param $key [为空随机生成 key]
     */
    public function setKey($key = '')
    {
        if (empty($key)) {
            $temp_key = cc_random_letter(12);
            $this->key_flag = $temp_key . time();
        } else {
            $this->key_flag = $key;
        }
        return $this;
    }
    /**
     * 设置时效
     * @param $time [ $time 为空时，默认0 ]
     */
    public function setTime($time = null)
    {
        if (!empty($time)) {
            $this->key_time = $time;
        }
        return $this;
    }
    /**
     * 设置缓存数据
     * @param $data 
     */
    public function setData($data = null)
    {
        $last_data = $data;
        if (!empty($data)) {

            if (is_array($data)) {
                if (cc_array_is_dimension($data)) {
                    $data['__cache_date__'] = date('Y-m-d H:i:s', time());
                }
                ksort($data);
                $last_data = json_encode($data, true);
            } else if (is_string($data) && json_validate($data)) {
                $data = json_decode($data, true);
                if (cc_array_is_dimension($data)) {
                    $data['__cache_date__'] = date('Y-m-d H:i:s', time());
                }
                ksort($data);
                $last_data = json_encode($data, true);
            }
        }
        $this->cache_data = $last_data;
        return $this;
    }
    /**
     * 缓存-保存
     */
    public function saveCache($data = null)
    {
        if (!empty($data)) {
            $this->setData($data);
        }
        $this->parseCacheKey();

        $this->redis_handle->set(
            $this->cache_key,
            $this->cache_data,
            $this->key_time
        );
        return $this;
    }
    /**
     * 缓存-获取
     */
    public function getCache($isParseKey = true)
    {
        if ($isParseKey) {
            $this->parseCacheKey();
        }
        $cache = $this->redis_handle->get($this->cache_key);
        if (json_validate($cache)) {
            $cache = json_decode($cache, true);
        }
        return $cache;
    }
    /**
     * 缓存-清空
     */
    public function clearCache()
    {
        $this->parseCacheKey();
        $tagArr = $this->redis_handle->keys("{$this->key_tags}:*");
        $this->redis_handle->del($tagArr);
    }
    /**
     * 缓存-删除
     */
    public function delCache()
    {
        $this->parseCacheKey();
        $this->redis_handle->del($this->cache_key);
    }

    protected function parseCacheKey()
    {
        $keyTagArr = [];
        /**
         * 生成【项目】下的token
         */
        if ($this->key_level > 0) {
            $key_project = syOpenAppsAuth('syOpenAppProject');
            $keyTagArr[] = $key_project;
        }
        /**
         * 生成【项目 + 应用】的token
         */
        if ($this->key_level > 1) {
            $key_appid = syOpenAppsAuth('syOpenAppId');
            $keyTagArr[] = $key_appid;
        }
        /**
         * 生成【项目 + 应用 + 商家】的token
         */
        if ($this->key_level > 2) {
            $bid_id = syOpenAccess('business_id');
            $keyTagArr[] = $bid_id;
        }
        if (!empty($this->key_group)) {
            $keyTagArr[] = $this->key_group;
        }
        if (!empty($keyTagArr)) {
            $this->key_tags = implode(":", $keyTagArr);
        }
        $this->cache_key = $this->key_tags;
        if (!empty($this->key_flag)) {
            $this->cache_key = $this->key_tags . ':' . $this->key_flag;
        }
    }
}
