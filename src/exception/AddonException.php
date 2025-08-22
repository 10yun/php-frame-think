<?php

namespace shiyun\exception;

use shiyun\extend\ExceptionExtend;

/**
 * 插件异常处理类
 * @package think\addons
 */
class AddonException extends ExceptionExtend
{
    public function __construct($message, $code, $data = '')
    {
        $this->message  = $message;
        $this->code     = $code;
        $this->data     = $data;
    }
}
