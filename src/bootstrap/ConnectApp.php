<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

class ConnectApp extends \think\Service
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
    }
    public function boot()
    {
        // var_dump('ConnectApp   boot');
    }
}
