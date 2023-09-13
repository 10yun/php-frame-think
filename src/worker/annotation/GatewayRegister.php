<?php

declare(strict_types=1);

namespace shiyunWorker\annotation;

use Attribute;
use \Workerman\Worker;
use \GatewayWorker\Register;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class GatewayRegister
{
    public function __construct(
        string $socket = ''
    ) {
        // register 服务必须是text协议
        $register = new Register($socket);
    }
}
