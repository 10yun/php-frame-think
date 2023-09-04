<?php

namespace shiyun\exception;

use Exception;
use RuntimeException;

/**
 * 插件异常处理类
 * @package shiyun\exception
 */
class AddonsLoadException extends \Exception
{
    /**
     * 保存异常页面显示的额外Debug数据
     */
    protected array $data = [];
    protected string $error;

    public function __construct(string $message = '', int $code = 404, mixed $data = '')
    {
        $this->message  = $message;
        $this->message = is_array($message) ? implode(PHP_EOL, $message) : $message;
        $this->data     = $data;
        $this->error   = $message;
        $this->code     = $code;
        $this->code     = 404;
    }
}
