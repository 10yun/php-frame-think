<?php

declare(strict_types=1);

namespace shiyun\route\annotation;

use shiyun\route\annotation\common\RouteAbstract;
use Attribute;

/**
 * rule路由
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class RouteRule extends RouteAbstract
{
    protected array $attrMust = ['path'];
    /**
     * @param string|array $path 路由路径 使用"/"开始则忽略控制器分组路径
     * @param string|array $methods 请求方法 例：GET 或 ['GET', 'POST']，默认为所有方法
     * @param string $name 路由名称 用于生成url的别名
     * @param array $params 路由参数
     * @param array $pattern 路由规则
     * @param array $append 路由追加参数
     * @param array $ext 路由后缀
     */
    public function __construct(
        public string|array $path = '',
        public string|array $methods = ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', '*'],
        public string       $name = '',
        public array        $params = [],
        public ?array       $pattern = null,
        public array        $append = [],
        public ?string      $ext = null,
        // public null|string|array $middleware = null,
        // public ?string           $deny_ext = null,
        // public ?bool             $https = null,
        // public ?string           $domain = null,
        // public ?bool             $complete_match = null,
        // public null|string|array $cache = null,
        // public ?bool             $ajax = null,
        // public ?bool             $pjax = null,
        // public ?bool             $json = null,
        // public ?array            $filter = null,
        // // 单独设置路由到特定组
        // public ?string           $setGroup = null,
    ) {
        // 解析参数
        $this->paresArgs(func_get_args(), 'path');
    }
}
