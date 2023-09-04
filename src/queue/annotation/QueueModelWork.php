<?php

namespace shiyunQueue\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class QueueModelWork
{
    public function __construct(
        string $type = '', // 驱动类型 
    ) {
    }
}
