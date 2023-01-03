<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use shiyun\annotation\AnnotationParse;
use shiyun\route\RouteAttriLoad;
use shiyun\route\RouteAnnotationHandle;
use think\Route;

class AnnotationBootstrap extends \think\Service
{
    // public function register()
    // {
    //     AnnotationReader::addGlobalIgnoredName('mixin');

    //     // TODO: this method is deprecated and will be removed in doctrine/annotations 2.0
    //     AnnotationRegistry::registerLoader('class_exists');

    //     $this->app->bind(Reader::class, function (App $app, Config $config, Cache $cache) {

    //         $store = $config->get('annotation.store');

    //         return new CachedReader(new AnnotationReader(), $cache->store($store), $app->isDebug());
    //     });
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

        return "addons/{$requFirst}/controller";
    }
    function is_cli()
    {
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }
    public function boot()
    {
        // 获取配置
        $config = $this->getConfig();
        if (!$this->is_cli()) {
            if (!empty($config['route']['load_type']) && $config['route']['load_type'] == 'current') {
                //
                $config['include_paths'] = [
                    $this->getUriFirst()
                ];
            }
        }
        RouteAttriLoad::loader();
        // 注解扫描
        $generator = AnnotationParse::scanAnnotations($config['include_paths'], $config['exclude_paths']);
        // var_dump('--111--');
        try {
            // 解析注解
            AnnotationParse::parseAnnotations($generator);
            // RouteAttriLoad::register();
            // RouteAnnotationHandle::createRoute();
            /**
             *  可能需要这么注册，才能生成缓存文件
             */
            $this->registerRoutes(function (Route $route) {
                //     $route->get('captcha/[:config]', "\\think\\captcha\\CaptchaController@index");
                RouteAnnotationHandle::createRoute($route);
            });
        } catch (\Throwable $th) {
            // throw $th;
            var_dump('--解析错误-', $th->getMessage());
        }

        // $this->reader = $reader;

        // //注解路由
        // $this->registerAnnotationRoute();

        // //自动注入
        // $this->autoInject();
    }
}
