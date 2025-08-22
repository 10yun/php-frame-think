<?php

namespace shiyun\exception;

use think\Response;
use shiyun\support\Db;
use Throwable;
use RuntimeException;

/**
 * API主动抛出异常
 *
 * @author ctocode
 * @since 2020-11-02
 * @version 2020-11-02
 */
class ApiException extends \think\Exception
{
    public function __construct($message = null, $code = 0, $data = null)
    {
        if (is_array($message) && isset($message['code'])) {
            $code = $message['code'];
            $data = $message['data'];
            $message = $message['message'];
        }
        $this->data = $data;
        parent::__construct($message ?? '操作失败', $code);
    }
    public static function response($request, Throwable $e): Response
    {
        $response = [
            'success' => false,
            'code' => $e->getCode() ?: 400,
            'message' => self::normalizeMessage($e->getMessage()),
        ];
        return json($response);
    }
    protected static function normalizeMessage(string $message): string
    {
        if (str_contains($message, "method not exists:")) {
            return '接口错误fn';
        }
        if (str_contains($message, "Route Not Found")) {
            return '接口错误route';
        }
        return $message;
    }
}
