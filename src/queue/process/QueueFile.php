<?php

namespace shiyunQueue\process;

use shiyunWorker\WorkermanServer;

/**
 * 常规job的方式
 */
class QueueFile
{
    protected $processes    = 1;
    protected $socket       = 'tcp://0.0.0.0:1605';
    protected $workerName   = 'queue_file';

    public function onWorkerStart()
    {
        $excuteFunc = !empty($consumeJobMsg['jobFunc']) ? $consumeJobMsg['jobFunc'] : 'onQueueMessage';
        if (!method_exists($classObj, $excuteFunc)) {
            echo  "执行方法不存在\n";
            return;
        }
    }
}
