<?php

namespace shiyunQueue\drive\sync;

use Exception;
use shiyunQueue\drive\Connector;
use Throwable;

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
        // if ($this->connectHandle->rPush($this->getPrefixQueue($queue), $payload)) {
        //     return json_decode($payload, true)['id'] ?? null;
        // }
    }
    public function sendPublish(?array $msg = null)
    {
        if (!empty($msg)) {
            $this->addMessage($msg);
        }
        $job = null;
        $payload = $this->createPayload($job);
        if (!empty($this->msgDelay) && $this->msgDelay > 0) {
        } else {
            $queueJob = new SyncJob($payload, $this->connectionName, $queue);
        }
        try {
            // $this->addMessage([
            //     'aaaa' => 1,
            //     'bbbb' => 2,
            // ]);
            //     // $logArr = [
            //     //     'type' => 'comment',
            //     //     'job_id' => $job->getJobId(),
            //     //     'job_name' => $job->getName(),
            //     //     'status' => 'Processing',
            //     //     'date' => date('Y-m-d H:i:s'),
            //     // ];
            $queueJob->onQueueMessage();
            $this->addLogComplete($this->connectionName, $job);
        } catch (Exception | Throwable $e) {
            $this->addLogFailed($this->connectionName, $job, $e);
            throw $e;
        }
        return 0;
    }
}
