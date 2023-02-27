<?php

namespace shiyun\middleware\role;

use shiyun\support\Db;

/**
 * iaas鉴权
 */
class IaasMiddle
{
    protected $log_role = 'Iaas端';
    /**
     * 前置
     */
    public function handle($request, \Closure $next)
    {
        $currAppRole = syOpenAppsAuth('syOpenAppRole');
        if ($currAppRole != 'org-iaas') {
            return sendRespError('角色类型错误A~');
        }
        $currTokenRole = syOpenAccess('token_role');
        if (empty($currTokenRole)) {
            // sendRespError('角色类型错误B~');
        }
        // if ($currAppRole != $currTokenRole) {
        //     return sendRespError('角色类型错误C~');
        // }
        return $next($request);
    }
    /**
     * 回调行为
     */
    public function end(\think\Response $response)
    {
        // frameLogs('logs_channel_debug', '执行结束了');
        $this->addEndLog();
    }
}
