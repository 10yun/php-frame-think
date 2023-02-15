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
        // $this->app->bind('SyOpenAppsAuth', \shiyun\connection\OpenAppAuth::class);
        $this->app->bind('SyOpenAppsAuth', function () {
            $class = new \shiyun\connection\OpenAppAuth();
            $class->initAuthData();
            return $class;
        });
        $this->app->bind('SyOpenAppsConfig', \shiyun\connection\OpenAppConfig::class);
        // 应用信息
        $this->app->bind('SyOpenAppsAccess', \shiyun\connection\OpenAppAccess::class);
        // 应用事件
        // $this->app->bind('SyOpenAppsEvent', \shiyun\connection\OpenAppAccess::class);

        /**
         * 加载路由
         */
        $this->registerRoutes(function (\think\Route $route) {
            // $route->get('captcha/[:config]', "\\think\\captcha\\CaptchaController@index");
            $route->get('/ui/<addons>/<resource>', "\\shiyun\\connection\\LoadAppResource@getUI")
                ->ext('css|js|jpg|jpeg|png|gif|ico');;
        });
    }
    public function boot()
    {
        // var_dump('ConnectApp   boot');
    }
}
