<?php

declare(strict_types=1);

namespace shiyunWorker\annotation;

use Attribute;

/**
 * gateway 进程
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class GatewayGateway
{
    protected array $attrMust = ['path'];
    protected array $_defaultValues = ['methods' => ['OPTIONS', 'GET']];
    protected string|array $methods = ['OPTIONS', 'GET'];

    /**
     * @param string|array $path 路由路径 使用"/"开始则忽略控制器分组路径
     * 
     * @param string $name 路由名称 用于生成url的别名
     * @param array $params 路由参数
     * @param array $pattern 路由规则
     * @param array $append 路由追加参数
     * @param array $ext 路由后缀
     */
    public function __construct(
        public string|array $socket = '',
        public string       $name = '',
        public array        $params = [],
        public ?array       $pattern = null,
        public array        $append = [],
        public ?string      $ext = null,
    ) {}
}
