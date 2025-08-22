<?php

declare(strict_types=1);

namespace shiyunWorker\annotation;

use Attribute;

/**
 * Json 进程
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class JsonRpcService {}
