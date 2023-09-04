<?php

namespace shiyunQueue\drive\sync;

use Exception;
use shiyunQueue\drive\Connector;
use shiyunQueue\drive\IntfDrive;
use shiyunQueue\libs\JobFailed;
use shiyunQueue\libs\JobProcessed;
use shiyunQueue\libs\JobProcessing;
use Throwable;

//  implements IntfDrive
class SyncConnector extends Connector
{
    protected $connectHandle;


    public function createConnect()
    {
        $this->connectHandle = '';
    }

    public function size($queue = null)
    {
        return 0;
    }
    /**
     * 重新发布
     */
    public function retryPublish($payload, $queue = null, array $options = [])
    {
        // if ($this->connectHandle->rPush($this->getQueue($queue), $payload)) {
        //     return json_decode($payload, true)['id'] ?? null;
        // }
    }
    public function sendPublish()
    {
        if (!empty($this->msgDelay) && $this->msgDelay > 0) {
        } else {
            $payload = $this->createPayload($job, $data);
            $queueJob = new SyncJob($this->app, $payload, $this->connectionName, $queue);
        }
        try {
            $jobObj = new JobProcessing($this->connectionName, $job);
            $jobObj->handle();
            $queueJob->onQueueMessage();
            $jobObj = new JobProcessed($this->connectionName, $job);
            $jobObj->handle();
        } catch (Exception | Throwable $e) {
            $jobObj = new JobFailed($this->connectionName, $job, $e);
            $jobObj->handle();
            throw $e;
        }
        return 0;
    }
}
