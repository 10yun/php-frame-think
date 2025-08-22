<?php

declare(strict_types=1);

use shiyun\ffi\ShiyunSO;
use think\Response;
// use think\facade\Request;
use shiyun\exception\ApiException;
use shiyun\exception\AuthException;

use shiyun\libs\ResponseCode;
use shiyun\support\Request;

function _parse_resp_data_int($data = [])
{
    if (is_array($data)) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if ($value === null) {
                    $data[$key] = '';
                } else {
                    $data[$key] = _parse_resp_data_int($value); // 递归再去执行
                }
            }
        } else {
            $data = '';
        }
    }
    if (is_int($data) && strlen((string)$data) > 16) {
        return (string)$data;
    }
    return $data;
}
/**
 * 返回一个结果
 * @param $send_data
 * @return Response
 */
function sendRespInfo($send_data = []): Response
{
    $currTime = time();
    $send_data['curr_time'] = $currTime;
    $send_data['curr_data'] = date('Y-m-d H:i:s', $currTime);
    if (empty($send_data['message'])) {
        $send_data['message'] = !empty($send_data['msg']) ? $send_data['msg'] : '';
    }
    if (empty($send_data['code'])) {
        $send_data['code'] = !empty($send_data['status']) ? $send_data['status'] : '';
    }
    if (!empty($send_data['data'])) {
        $send_data['data'] = _parse_resp_data_int($send_data['data']);
    }
    $send_data = cc_array_unset_null($send_data);
    if ($send_data['code'] == 200) {
        // echo json_encode($send_data, JSON_FORCE_OBJECT);
        // if (function_exists('fastcgi_finish_request')) {
        //     // 提高页面响应
        //     fastcgi_finish_request();
        // }
        return Response::create($send_data, 'json', 200);
    } else {
        return Response::create($send_data, 'json', 200);
    }
}
/**
 * 成功
 */
function sendRespSucc(string $msg = '操作成功', int $status = 200, mixed $data = []): Response
{
    return sendRespInfo([
        'status' => $status,
        'success' => true,
        'message' => $msg,
        'data' => $data,
        // 'ret' => -1,
        // 'msg' => ShiyunSO::translate($msg),
    ]);
}
/**
 * 错误
 */
function sendRespError(string $msg = '操作失败', int $code = 400, mixed $data = []): Response
{
    if (empty($msg)) {
        $msg = '操作失败';
    }
    throw new ApiException($msg, $code, $data);
}
/**
 * Ajax 错误返回
 * @param $msg
 * @param array $data
 * @param int $code
 * @param int $abortCode
 * @return array
 */
function sendAjaxError($msg, $data = [], $ret = 0, $abortCode = 404): Response|array
{
    // Request::header('Content-Type') === 'application/json'
    //     ? throw new ApiException($msg, $ret, $data)
    //     : throw new \think\exception\HttpException($abortCode, $msg);

    if (Request::header('Content-Type') === 'application/json') {
        // return sendRespError($msg, $ret, $data);
    } else {
        abort($abortCode, $msg);
    }
    return [];
}
/**
 * 响应码
 */
function sendRespCode200($code = 0, $isReturn = false): Response|array
{
    $codeObj = ResponseCode::getInstance();
    $msg = $codeObj->codeToMessage($code);

    $codeReturn = [
        'status' => $code,
        'data' => [],
        'message' => $msg
    ];
    if ($isReturn) {
        return $codeReturn;
    }
    return sendRespInfo($codeReturn);
}
function sendRespCode400(int $errorCode)
{
    $codeObj = ResponseCode::getInstance();
    $isCheckApi = !Request::isCheckHtml();

    $errorCode = $isCheckApi ? $errorCode : 100000;
    $errorMessage = $codeObj->codeToMessage($errorCode);
    throw new ApiException($errorMessage, $errorCode);
}
function sendRespCode401(int $errorCode)
{
    $codeObj = ResponseCode::getInstance();
    $isCheckApi = !Request::isCheckHtml();

    $errorCode = $isCheckApi ? $errorCode : 100000;
    $errorMessage = $codeObj->codeToMessage($errorCode);
    throw new AuthException($errorMessage, $errorCode);
}
function sendRespCode422(string $message, int $errorCode = 0)
{
    throw new \think\exception\ValidateException($message);
}
/**
 * get单条
 */
function sendRespGetItem($result_data = [], string $msg = '请求成功', int $status = 200): Response
{
    return sendRespInfo([
        'status' => $status,
        'success' => true,
        'message' => $msg,
        'data' => !empty($result_data['data'][0]) ? $result_data['data'][0] : array()
    ]);
}
/**
 * get多条
 */
function sendRespGetAll($wsql = [], $result_data = []): Response
{
    $result_data['total'] = !empty($result_data['total']) ? $result_data['total'] : 0;
    return sendRespInfo(array(
        'status' => 200,
        'success' => true,
        'message' => '请求成功',
        'page' => $wsql['page'],
        'pagecurr' => $wsql['page'],
        'pagesize' => $wsql['pagesize'],
        'pagemax' => is_numeric($wsql['pagesize']) ? ceil($result_data['total'] / $wsql['pagesize']) : 1,
        'total' => $result_data['total'],
        'data' => !empty($result_data['data']) ? $result_data['data'] : array()
    ));
}
function sendRespDump(): Response
{
    return sendRespInfo([
        'status' => 200,
        'success' => false,
        'message' => 'debug',
        'data' => func_get_args()
    ]);
}
