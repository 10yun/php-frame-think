<?php

namespace shiyun\middleware\role;

use app\common\RoleOrgLogs;

/**
 * 组织鉴权
 */
class OrgMiddle
{
    protected $log_role = '组织端';
    protected $orgArr = [
        'org-business' => '商家端',
        'org-saas' => '平台端',
        'org-paas' => 'paas端',
        'org-admin' => '超管端',
    ];
    /**
     * 前置
     */
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if (!in_array($currAppRole, array_keys($this->orgArr))) {
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
        $currAppRole = syOpenAppsAuth('syOpenAppRole');

        $log_role = $this->orgArr[$currAppRole];

        // frameLogsDebug('执行结束了');
        RoleOrgLogs::addEndLog($log_role);
    }
}
