<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace shiyunQueue\drive\sync;

use Exception;
use shiyunQueue\drive\Connector;
use shiyunQueue\drive\IntfDrive;
use shiyunQueue\event\JobFailed;
use shiyunQueue\event\JobProcessed;
use shiyunQueue\event\JobProcessing;
use Throwable;
use shiyunQueue\drive\TraitConnect;
use shiyunQueue\drive\TraitChannel;
use shiyunQueue\drive\TraitMessage;

//  implements IntfDrive
class SyncConnector extends Connector
{
    use TraitConnect,
        TraitChannel,
        TraitMessage;

    public function size($queue = null)
    {
        return 0;
    }
    protected function resolveJob($payload, $queue)
    {
        return new SyncJob($this->app, $payload, $this->connectionName, $queue);
    }
    public function sendPublish()
    {
        if (!empty($this->msgDelay) && $this->msgDelay > 0) {
        } else { 
        $payload = $this->createPayload($job, $data);
        $queueJob = $this->resolveJob($payload, $queue);
        try {
            $this->app->event->trigger(new JobProcessing($this->connectionName, $job));
            $queueJob->onQueueMessage();
            $this->app->event->trigger(new JobProcessed($this->connectionName, $job));
        } catch (Exception | Throwable $e) {
            $this->app->event->trigger(new JobFailed($this->connectionName, $job, $e));
            throw $e;
        }
        return 0;
    }
}
