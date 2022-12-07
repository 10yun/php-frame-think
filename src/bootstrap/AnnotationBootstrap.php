<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use shiyun\annotation\AnnotationParse;
use shiyun\route\RouteAttriLoad;

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

    public function boot()
    {
        // $this->reader = $reader;

        // //注解路由
        // $this->registerAnnotationRoute();

        // //自动注入
        // $this->autoInject();

        // //模型注解方法提示
        // $this->detectModelAnnotations();
    }

    protected static array $defaultConfig = [
        'include_paths' => [
            'app',
        ],
        'exclude_paths' => [],
        'route' => [
            'use_default_method' => true,
        ],
    ];

    /**
     * 进程名称
     * @var string
     */
    protected static string $workerName = '';

    /**
     * 注解配置
     * @var array
     */
    public static array $config = [];

    /**
     * @param $worker
     * @return void
     * @throws ReflectionException
     */
    public static function start($worker)
    {
        // monitor进程不执行
        //if ($worker?->name == 'monitor') {
        //   return;
        //}

        // 跳过忽略的进程
        // if (!$worker || self::isIgnoreProcess(self::$workerName = $worker->name)) {
        //     return;
        // }

        // // 获取配置
        // self::$config = config('plugin.shiyun.webman.annotation', []);
        // $config = self::$config = array_merge(self::$defaultConfig, self::$config);

        RouteAttriLoad::loader();

        // 注解扫描
        $generator = AnnotationParse::scanAnnotations($config['include_paths'], $config['exclude_paths']);
        // 解析注解
        AnnotationParse::parseAnnotations($generator);
    }
}
