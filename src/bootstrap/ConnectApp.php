<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use shiyun\support\Service as BaseService;

class ConnectApp extends BaseService
{
    public function register()
    {
        // var_dump('ConnectApp   register');
        // 应用接入参数
        // $this->app->bind('SyOpenAppsAuth', \shiyunOpensdk\connection\OpenAppAuth::class);
        $this->app->bind('SyOpenAppsAuth', function () {
            $class = new \shiyunOpensdk\connection\OpenAppAuth();
            $class->initAuthData();
            return $class;
        });
        $this->app->bind('SyOpenAppsConfig', \shiyunOpensdk\connection\OpenAppConfig::class);
        // 应用信息
        $this->app->bind('SyOpenAppsAccess', \shiyunOpensdk\connection\OpenAppAccess::class);
        /**
         * 注册路由
         */
        $this->registerRoutes(function (\think\Route $route) {
            // $route->get('captcha/[:config]', "\\think\\captcha\\CaptchaController@index");

            /**
             * 注册资源路由
             */
            $route->get('/ui/<addons>/<resource>', "\\shiyunOpensdk\\connection\\LoadAppResource@getUI")
                ->ext('css|js|jpg|jpeg|png|gif|ico');
        });
    }
    public function boot()
    {
        // 可以参考 tp6的加载应用相关配置
        // $appPath = $this->getAppPath();
        // \think\App-> load() 方法加载
        /**
         * 注册路由
         */
        new \shiyunOpensdk\connection\LoadAppRoute($this->app);
        /**
         * 注册事件
         */
        // new \shiyunOpensdk\connection\LoadAppEvent($this->app);
        /**
         * 默认数据库切换
         */
        new \shiyunOpensdk\connection\LoadAddonsDb($this->app);
    }
}
