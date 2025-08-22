<?php

namespace shiyunOpensdk\middleware;

use shiyun\exception\AuthException;

/**
 * 跑腿鉴权
 */
class SyRolePaotuiMiddle
{
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if ($currAppRole != 'general-user') {
            throw new AuthException('角色类型错误', 100206);
        }
        $currTokenRole = syOpenAccess('token_role');
        if (empty($currTokenRole)) {
            throw new AuthException('角色类型错误', 100206);
        }
        if ($currAppRole != $currTokenRole) {
            throw new AuthException('角色类型错误', 100206);
        }
        if ($currTokenRole != 'user-paotui') {
            throw new AuthException('角色类型错误', 100206);
        }
        // 添加中间件执行代码
        return $next($request);
    }
}
