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
         * 注册中间件
         */
        $this->app->middleware->import([
            // 跨域
            \shiyun\middleware\BaseCrossMiddle::class,
            // methods处理
            \shiyun\middleware\CheckRestMiddle::class,
            // 限流
            \shiyun\middleware\ThrottleMiddle::class,
        ]);
        /**
         * 注册事务管理
         */
        $this->registerTransaction();
    }
    protected function registerTransaction()
    {
        // 是否启动事务
        $isDbTxnAuto = syGetConfig('shiyun.app.db_transaction_auto');
        // 事务类型
        $isDbTxnType = syGetConfig('shiyun.app.db_transaction_type');
        if (app()->runningInConsole() || empty($isDbTxnAuto) || empty($isDbTxnType)) {
            return;
        }
        if (!in_array($isDbTxnAuto, [true, 'on'])) {
            return;
        }
        $typeArr = ['middleware', 'event'];
        if (!in_array($isDbTxnType, $typeArr)) {
            return;
        }
        // 注册事务管理器
        $this->app->bind(\shiyun\extend\TransactionManager::class);
        if ($isDbTxnType == 'middleware') {
            // 中间件方式
            $this->app->middleware->add(\shiyun\middleware\AutoTransactionMiddle::class);
        } else  if ($isDbTxnType == 'event') {
            // 或事件监听方式
            $this->app->event->subscribe(\shiyun\extend\TransactionSubscriber::class);
        }
    }
    public function boot()
    {
        // 指令定义
        $this->commands([
            /**
             * 基础相关
             */
            'ConfigPush' => \shiyun\command\ConfigPush::class,
            'ServiceDiscover' => \shiyun\command\ServiceDiscover::class,
            'VendorPublish' => \shiyun\command\vendorPublish::class,
            'CreateApiFlag' => \shiyun\command\CreateApiFlag::class,
            /**
             *  生成 addons 应用相关 
             */
            'MakeController' => \shiyun\command\make\MakeController::class,
            'MakeModel' => \shiyun\command\make\MakeModel::class,
            'MakeServer' => \shiyun\command\make\MakeServer::class,
            'MakeValidate' => \shiyun\command\make\MakeValidate::class,
            'MakeQueue' => \shiyun\command\make\MakeQueue::class,
            /**
             * 验证 addons
             */
            'AddonsCheck' => \shiyun\command\AddonsCheck::class,

            /**
             *  队列相关 
             */

            // shiyunQueue\command\FailedFlush::class,
            // shiyunQueue\command\Retry::class,
            // shiyunQueue\command\Work::class,
            // shiyunQueue\command\Restart::class,
            // shiyunQueue\command\Listen::class,
            // 'queueWorker' => \shiyunQueue\command\QueueWorker::class
            \shiyunQueue\command\QueueWorker::class
            // 创建 简单模型队列
            // 'create:simpleQueue' => '',
        ]);
    }
}
