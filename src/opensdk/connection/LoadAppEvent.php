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

namespace shiyunOpensdk\connection;

use think\App;
use shiyun\support\Cache;
use shiyun\support\Env;

/**
 * 注册的事件
 * 应用事件类
 * 鉴权的时候 获取 【apps应用独立事件配置】
 * @author ctocode
 */
class LoadAppEvent
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
        $envProjectEnvironment = Env::get('ctocode.project_environment');

        $loadCache = [];
        $cacheOptimizePath = _PATH_RUNTIME_ . '/shiyun_optimize/events.php';
        if (!$isDebug && $envProjectEnvironment != 'development') {
            if (file_exists($cacheOptimizePath)) {
                $loadCache = include_once $cacheOptimizePath;
            }
        }
        if (empty($loadCache)) {
            $rootPath = root_path();
            $batchPathArr = [];
            $configLoadDirArr = syGetConfig('shiyun.connect_open.load_app_event', []);
            if (!empty($configLoadDirArr) && is_array($configLoadDirArr)) {
                foreach ($configLoadDirArr as $key => $val) {
                    $itemPath = $val;
                    if (!str_contains($itemPath, $rootPath)) {
                        $itemPath = $rootPath . $itemPath;
                    }
                    if (is_dir($itemPath)) {
                        $itemPath = "{$itemPath}/*.php";
                    }
                    if (!str_contains($itemPath, "*.php")) {
                        $itemPath = "{$itemPath}/*.php";
                    }
                    $itemPath = str_replace("//", "/", $itemPath);
                    $batchPathArr = array_merge($batchPathArr, glob($itemPath));
                }
            }
            /**
             * 批量注册 - 事件、监听、订阅
             * @var array $batchPathArr
             * @version 2019-12-11
             */
            if (!empty($batchPathArr)) {
                $includeData = [
                    'bind' => [],
                    'listen' => [],
                    'subscribe' => [],
                ];
                foreach ($batchPathArr as $itemPath) {
                    $itemData = include_once $itemPath;
                    if (empty($itemData) || !is_array($itemData)) {
                        continue;
                    }
                    if (!empty($itemData['bind'])) {
                        $includeData['bind'] = array_merge($includeData['bind'], $itemData['bind']);
                    }
                    if (!empty($itemData['listen'])) {
                        $includeData['listen'] = array_merge($includeData['listen'], $itemData['listen']);
                    }
                    if (!empty($itemData['subscribe'])) {
                        $includeData['subscribe'] = array_merge($includeData['subscribe'], $itemData['subscribe']);
                    }
                }
                $loadCache = $includeData;
            }

            if (!$isDebug && $envProjectEnvironment != 'development') {
                @mkdir(_PATH_RUNTIME_ . '/shiyun_optimize/');
                if (is_file($cacheOptimizePath)) {
                    unlink($cacheOptimizePath);
                }
                $cacheOptimizeContent = '<?php ' . PHP_EOL . 'return unserialize(\'' . serialize($loadCache) . '\');';
                file_put_contents($cacheOptimizePath, $cacheOptimizeContent);
            }
        }
        if (!empty($loadCache)) {
            $this->app->loadEvent($loadCache);
        }
    }
    /**
     * 注册项目配置下的事件
     */
    public function loadProject()
    {
        $diyProConf = [];
        $diyProFlag = request()->header('syOpenAppProject') ?: '';

        $diyProFlag_str = \think\helper\Str::snake($diyProFlag);

        // $xxx = syOpenAppsAuth();
        var_dump('--222--', $diyProFlag, $diyProFlag_str);

        // 事件定义文件
        $event_def = [
            'bind' => [],
            'listen' => [],
            'subscribe' => []
        ];

        var_dump($event_def);
        // 服务启动
        echo '启动xxxxx';
    }
}
