<?php

namespace shiyun\middleware;

/**
 * 权限鉴权
 */
class SyAuthRbacMiddle
{
    public function handle($request, \Closure $next)
    {
        // 1、获取前端传递的权限标识位
        // 2、判断标识位是否为空
        // 3、判断 是否拥有改标识位
        // 
        $url_rule = $request->root() . '/' . $request->pathinfo();
        dd($request->root(), $url_rule);
        $response = $next($request);

        // 添加中间件执行代码
    }
}
