<?php

declare(strict_types=1);

namespace shiyun\route\annotation;

use shiyun\annotation\AnnotationAbstract;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RouteHead extends AnnotationAbstract
{
    protected string|array $methods = ['OPTIONS', 'HEAD'];
    public function __construct(
        public string|array $prefix = '',
    ) {
    }
}
