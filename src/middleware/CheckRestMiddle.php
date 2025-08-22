<?php

declare(strict_types=1);

namespace shiyun\middleware;

use think\Request;

/**
 * 
 * RESTful 方法处理中间件
 * 处理HTTP方法转换
 * 路由 method 处理
 * @author 福州十云科技有限公司
 * @version 2108
 * @package shiyun\middleware
 */
class CheckRestMiddle
{
    /**
     * 允许通过POST转换的方法
     */
    protected $allowedPostTypes = ['delete', 'put', 'patch'];
    public function handle(Request $request, \Closure $next)
    {
        // 先处理请求方法转换
        if ($request->isPost()) {
            $this->convertPostMethod($request);
        }
        // 添加中间件执行代码
        return $next($request);
    }
    /**
     * 路由methods转换
     */
    protected function convertPostMethod(Request $request)
    {
        $postType = $request->param('postType', '');
        $postType = strtolower($postType);
        $postId = $request->param('id');
        // if (!empty($postType) && $this->isValidId($postId)) {
        if (!empty($postType) && !empty($postId)) {
            if (in_array($postType, $this->allowedPostTypes)) {
                $request->setMethod(strtoupper($postType));
            }
        }
    }
    /**
     * 验证ID格式
     */
    protected function isValidId($id): bool
    {
        // 根据业务需求调整验证规则
        return !empty($id) && (is_numeric($id) || preg_match('/^[a-f0-9]{24}$/i', $id));
    }
    /**
     * @param $method
     * @param string $action
     * @return array|void
     */
    // public function __invoke($method, $action = '')
    // {
    //     $app = $method ?: 'main';
    //     if ($action) {
    //         $app .= "__" . $action;
    //     }
    //     // 接口不存在
    //     if (!method_exists($this, $app)) {
    //         $msg = "404 not found (" . str_replace("__", "/", $app) . ").";
    //         return sendAjaxError($msg);
    //     }
    //     // 使用websocket请求
    //     $apiWebsocket = Request::header('Api-Websocket');
    //     if ($apiWebsocket) {
    //         $userid = User::userid();
    //         if ($userid > 0) {
    //             $url = 'http://127.0.0.1:' . env('LARAVELS_LISTEN_PORT') . Request::getRequestUri();
    //             $task = new IhttpTask($url, Request::post(), [
    //                 'Content-Type' => Request::header('Content-Type'),
    //                 'language' => Request::header('language'),
    //                 'token' => Request::header('token'),
    //             ]);
    //             $task->setApiWebsocket($apiWebsocket);
    //             $task->setApiUserid($userid);
    //             Task::deliver($task);
    //             return sendRespSucc('wait');
    //         }
    //     }
    //     // 正常请求
    //     $res = $this->__before($method, $action);
    //     if ($res === true || !__base_isError($res)) {
    //         return $this->$app();
    //     } else {
    //         return is_array($res) ? $res : sendAjaxError($res);
    //     }
    // }
}
