<?php

namespace shiyun\exception;

use shiyun\extend\ExceptionExtend;

/**
 * 鉴权错误
 * @author 福州十云科技有限公司
 */
class AuthException extends ExceptionExtend
{
    public function __construct(string $message, int $code = 0, $data = '')
    {
        $this->statusCode = 401;
        $this->message  = $message;
        $this->code     = $code;
        if (empty($data)) {
            $this->data     = [
                'errorCode' => $code,
                'errorMessage' => $message,
            ];
        }
    }
}
