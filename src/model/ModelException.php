<?php

namespace shiyun\model;

class ModelException extends \think\Exception
{
    public function __construct($message = null, $code = 100020, $data = null, \Exception $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }
}
