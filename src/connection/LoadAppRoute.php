<?php

/**
 * 【ctocode】     核心文件
 * ============================================================================
 * @author       作者         ctocode-zhw
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @copyright    版权所有   【福州十云科技有限公司】，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936 ，QQ:240337740
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

declare(strict_types=1);

namespace shiyun\connection;

use shiyun\support\Cache;
use shiyun\support\Env;
use think\App;
use think\Route;
use think\event\RouteLoaded;
use shiyun\support\Route as supportRoute;

class LoadAppRoute
{
    protected $app;
    public function __construct(App $app)
    {
        $this->app = $app;
        // 加载addons下的
        $this->loadAddons();
        // 加载project下的
        // $this->loadProject();
    }
    public function loadAddons()
    {
        // 这边可以判断缓存是否存在
        $isDebug = $this->app->isDebug();
        $isEnvironment = Env::get('ctocode.environment');

        $loadCache = [];
        $cacheOptimizePath = _PATH_RUNTIME_ . '/shiyun_optimize/route.php';
        if (!$isDebug && $isEnvironment != 'development') {
            if (file_exists($cacheOptimizePath)) {
                $loadCache = include_once $cacheOptimizePath;
            }
        }
        if (empty($loadCache)) {
            $rootPath = root_path();
            $batchPathArr = [];
            /**
             * 加载全部路由
             */
            // $configLoadDirArr = syGetConfig('shiyun.connect_open.load_app_route', []);
            // if (!empty($configLoadDirArr) && is_array($configLoadDirArr)) {
            //     foreach ($configLoadDirArr as $key => $val) {
            //         $itemPath = $val;
            //         if (!str_contains($itemPath, $rootPath)) {
            //             $itemPath = $rootPath . $itemPath;
            //         }
            //         if (is_dir($itemPath)) {
            //             $itemPath = "{$itemPath}/*.php";
            //         }
            //         if (!str_contains($itemPath, "*.php")) {
            //             $itemPath = "{$itemPath}/*.php";
            //         }
            //         $itemPath = str_replace("//", "/", $itemPath);
            //         $batchPathArr = array_merge($batchPathArr, glob($itemPath));
            //     }
            // }
            /**
             * 获取第一段路由
             * 只加载第一段路由
             */
            // 
            $request_uri = request()->server('REQUEST_URI');
            $uriArr = array_filter(explode("/", parse_url($request_uri, PHP_URL_PATH)));
            $firstUriStr = current($uriArr);
            if (!empty($firstUriStr)) {
                if (str_contains($firstUriStr, ".")) {
                    $firstUriStr = str_replace(".", "/", $firstUriStr);
                }
                $firstUriDir = $rootPath . "addons/$firstUriStr/route/*.php";
                $batchPathArr = array_merge($batchPathArr, glob($firstUriDir));
            }
            if (!empty($batchPathArr)) {
                foreach ($batchPathArr as $itemPath) {
                    include $itemPath;
                }
            }
        }
        // dd($this->app->route->getRule('/'));
        $this->app->event->listen(RouteLoaded::class, function (Route $route) {
            if (empty($route->getRule(''))) {
                $route->get('/', function () {
                    if (request()->isAjax()) {
                        return sendRespSucc('暂无数据');
                    } else {
                        return 'hello - ' . syGetVersion();
                    }
                });
            }
            // dd(
            //     '----LoadAppRoute',
            //     $route->getName(),
            //     $route->getRuleList(),
            //     $route->getRule('')
            // );
            // dd('???');
        });
        /**
         * 写入缓存
         */
        // dd($batchPathArr, $loadCache);
        // if (empty($loadCache)) {
        //     if (!$isDebug && $isEnvironment != 'development') {
        //         $path = $rootPath . 'runtime/shiyun/';
        //         $path = $rootPath . 'runtime/';
        //         @mkdir($path);
        //         $filename = $path . 'route.php';
        //         if (is_file($filename)) {
        //             unlink($filename);
        //         }
        //         file_put_contents($filename, $this->buildRouteCache());
        //     }
        // }
    }

    protected function buildRouteCache(): string
    {
        $this->app->route->clear();
        $this->app->route->lazy(false);

        // 路由检测
        $path = $this->app->getRootPath() . 'runtime/shiyun/route/';
        $files = is_dir($path) ? scandir($path) : [];

        foreach ($files as $file) {
            if (strpos($file, '.php')) {
                include $path . $file;
            }
        }
        //触发路由载入完成事件
        $this->app->event->trigger(RouteLoaded::class);
        $rules = $this->app->route->getName();

        return '<?php ' . PHP_EOL . 'return unserialize(\'' . serialize($rules) . '\');';
    }
}
