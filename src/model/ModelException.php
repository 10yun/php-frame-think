<?php

namespace shiyun\model;

use think\exception\Handle;
use think\Response;
use Throwable;

class ModelException extends \think\Exception
{
    public function __construct($message = null, $code = 0, $data = null, \Exception $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }
    public static function response($request, Throwable $e): Response
    {
        $msg = $e->getMessage();
        $request_id = \app\common\lib\RequestId::create_guid();
        $return_data = [
            'request_id' => $request_id,
            'msg' => $msg
        ];
        return json($return_data);
    }
}
