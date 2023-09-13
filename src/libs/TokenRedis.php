<?php

namespace shiyun\libs;

use shiyun\extend\RedisCache;

/**
 * 生成临时token加缓存
 */
class TokenRedis extends RedisCache
{
    /**
     * 生成临时ken
     * 获取token
     */
    public function getToken()
    {
        $aesObj = new \shiyunUtils\libs\LibsSymmAES();
        $token = $aesObj->encrypt($this->cache_key);
        return $token;
    }
    /**
     * 验证 token
     */
    public function checkToken($token = '')
    {
        $aesObj = new \shiyunUtils\libs\LibsSymmAES();
        $this->cache_key = $aesObj->decrypt($token);
        return $this;
    }
}
