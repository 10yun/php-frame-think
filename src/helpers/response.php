<?php

declare(strict_types=1);

use think\Response;
use shiyunUtils\helper\HelperArr;

/**
 * 返回一个结果
 * @param $send_data
 * @return Response
 */
function sendRespInfo($send_data = [])
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
    $send_data = HelperArr::unsetNull($send_data);

    if ($send_data['code'] == 200) {
        // echo json_encode($send_data, JSON_FORCE_OBJECT);
        // if (function_exists('fastcgi_finish_request')) {
        //     // 提高页面响应
        //     fastcgi_finish_request();
        // }
        return Response::create($send_data, 'json', 200)->send();
    } else {
        return Response::create($send_data, 'json', 200)->send();
    }
}

/**
 * 成功
 */
function sendRespSucc($msg = '操作成功~', $status = 200, $data = [])
{
    return sendRespInfo([
        'status' => $status,
        'success' => true,
        'msg' => $msg,
        'data' => $data
    ]);
}

/**
 * 错误
 */
function sendRespError($msg = null, $code = 404, $data = null): Response
{
    if ($msg == null) {
        $msg = '操作失败';
    }
    if (is_array($msg)) {
        $temp_msg = $msg;
        $msg = $temp_msg['msg'] ?? '操作失败';
        unset($temp_msg['msg']);
        $data = $temp_msg;
    }
    // throw new ApiException($msg, $code, $data);
    // throw new \app\common\exception\ApiException($msg, $code, $data);
    return sendRespInfo([
        'status' => $code,
        'success' => false,
        'error' => 'Not Found',
        'msg' => $msg
    ]);
}
/**
 * 响应码
 */
function sendRespCode200($code = 0, $isReturn = false): Response
{
    $codeObj = new \app\common\lib\ResponseCode();
    $codeArr = $codeObj->getCodeArr();
    $msg = $codeArr[$code] ?? '暂无该状态码信息';
    $codeReturn = [
        'status' => $code,
        'data' => [],
        'msg' => $msg
    ];
    if ($isReturn) {
        return $codeReturn;
    }
    return sendRespInfo($codeReturn);
}
function sendRespCode401($code = 0, $isReturn = false): Response
{
    $codeObj = new \app\common\lib\ResponseCode();
    $codeArr = $codeObj->getCodeArr();
    $msg = $codeArr[$code] ?? '暂无该状态码信息';
    $codeReturn = [
        'status' => $code,
        'data' => [],
        'error' => $msg,
        'msg' => $code,
    ];
    if ($isReturn) {
        return $codeReturn;
    }
    return Response::create($codeReturn, 'json', 401)->send();
}
/**
 * get单条
 */
function sendRespGetItem($result_data = [], $msg = '请求成功', $status = 200)
{
    return sendRespInfo([
        'status' => $status,
        'success' => true,
        'msg' => $msg,
        'data' =>  !empty($result_data['data'][0]) ? $result_data['data'][0] : array()
    ]);
}
/**
 * get多条
 */
function sendRespGetAll($wsql = [], $result_data = [])
{
    $result_data['total'] = !empty($result_data['total']) ? $result_data['total'] : 0;
    return sendRespInfo(array(
        'status' => 200,
        'success' => true,
        'msg' => '请求成功',
        'page' => $wsql['page'],
        'pagesize' => $wsql['pagesize'],
        'pagemax' => is_numeric($wsql['pagesize']) ? ceil($result_data['total'] / $wsql['pagesize']) : 1,
        'total' => $result_data['total'],
        'data' => !empty($result_data['data']) ? $result_data['data'] : array()
    ));
}
