<?php

namespace shiyunOpensdk\middleware;


/**
 * token鉴权
 */
class SyAuthTokenMiddle
{
    public function handle($request, \Closure $next)
    {
        $syAppsAccess = [];
        $SyOpenAppsAuth = app('SyOpenAppsAuth')->getAuthData();
        $syOpenAppToken = $SyOpenAppsAuth['syOpenAppToken'] ?? '';
        if (empty($syOpenAppToken)) {
            return sendRespCode401(100101);
        }
        // 是否自动鉴权
        $syAppsAccess = app('SyOpenAppsAccess')->getAccessData();
        /**
         * token过期
         */
        if (!empty($syOpenAppToken) && empty($syAppsAccess)) {
            return sendRespCode401(100102);
        }
        if (empty($syAppsAccess)) {
            return sendRespCode401(100109);
        }
        if (
            $syAppsAccess
            && ($syAppsAccess['account_state'] == 9 || $syAppsAccess['account_state'] == 'disable')
        ) {
            return sendRespCode401(100910);
        }

        // 获取全部的禁用用户名单
        // $ucenterBlackDatas = loadAddonsModel('v210916_ucenter', 'Black')->getListData(array(
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
