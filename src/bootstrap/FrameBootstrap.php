<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use shiyun\support\Service as BaseService;
use think\response\Json;
use think\facade\Cache;
use think\Request;

class FrameBootstrap extends BaseService
{

    public function register()
    {
        /**
         * 注册 csrf-token
         */
        $this->registerRoutes(function (\think\Route $route) {
            $route->get('/csrf_token', "\\shiyun\\extend\CsrfToken@getCsrfToken");
        });
        /**
         * 注册 事件
         */
        $isDbAutoTA = syGetConfig('shiyun.app.db_auto_transaction');

        if (!empty($isDbAutoTA)  && ($isDbAutoTA == true || $isDbAutoTA == 'on')) {
            $this->app->loadEvent([
                'subscribe' => [
                    \shiyun\extend\DbAutoTransaction::class,
                ]
            ]);
        }
    }
    public function boot()
    {
        $this->commands([
            'ConfigPush' => \shiyun\command\ConfigPush::class,
            'ServiceDiscover' => \shiyun\command\ServiceDiscover::class,
            'VendorPublish' => \shiyun\command\vendorPublish::class,
            'CreateApiFlag' => \shiyun\command\CreateApiFlag::class,
            // 生成 addons 应用相关
            'MakeController' => \shiyun\command\make\MakeController::class,
            'MakeModel' => \shiyun\command\make\MakeModel::class,
            'MakeServer' => \shiyun\command\make\MakeServer::class,
            'MakeValidate' => \shiyun\command\make\MakeValidate::class,
            'MakeQueue' => \shiyun\command\make\MakeQueue::class,
        ]);
    }
}
