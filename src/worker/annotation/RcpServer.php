<?php

declare(strict_types=1);

namespace shiyunWorker\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class RpcServer
{
    public function __construct(
        string $socket = ''
    ) {
    }
}
