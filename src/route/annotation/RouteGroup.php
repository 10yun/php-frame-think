<?php

declare(strict_types=1);

namespace shiyun\route\annotation;

use shiyun\route\annotation\common\RouteAbstract;
use Attribute;

/**
 * 路由分组
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RouteGroup extends RouteAbstract
{
    protected array $attrMust = ['prefix'];
    protected string|array $methods = "*";
    /**
     * @param string|array $prefix 路由分组路径
     */
    public function __construct(
        public string|array $prefix = '',

        // public ?string           $name = null,
        // public null|string|array $middleware = null,
        // // ==== 通用参数 ====
        // public ?string           $ext = null,
        // public ?string           $deny_ext = null,
        // public ?bool             $https = null,
        // public ?string           $domain = null,
        // public ?bool             $complete_match = null,
        // public null|string|array $cache = null,
        // public ?bool             $ajax = null,
        // public ?bool             $pjax = null,
        // public ?bool             $json = null,
        // public ?array            $filter = null,
        // public ?array            $append = null,
        // public ?array            $pattern = null,
        // // ==== 特殊参数 ====
        // public int               $registerSort = 1000,

    ) {
        // 解析参数
        $this->paresArgs(func_get_args(), 'prefix');
    }
}
