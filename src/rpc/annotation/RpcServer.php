<?php

declare(strict_types=1);

namespace shiyunRpc\annotation;

use Attribute;

/**
 * RPC 服务端
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class RpcServer
{
    public function __construct(
        string $socket = ''
    ) {
    }
}
