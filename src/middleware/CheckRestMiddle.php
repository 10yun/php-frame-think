<?php

declare(strict_types=1);

namespace shiyun\middleware;

use shiyun\support\Event;

/**
 * 路由 method 处理
 */
class CheckRestMiddle
{
    public function handle($request, \Closure $next)
    {
        $method = $request->method();

        if ($method == 'GET') {
            // redirect('index/think');
            /*
			 * 是否为 GET 请求
			 */
        } else if ($method == 'PUT') {
            // 开启事务
            Event::trigger('dbStartTask');
        } else if ($method == 'PATCH') {
            // 开启事务
            Event::trigger('dbStartTask');
        } else if ($method == 'DELETE') {
            // 开启事务
            Event::trigger('dbStartTask');
        } else if ($method == 'POST') {
            // 开启事务
            Event::trigger('dbStartTask');
            /**
             *  '路由methods 转换';
             */
            $postType = $request->param('postType');
            $postId = $request->param('id');

            if (!empty($postType) && !empty($postId)) {
                if ($postType == 'delete') {
                    $request->setMethod('DELETE');
                } else if ($postType == 'put') {
                    $request->setMethod('PUT');
                } else if ($postType == 'patch') {
                    $request->setMethod('PATCH');
                }
            }
        }

        // 添加中间件执行代码
        return $next($request);
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
