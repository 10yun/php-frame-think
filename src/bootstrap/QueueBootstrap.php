<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use think\helper\Arr;
use think\helper\Str;
use shiyunQueue\QueueFactory;
use shiyunQueue\command\Listen;
use shiyunQueue\command\Restart;
use shiyunQueue\command\Retry;
use shiyunQueue\command\Work;
use shiyun\support\Service as BaseService;

class QueueBootstrap extends BaseService
{
    public function register()
    {
        // 队列
        $this->app->bind('queue', QueueFactory::class);
        // 错误
        $this->app->bind('queue_failer', function () {
            $config = $this->app->config->get('queue.failed', []);
            $type = Arr::pull($config, 'type', 'none');
            // $class = false !== strpos($type, '\\') ? $type : 'shiyunQueue\\failed\\' . Str::studly($type) . "Failed";
            $class = false !== strpos($type, '\\') ? $type : 'shiyunQueue\\drive\\' . $type . "\\" . Str::studly($type) . "Failed";
            return $this->app->invokeClass($class, [$config]);
        });
    }

    public function boot()
    {
        // 指令定义
        $this->commands([
            // FailedFlush::class,
            // Retry::class,
            // Work::class,
            // Restart::class,
            Listen::class,
            // 'queueWorker' => \shiyunQueue\command\QueueWorker::class
            \shiyunQueue\command\QueueWorker::class
            // 创建 简单模型队列
            // 'create:simpleQueue' => '',
        ]);
    }
}
