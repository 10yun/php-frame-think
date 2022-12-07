<?php

declare(strict_types=1);

namespace shiyun\middleware;

use shiyun\support\Route;

/**
 * 路由 method 处理
 */
class CheckRestMiddle
{
    public function handle($request, \Closure $next)
    {
        $method = $request->method();

        // $dd = Route::getRuleList();
        // $dd2 = Route::getRule('sett_parts');
        // $dd3 = Route::getRuleName();
        // // $dd = Route::setRule('sett_');
        // $currItemRoute = current($dd2);
        // echo $currItemRoute->getName();
        // dd('---',  $dd3,);

        if ($method == 'GET') {
            // redirect('index/think');
            /*
			 * 是否为 GET 请求
			 */
        } else if ($method == 'PUT') {
            // 开启事务
            frameEventTrigger('dbStartTask');
        } else if ($method == 'PATCH') {
            // 开启事务
            frameEventTrigger('dbStartTask');
        } else if ($method == 'DELETE') {
            // 开启事务
            frameEventTrigger('dbStartTask');
        } else if ($method == 'POST') {
            // 开启事务
            frameEventTrigger('dbStartTask');
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
        // dd($method);

        // 添加中间件执行代码
        return $next($request);
    }
}
