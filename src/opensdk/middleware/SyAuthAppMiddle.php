<?php

namespace shiyunOpensdk\middleware;

use shiyun\support\Cache;

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
        $OpenAppAuthObj = new \shiyunOpensdk\connection\OpenAppAuth();
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
            return sendRespCode401(100201);
        }
        /**
         * 判断： syOpenAppId
         */
        if (empty($authAppData['syOpenAppId'])) {
            return sendRespCode401(100202);
        }
        /**
         * 过滤  $syOpenAppId
         */
        $isCheckApp = base_privatization_check_app($authAppData['syOpenAppProject'], $authAppData['syOpenAppId']);
        if (!$isCheckApp) {
            return sendRespCode401(100202);
        }
        /**
         * appSecret
         */
        if (empty($authAppData['syOpenAppSecret'])) {
            // return sendRespCode401(100203);
        }
        /**
         * 判断：角色
         */
        if (empty($authAppData['syOpenAppRole'])) {
            // return sendRespCode401(100206);
        }
        /**
         * 判断：token
         */
        if (empty($authAppData['syOpenAppToken'])) {
            /**
             * 这里要转移到鉴权token上
             * 转到 SyAuthTokenMiddle 中间件处理
             */
            // return sendRespCode401(100101);
        }
        /**
         * 判断：是否在维护
         */
        // $maintainInfo = Cache::store('CACHE_STORES_RD2')->get($authAppData['syOpenAppProject'] . ":Maintain");
        // if (!empty($maintainInfo) && $maintainInfo['weihu_open'] == 'on') {
        //     return sendRespCode200('900000');
        // }

        return $next($request);
    }
}
