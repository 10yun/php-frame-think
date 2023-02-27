<?php

namespace shiyun\middleware\role;

/**
 * 超管鉴权
 */
class AdminMiddle
{
    /**
     * 前置
     */
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if ($currAppRole != 'org-admin') {
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
        // frameLogs('logs_channel_debug', '执行结束了');
        event('syRoleOrgLogs', [
            'type' => '超管端'
        ]);
    }
}
