<?php

namespace shiyunOpensdk\middleware;

use shiyun\exception\AuthException;

/**
 * 组织鉴权
 */
class SyRoleOrgMiddle
{
    protected $log_role = '组织端';
    protected $orgArr = [
        'org-business' => '商家端',
        'org-operator' => '平台端',
        'org-agent' => 'agent端',
        'org-group' => '集团端',
        'org-admin' => '超管端',
    ];
    /**
     * 前置
     */
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if (!in_array($currAppRole, array_keys($this->orgArr))) {
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
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        $log_role = $this->orgArr[$currAppRole];
        $log_method = request()->method();
        if ($log_method != 'GET') {
            queue_producer('queue_connect_redis', '', 'RoleOrgLogAdd', [
                'business_id' => syOpenAccess('business_id'),
                'log_role' => $log_role,
                'log_method' => request()->method(),
                'log_remarks' => request()->pathinfo(),
                'log_optid' => syOpenAccess('account_id'),
                'log_name' => syOpenAccess('staff_name'),
            ]);
        }
    }
}
