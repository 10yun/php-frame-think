<?php

namespace shiyun\middleware;

use shiyun\support\Db;
use shiyun\support\Cache;
use shiyun\support\Request;

/**
 * ========== 中间件 ==========
 * 十云开放平台 apps 应用鉴权
 * ========== #### ==========
 */
class SyAuthAppMiddle
{
    /**
     * @param \think\Request $request
     * @param \Closure $next
     *            return void
     */
    public function handle($request, \Closure $next)
    {
        $isCheckApi = Request::isCheckApi();

        $OpenAppAuthObj = new \shiyun\connection\OpenAppAuth();
        $OpenAppAuthObj->initAuthData();
        $authAppData = $OpenAppAuthObj->getAuthData();
        /**
         *  接收 appid + appkey
         *  验证 appsecret 
         */
        /**
         * 判断：项目
         */
        if (empty($authAppData['syOpenAppProject'])) {
            return sendRespCode401($isCheckApi ? '100105' : '100000');
        }
        /**
         * 判断：appID
         */
        if (empty($authAppData['syOpenAppId'])) {
            return sendRespCode401($isCheckApi ? '100106' : '100000');
        }
        /**
         * 过滤 $syOpenAppId
         */
        $pass_appsid = syGetAppsArr();
        if (!in_array($authAppData['syOpenAppId'], $pass_appsid)) {
            return sendRespCode401($isCheckApi ? '100106' : '100000');
        }
        /**
         * appKey
         */
        if (empty($authAppData['syOpenAppKey'])) {
            return sendRespCode401($isCheckApi ? '100107' : '100000');
        }
        /**
         * 判断：角色
         */
        if (empty($authAppData['syOpenAppRole'])) {
            // return sendRespCode401($isCheckApi ? '100108' : '100000');
        }
        /**
         * 判断：token
         */
        if (empty($authAppData['syOpenAppToken'])) {
            /**
             * 这里要转移到鉴权token上
             * 转到 SyAuthTokenMiddle 中间件处理
             */
            // return sendRespCode401('100101');
        }
        return $next($request);
    }
}
