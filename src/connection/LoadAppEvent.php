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

/**
 * 依赖注入，应用事件类
 * 鉴权的时候 获取 【apps应用独立事件配置】
 * @author ctocode
 */
class LoadAppEvent
{
    protected $app;
    public $eventData = [];
    public function register()
    {
        $this->app = app();

        // 可以参考 tp6的加载应用相关配置
        // $appPath = $this->getAppPath();

        // if (is_file($appPath . 'common.php')) {
        //     include_once $appPath . 'common.php';
        // }

        // include_once $this->thinkPath . 'helper.php';

        // $configPath = $this->getConfigPath();

        // $files = [];

        // if (is_dir($configPath)) {
        //     $files = glob($configPath . '*' . $this->configExt);
        // }

        // foreach ($files as $file) {
        //     $this->config->load($file, pathinfo($file, PATHINFO_FILENAME));
        // }

        // if (is_file($appPath . 'event.php')) {
        //     app()->loadEvent();
        //     $this->loadEvent(include $appPath . 'event.php');
        // }

        // if (is_file($appPath . 'service.php')) {
        //     app()->register();
        //     $services = include $appPath . 'service.php';
        //     foreach ($services as $service) {
        //         $this->register($service);
        //     }
        // }
        // $this->app->event->listen('HttpRun', function () {
        //     app()->register();
        //     $this->app->middleware->add(MultiApp::class);
        // });

        // $this->commands([
        //     'build' => command\Build::class,
        //     'clear' => command\Clear::class,
        // ]);

        // $this->app->bind([
        //     'think\route\Url' => Url::class,
        // ]);

        // bind('SyOpenAppsEve', function () {
        //     $class = new \shiyun\connection\OpenAppEvent();
        //     return $class;
        // });
        // bind('SyOpenAppsEve', 'shiyun\connection\SyOpenAppsEve');
    }
    public function boot()
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
        /**
         * 批量注册 - 事件、监听、订阅
         * @var array $batchPathArr
         * @version 2019-12-11
         */
        $batchPathArr = glob(root_path() . '/config/' . $diyProFlag_str . '/events/*.php');
        if (!empty($batchPathArr)) {
            foreach ($batchPathArr as $itemPath) {
                $itemData = include_once $itemPath;
                if (empty($itemData) || !is_array($itemData)) {
                    continue;
                }
                $event_def['bind'] = array_merge($event_def['bind'], $itemData['bind']);
                $event_def['listen'] = array_merge($event_def['listen'], $itemData['listen']);
                $event_def['subscribe'] = array_merge($event_def['subscribe'], $itemData['subscribe']);
            }
        }
        var_dump($event_def);
        // 服务启动
        echo '启动xxxxx';
    }
    public function setEventData($data = [])
    {
        $this->eventData = $data;
    }
    public function getEventData()
    {
        return $this->eventData;
    }
}
