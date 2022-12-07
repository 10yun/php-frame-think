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


        /**
         *  接收 appid + appkey
         *  验证 appsecret 
         */


        /**
         * 是否自动鉴权
         */
        $autoSett = $this->doPassAuto($request);
        $syAppAutoAuth = $autoSett['_tokenAutoAuth'] ?? false;
        $syAppAutoBase = $autoSett['_tokenAutoBaseAuth'] ?? true;


        $reqParam = $request->param();

        /**
         * 处理 syOpenAppProject
         * 如果没有header数据，获取 $reqParam['syOpenAppProject'] 数据
         */
        $syOpenAppProject = $request->header('syOpenAppProject') ?: '';
        $syOpenAppProject = $syOpenAppProject ?: ($reqParam['syOpenAppProject'] ?? '');

        $maintainInfo = Cache::store('CACHE_STORES_RD2')->get($syOpenAppProject . ":Maintain");
        if (!empty($maintainInfo) && $maintainInfo['weihu_open'] == 'on') {
            return sendRespCode200('900000');
        }
        /**
         * 处理 syOpenAppRole
         * 如果没有header数据，获取 $reqParam['syOpenAppRole'] 数据
         */
        $syOpenAppRole = $request->header('syOpenAppRole') ?: '';
        $syOpenAppRole = $syOpenAppRole ?: ($reqParam['syOpenAppRole'] ?? '');

        /**
         * 处理 syOpenAppId
         * 如果没有header数据，获取 $reqParam['syOpenAppId'] 数据
         */
        $syOpenAppId = $request->header('syOpenAppId') ?: '';
        $syOpenAppId = $syOpenAppId ?: ($reqParam['syOpenAppId'] ?? '');
        if ($syAppAutoAuth && empty($syOpenAppId)) {
            return sendRespCode401('100106');
        }
        /**
         * 处理 syOpenAppKey
         * 如果没有header数据，获取 $reqParam['syOpenAppKey'] 数据
         */
        $syOpenAppKey = $request->header('syOpenAppKey') ?: '';
        $syOpenAppKey = $syOpenAppKey ?: ($reqParam['syOpenAppKey'] ?? '');

        if ($syAppAutoAuth && empty($syOpenAppKey)) {
            return sendRespCode401('100107');
        }
        // syGetProjectBase($syOpenAppProject);
        // dd($syOpenAppProject);
        /**
         * 过滤 $syOpenAppId
         */
        if ($syAppAutoBase) {
            $pass_appsid = syGetAppsArr();
            if (!in_array($syOpenAppId, $pass_appsid)) {
                return sendRespCode401('100106');
            }
        }

        /**
         * 处理 syOpenAppToken
         */
        $syOpenAppToken = $request->header('syOpenAppToken') ?: '';
        // 如果没有header数据，获取 $reqParam['syOpenAppToken'] 数据
        $syOpenAppToken =  $syOpenAppToken ?: ($reqParam['syOpenAppToken'] ?? '');
        if ($syAppAutoAuth && empty($syOpenAppToken)) {
            return sendRespCode401('100101');
            // sendRespCode200(100101, false);
        }
        $fwParam = [];
        $fwParam['syOpenAppProject'] = $syOpenAppProject;
        $fwParam['syOpenAppRole'] = $syOpenAppRole;
        $fwParam['syOpenAppId'] = $syOpenAppId;
        $fwParam['syOpenAppKey'] = $syOpenAppKey;
        $fwParam['syOpenAppToken'] = $syOpenAppToken;
        $fwParam['syAppAutoAuth'] = $syAppAutoAuth;
        $fwParam['syAppAutoBase'] = $syAppAutoBase;
        // 设备 类型：ios、android
        $fwParam['syAppClientPlatform'] = $request->header('syOpenAppClientPlatform') ?? '';
        // 获取设备唯一标识
        $fwParam['syAppClientUUID'] = '';
        app('SyOpenAppsAuth')->setAuthData($fwParam);

        $syAppsAccess = [];
        if ($syAppAutoAuth) {
            // 是否自动鉴权
            $syAppsAccess =  app('SyOpenAppsAccess')->getAccessData();

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
            if (empty($syAppsAccess)) {
                return sendRespCode401('100109');
            }
        }
        if ($syAppsAccess && $syAppsAccess['ucenter_state'] == 9) {
            return sendRespCode200('100400');
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
            // $reflectObj->hasProperty("_tokenAutoBaseAuth") // 是否定义
            $prosArr = $reflectObj->getDefaultProperties(); // 默认的值
            return $prosArr;
        } catch (\LogicException $logicDuh) {
            // print_r($logicDuh);
        }
        return [];
    }
}
