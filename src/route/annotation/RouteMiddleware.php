<?php

declare(strict_types=1);

namespace shiyun\route\annotation;

use shiyun\route\annotation\common\RouteAbstract;
use Attribute;

/**
 * 路由
 * 注解中间件
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class RouteMiddleware extends RouteAbstract
{
    /**
     * 注解中间件
     * @param string|array $middlewares 路由中间件 支持多个中间件
     * @param array $only 指定需要走中间件的方法, 不指定则全部走中间件, 与except互斥 只支持在控制器中使用
     * @param array $except 指定不需要走中间件的方法, 不指定则全部走中间件, 与only互斥 只支持在控制器中使用
     */
    public function __construct(
        public string|array $middlewares,
        public array $only = [],
        public array $except = []
    ) {
        $this->paresArgs(func_get_args(), 'middlewares');
    }
}
