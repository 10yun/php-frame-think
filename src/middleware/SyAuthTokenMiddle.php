<?php

namespace shiyun\middleware;

use shiyun\support\Request;

/**
 * token鉴权
 */
class SyAuthTokenMiddle
{
    public function handle($request, \Closure $next)
    {
        $isCheckApi = Request::isCheckApi();

        $syAppsAccess = [];
        $SyOpenAppsAuth = app('SyOpenAppsAuth')->getAuthData();
        $syOpenAppToken = $SyOpenAppsAuth['syOpenAppToken'] ?? '';
        if (empty($syOpenAppToken)) {
            return sendRespCode401($isCheckApi ? '100101' : '100000');
        }
        // 是否自动鉴权
        $syAppsAccess =  app('SyOpenAppsAccess')->getAccessData();
        /**
         * token过期
         */
        if (!empty($syOpenAppToken) && empty($syAppsAccess)) {
            return sendRespCode401(100102);
        }
        if (empty($syAppsAccess)) {
            return sendRespCode401('100109');
        }
        if ($syAppsAccess && $syAppsAccess['ucenter_state'] == 9) {
            return sendRespCode200('100400');
        }
        // 获取全部的禁用用户名单
        // $ucenterBlcakRpcModelObj = loadAddonRpcClass('v210916_ucenter', 'Black');
        // $ucenterBlackDatas = $ucenterBlcakRpcModelObj->getListData(array(
        //     'field' => 'a.account_id'
        // ));
        // if (!empty($ucenterBlackDatas['data'])) {
        //     $tempArr = array();
        //     foreach ($ucenterBlackDatas['data'] as $key => $val) {
        //         $tempArr[$key] = $val['account_id'];
        //     }
        //     $wsql['ucenter_black'] = implode(',', $tempArr);
        // }

        // $url_rule = $request->root() . '/' . $request->pathinfo();
        // dd($request->root(), $url_rule);
        return $next($request);

        /**
         * 后置
         */
        // $response = $next($request);
        // return $response;
    }
}
