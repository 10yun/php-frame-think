<?php

declare(strict_types=1);

namespace shiyun\route\annotation;

use shiyun\route\annotation\common\RouteAbstract;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RouteHead extends RouteAbstract
{
    protected string|array $methods = ['OPTIONS', 'HEAD'];
    public function __construct(
        public string|array $prefix = '',
    ) {
    }
}
