<?php

namespace shiyun\middleware\role;

/**
 * 平台鉴权
 */
class SaasMiddle
{
    /**
     * 前置
     */
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if ($currAppRole != 'org-saas') {
            return sendRespError('角色类型错误~');
        }
        $currTokenRole = syOpenAccess('token_role');
        if (empty($currTokenRole)) {
            return sendRespError('角色类型错误~');
        }
        if ($currAppRole != $currTokenRole) {
            return sendRespError('角色类型错误~');
        }
        $request->role_type_xxxx = 'org-saas';
        // dd('-12321--');
        // $isSaas = gDoGetRoleTypeArr([
        //     'account_id' => syOpenAccess('account_id'),
        //     'business_mode' => 'org-saas',
        // ]);
        // // $isSaas = gDoGetRoleTypeArr([
        // //     'account_id' => syOpenAccess('account_id'),
        // //     'role_type_in' => 'sy-org-admin',
        // // ]);
        // // 如果是平台
        // $isSaas = gDoGetRoleTypeArr([
        //     'account_id' => syOpenAccess('account_id'),
        //     'business_mode' => 'org-saas',
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
        // frameLogs('logs_channel_debug', '执行结束了');
        event('syRoleOrgLogs', [
            'type' => '平台端'
        ]);
    }
}
