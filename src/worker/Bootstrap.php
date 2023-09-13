<?php

declare(strict_types=1);

namespace shiyunWorker;

use shiyun\support\Service as BaseService;

class Bootstrap extends BaseService
{
    public function register()
    {
        $this->commands([
            'worker'         => \shiyunWorker\command\Worker::class,
            'worker:server'  => \shiyunWorker\command\Server::class,
            'worker:gateway' => \shiyunWorker\command\GatewayWorker::class,
            'worker:task' => \shiyunWorker\command\WorkerTask::class,
            \shiyunWorker\command\WorkerAll::class,
        ]);
    }
}
