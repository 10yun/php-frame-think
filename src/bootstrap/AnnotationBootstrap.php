<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use shiyun\support\Service as BaseService;
use shiyun\annotation\AnnotationParse;
use shiyun\route\RouteAttriLoad;
use think\Route;

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
     * @var array
     */
    public function getConfig()
    {
        // // 获取配置
        $configOpt = syGetConfig('shiyun.annotation');
        $config = array_merge($this->defaultConfig, $configOpt);
        return $config;
    }
    protected function getUriFirst()
    {
        $requestObj = $this->app->request;
        $request_uri = $requestObj->baseUrl();
        // $request_uri = $requServer['REQUEST_URI'] ?? '';
        if ($request_uri == '/') {
        } else {
            $requSerArr = explode("/", $request_uri);
            $requSerArr2 = array_filter($requSerArr);
            $requSerArr = array_merge($requSerArr2);
            $requFirst = $requSerArr[0];
        }
        if (!empty($requFirst)) {
            if (str_contains($requFirst, ".")) {
                $companyArr = str_replace(".", "/", $requFirst);
                return "addons/{$companyArr}/controller";
            }
            return "addons/{$requFirst}/controller";
        }
        return '';
    }
    function is_cli()
    {
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }
    public function boot()
    {
        // var_dump('---AnnotationBootstrap---boot---');
        try {
            // 加载
            RouteAttriLoad::loader();
            // 获取配置
            $config = $this->getConfig();

            if (!empty($config['route']['load_type']) && $config['route']['load_type'] == 'current') {
                if (!$this->is_cli()) {
                    $config['include_paths'] = '';
                    if (!empty($this->getUriFirst())) {
                        $config['include_paths'] = [
                            $this->getUriFirst()
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
                $this->registerRoutes(function (Route $routeObj) use ($config) {

                    RouteAttriLoad::register($routeObj);

                    if (!empty($config['route']['debug']) && $config['route']['debug'] == 'html') {
                        /**
                         * 调试展示 - html
                         */
                        RouteAttriLoad::debug($routeObj);
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
