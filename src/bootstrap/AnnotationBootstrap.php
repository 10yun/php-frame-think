<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use shiyun\support\Service as BaseService;
use shiyun\annotation\AnnotationParse;
use shiyun\annotation\AnnotationLoad;
use think\Route as FrameRoute;

class AnnotationBootstrap extends BaseService
{
    // public function register()
    // {
    //     // var_dump('---AnnotationBootstrap---register---');
    //     // $this->app->bind(Reader::class, function (App $app, Config $config, Cache $cache) {
    //     //     $store = $config->get('annotation.store');
    //     //     return new CachedReader(new AnnotationReader(), $cache->store($store), $app->isDebug());
    //     // });
    // }
    protected array $defaultConfig = [
        'include_paths' => [
            'app/controller',
        ],
        'exclude_paths' => [],
        'route' => [
            'use_default_method' => true,
        ],
    ];
    /**
     * 注解配置
     */
    public function getConfig(): array
    {
        // // 获取配置
        $configOpt = syGetConfig('shiyun.route');
        $config = array_merge($this->defaultConfig, $configOpt);
        return $config;
    }
    function is_cli()
    {
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
        // return PHP_SAPI == 'cli';
        // return str_starts_with(PHP_SAPI, 'cgi');
    }
    public function boot()
    {
        try {
            // 加载
            AnnotationLoad::loader();
            // 获取配置
            $config = $this->getConfig();
            if (!empty($config['route']['load_type']) && $config['route']['load_type'] == 'current') {
                if (!$this->is_cli() && !app()->runningInConsole()) {
                    $config['include_paths'] = '';
                    $supportCommon = new \shiyun\support\Common($this->app);
                    $include_paths = $supportCommon->getRoutePath();
                    if (!empty($include_paths)) {
                        $config['include_paths'] = [
                            $include_paths
                        ];
                    }
                }
            }
            if (!empty($config['include_paths'])) {
                // 注解扫描
                $generator = AnnotationParse::scanAnnotations($config['include_paths'], $config['exclude_paths']);
                // 解析注解
                AnnotationParse::parseAnnotations($generator);
                /**
                 *  目前需要这么注册，才能生成缓存文件
                 */
                $this->registerRoutes(function (FrameRoute $routeObj) use ($config) {

                    AnnotationLoad::register($routeObj);

                    if (!empty($config['route']['debug']) && $config['route']['debug'] == 'html') {
                        /**
                         * 调试展示 - html
                         */
                        AnnotationLoad::debug($routeObj);
                        dd();
                    } else if (!empty($config['route']['debug']) && $config['route']['debug'] == 'dump') {
                        /**
                         * 调试展示 - dump
                         */
                        $allRule = $routeObj->getRuleList();
                        dd('----555----', $allRule);
                    }
                });
            }
        } catch (\Exception $exception) {
            // throw $exception;
            var_dump('--解析错误-', $exception->getMessage());
        }
        // //注解路由
        // $this->registerAnnotationRoute();

        // //自动注入
        // $this->autoInject();
    }
}
