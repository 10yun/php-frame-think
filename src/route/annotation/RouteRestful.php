<?php

declare(strict_types=1);

namespace shiyun\route\annotation;

use shiyun\annotation\AnnotationAbstract;
use Attribute;

/**
 * 路由
 * 注解资源路由
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class RouteRestful extends AnnotationAbstract
{
    protected array $attrMust = ['path'];

    public function __construct(
        public string|array $path = '',
        public string|array $methods = ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', '*'],
        public string       $name = '',
        public array        $params = [],
        // 定义资源变量名
        // public ?array            $vars = null,
        // // 仅允许特定操作
        // public ?array            $only = null,
        // // 排除特定操作
        // public ?array            $except = null,
        // // ==== 通用参数 ====
        // public ?string           $ext = null,
        // public ?string           $deny_ext = null,
        // public ?bool             $https = null,
        // public ?string           $domain = null,
        // public ?bool             $completeMatch = null,
        // public null|string|array $cache = null,
        // public ?bool             $ajax = null,
        // public ?bool             $pjax = null,
        // public ?bool             $json = null,
        // public ?array            $filter = null,
        // public ?array            $append = null,
        // public ?array            $pattern = null,
    ) {
        // 解析参数
        $this->paresArgs(func_get_args(), 'path');
    }
}
