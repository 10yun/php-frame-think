<?php

declare(strict_types=1);

namespace shiyun\middleware\safety;

/**
 * 安全监控 - 限流
 * 
 * 请求的url 加 token 写入缓存，判断请求次数
 * 同一接口同一人每分钟最多5次，……同一人同一时间最多请求10个借口
 * 同一【IP】
 * 同一【Token】
 * 同一【接口】 
 */
class SafetyRestrictMiddle
{
    // api多次无效访问
}
