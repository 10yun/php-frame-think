<?php

namespace shiyun\middleware\role;

/**
 * 跑腿鉴权
 */
class PaotuiMiddle
{
    public function handle($request, \Closure $next)
    {
        if (syOpenAccess('token_type') != 'user-paotui') {
            return sendRespError('权限不足');
        }
        // 添加中间件执行代码
        return $next($request);
    }
}
