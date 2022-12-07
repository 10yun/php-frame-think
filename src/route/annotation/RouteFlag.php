<?php

declare(strict_types=1);

namespace shiyun\route\annotation;

use shiyun\route\annotation\common\RouteAbstract;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD
    | Attribute::TARGET_PARAMETER
    | Attribute::IS_REPEATABLE)]
class RouteFlag extends RouteAbstract
{
    protected array $attrMust = ['flag'];
    /**
     * @param string $flag 路由标识
     */
    public function __construct(
        public string $flag = ''
    ) {
        // 解析参数
        $this->paresArgs(func_get_args(), 'flag');
    }
}
