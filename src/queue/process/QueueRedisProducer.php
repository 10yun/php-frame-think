<?php

namespace shiyunQueue\process;

use shiyunWorker\WorkermanServer;

class QueueRedisProducer
{
    // 启动4个进程对外提供服务
    protected $processes    = 1;
    // 创建一个Worker监听2345端口，使用http协议通讯
    protected $socket       = 'tcp://0.0.0.0:2345';
    protected $workerName   = 'queue_redis_Producer';

    public function onMessage($connection, $request)
    {
        $data = $request->get();
        // $result = sendPublish();
        $result = ''; // true | false
        //接收到信息立即返回回应
        $connection->send([
            'result' => $result
        ]);
    }
}
