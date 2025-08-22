<?php

namespace shiyun\docs\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class DocsMdTable
{
    public function __construct(
        string $str = '', // 标题 
        string $desc = '', // 描述
        string $link = '', // 链接
    ) {}
}
