<?php

namespace shiyunOpensdk\middleware;

/**
 * 用户鉴权
 */
class SyRoleUserMiddle
{
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if ($currAppRole != 'general-user') {
            return sendRespError('用户角色类型错误~');
        }
        $currTokenRole = syOpenAccess('token_role');
        if (empty($currTokenRole)) {
            return sendRespError('用户角色类型错误~');
        }
        if ($currAppRole != $currTokenRole) {
            return sendRespError('用户角色类型错误~');
        }
        return $next($request);
    }
    // 删除
    protected function userCrudAll()
    {
        // if (syOpenAppsAuth('syOpenAppId') !== 'console-user') {
        //     return sendRespError('syOpenAppId 错误~');
        // }
        $request_data = request()->param();
        $request_data['account_id'] = syOpenAccess('account_id');
        $request_data['syOpenAppId'] = syOpenAppsAuth('syOpenAppId');
        $request_data['syOpenAppProject'] = syOpenAppsAuth('syOpenAppProject');
        $request_data['business_id'] = ctoRequest('business_id', 'int');
    }
}
