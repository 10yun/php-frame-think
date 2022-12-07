<?php

namespace shiyun\middleware;

/**
 * 角色鉴权
 */
class SyAuthRoleMiddle
{
    public function handle($request, \Closure $next)
    {
        $url_rule = $request->root() . '/' . $request->pathinfo();
        dd($request->root(), $url_rule);
        $response = $next($request);

        // 添加中间件执行代码
        return $response;
    }
}
