<?php

declare(strict_types=1);

namespace shiyun\support;

use think\Facade;

/**
 * @see \think\Session
 * @package think\facade
 * @mixin \think\Session
 * @method static mixed getConfig(null|string $name = null, mixed $default = null) 获取Session配置
 * @method static string|null getDefaultDriver() 默认驱动
 */
class Session extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'session';
    }
}
