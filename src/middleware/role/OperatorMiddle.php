<?php

namespace shiyun\middleware\role;

/**
 * 平台鉴权
 */
class OperatorMiddle
{
    /**
     * 前置
     */
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if ($currAppRole != 'org-operator') {
            return sendRespError('角色类型错误~');
        }
        $currTokenRole = syOpenAccess('token_role');
        if (empty($currTokenRole)) {
            return sendRespError('角色类型错误~');
        }
        if ($currAppRole != $currTokenRole) {
            return sendRespError('角色类型错误~');
        }
        $request->role_type_xxxx = 'org-operator';
        // dd('-12321--');
        // $isSaas = gDoGetRoleTypeArr([
        //     'account_id' => syOpenAccess('account_id'),
        //     'business_mode' => 'org-operator',
        // ]);
        // // $isSaas = gDoGetRoleTypeArr([
        // //     'account_id' => syOpenAccess('account_id'),
        // //     'role_type_in' => 'sy-org-admin',
        // // ]);
        // // 如果是平台
        // $isSaas = gDoGetRoleTypeArr([
        //     'account_id' => syOpenAccess('account_id'),
        //     'business_mode' => 'org-operator',
        //     'role_type_in' => 'sy-org-admin',
        //     'role_type_in' => 'sy-org-admin,sy-org-staff',
        // ]);
        // if (empty($isSaas[0])) {
        //     return sendRespError('没有权限');
        // }
        // if (empty($isSaas[0])) {
        //     return sendRespError('权限不足');
        // }
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
                'business_id' => syOpenAccess('business_id'),
                'log_role' => '平台端',
                'log_method' => request()->method(),
                'log_remarks' => request()->pathinfo(),
                'log_optid' => syOpenAccess('account_id'),
                'log_name' => syOpenAccess('staff_name'),
            ]);
        }
    }
}
