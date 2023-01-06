<?php

namespace shiyun\middleware;

use think\helper\Str;
use shiyun\support\Db;
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
        // frameLogsFile('SyAuthAppMiddleware  ----- ');
        $OpenAppAuthObj = new \shiyun\connection\OpenAppAuth();
        $OpenAppAuthObj->initAuthData();
        $authAppData = $OpenAppAuthObj->getAuthData();
        /**
         *  接收 appid + appkey
         *  验证 appsecret 
         */


        /**
         * 是否自动鉴权
         */
        // $autoSett = $this->doPassAuto($request);



        /**
         * 判断：项目
         */
        if (empty($authAppData['syOpenAppProject'])) {
            return sendRespCode401('100105');
        }
        // $maintainInfo = Cache::store('CACHE_STORES_RD2')->get($authAppData['syOpenAppProject'] . ":Maintain");
        // if (!empty($maintainInfo) && $maintainInfo['weihu_open'] == 'on') {
        //     return sendRespCode200('900000');
        // }
        /**
         * 判断：appID
         */
        if (empty($authAppData['syOpenAppId'])) {
            return sendRespCode401('100106');
        }
        /**
         * 过滤 $syOpenAppId
         */
        $pass_appsid = syGetAppsArr();
        if (!in_array($authAppData['syOpenAppId'], $pass_appsid)) {
            return sendRespCode401('100106');
        }
        /**
         * appKey
         */
        if (empty($authAppData['syOpenAppKey'])) {
            return sendRespCode401('100107');
        }
        /**
         * 判断：角色
         */
        if (empty($authAppData['syOpenAppRole'])) {
            // return sendRespCode401('100107');
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
    /**
     *  === 复杂、使用反射，判断权限 === 
     *  过滤权限
     */
    protected function doPassAuto($request)
    {
        // 获取应用名字
        // $appMapArr = frameGetConfig('app.app_map');
        // $modNameStr =  $request->root() ?: '';
        // $modNameStr = str_replace('/', '', $modNameStr);
        // $modNameStr = strtolower($modNameStr);
        // $modNamespace  = $appMapArr[$modNameStr] ?: '';
        // $conNameStr = $request->pathinfo() ?: '';

        $conNameStr = "";
        $ruleData = $request->rule();
        //获取访问控制器和方法
        $ruleDataName = $request->rule()->getName();
        //通过字符串分割，获取到具体的类文件和操作的方法名称

        if (!empty($ruleDataName) && strpos($ruleDataName, '@') !== false) {
            if (strpos($ruleDataName, '@') !== false) {
                $conName = substr($ruleDataName, 0, strpos($ruleDataName, '@'));
            } else {
                $conName = substr($ruleDataName, 0, strpos($ruleDataName, '/'));
            }
            $className = $conName;
        } else {
            /**
             * 获取应用名字
             */
            $modNameStr = app('http')->getName();
            $modNamespace = $modNameStr;
            /**
             * 获取类名字
             */
            $conNameStr = request()->controller();
            /**
             * 意图名字
             */
            $actNameStr = request()->action();

            /**
             * 如果是 【.】则 为多层控制器
             */
            $conNameArr = explode(".", $conNameStr);
            $conNameArrLen = count($conNameArr);

            $className  =  "\app\\{$modNamespace}\controller";


            // dd($modNameStr, $conNameStr, $actNameStr, $request);

            foreach ($conNameArr as  $key1 => $val1) {
                /**
                 * 如果是 【/】则后面为方法
                 */
                if (Str::contains($val1, '/')) {
                    $conFuncArr = explode("/", $val1);
                    $conFuncArrLen = count($conFuncArr);
                    foreach ($conFuncArr as $key2 => $val2) {
                        if ($key2 != ($conFuncArrLen - 1)) {
                            $comNamespaceItem = Str::studly($val2);
                            $className .= "\\{$comNamespaceItem}";
                        }
                    }
                } else {
                    if ($key1 == ($conNameArrLen - 1)) {
                        $comNamespaceItem = Str::studly($val1);
                    } else {
                        $comNamespaceItem = strtolower($val1);
                    }
                    $className .= "\\{$comNamespaceItem}";
                }
            }
        }


        try {
            // var_dump($className);
            // exit();
            // dd($className);
            if (!class_exists($className)) {
                // frameLogsFile($conNameStr, $className);
                return sendRespInfo([
                    'status' => 404,
                    'msg' => '接口错误',
                    'error2' => '验证class权限',
                    // 'error3' => $conNameArr,
                    'error' => $conNameStr,
                ]);
            }
            $reflectObj = new \ReflectionClass($className);
            // $reflectObj->hasProperty("_token_") // 是否定义
            $prosArr = $reflectObj->getDefaultProperties(); // 默认的值
            return $prosArr;
        } catch (\LogicException $logicDuh) {
            // print_r($logicDuh);
        }
        return [];
    }
}
