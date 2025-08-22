<?php

namespace shiyun\exception;

use shiyun\extend\ExceptionExtend;

/**
 * 全局参数配置错误
 * @package think\addons
 */
class GlobalConfigException extends ExceptionExtend
{
    public function __construct(string $message, int $code = 400, $data = '')
    {
        $this->message  = $message;
        $this->code     = $code;
        $this->data     = $data;
    }
}
