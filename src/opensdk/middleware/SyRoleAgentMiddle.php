<?php

namespace shiyunOpensdk\middleware;

use shiyun\exception\AuthException;

/**
 * agent鉴权
 */
class SyRoleAgentMiddle
{
    /**
     * 前置
     */
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if ($currAppRole != 'org-agent') {
            throw new AuthException('角色类型错误', 100206);
        }
        $currTokenRole = syOpenAccess('token_role');
        if (empty($currTokenRole)) {
            throw new AuthException('角色类型错误', 100206);
        }
        if ($currAppRole != $currTokenRole) {
            throw new AuthException('角色类型错误', 100206);
        }
        return $next($request);
    }
    /**
     * 回调行为
     */
    public function end(\think\Response $response)
    {
        $log_method = request()->method();
        if ($log_method != 'GET') {
            queue_producer('queue_connect_redis', '', 'RoleOrgLogAdd', [
                'business_id' =>  syOpenAccess('business_id'),
                'log_role' => 'agent端',
                'log_method' => request()->method(),
                'log_remarks' => request()->pathinfo(),
                'log_optid' => syOpenAccess('account_id'),
                'log_name' => syOpenAccess('staff_name'),
            ]);
        }
    }
}
