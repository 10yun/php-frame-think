<?php

namespace shiyunOpensdk\middleware;

/**
 * 商家鉴权
 */
class SyRoleBizsMiddle
{
    /**
     * 前置
     */
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if ($currAppRole != 'org-business') {
            return sendRespError('角色类型错误~');
        }
        $currTokenRole = syOpenAccess('token_role');
        if (empty($currTokenRole)) {
            return sendRespError('角色类型错误~');
        }
        if ($currAppRole != $currTokenRole) {
            return sendRespError('角色类型错误~');
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
                'log_role' => '商家端',
                'log_method' => request()->method(),
                'log_remarks' => request()->pathinfo(),
                'log_optid' => syOpenAccess('account_id'),
                'log_name' => syOpenAccess('staff_name'),
            ]);
        }
    }
}
