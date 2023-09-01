<?php

namespace shiyunQueue;

use stdClass;

interface IntfQueueConsumer
{
    /**
     * 队列消费
     */
    public function onQueueMessage($data);
    /**
     * 处理错误
     */
    public function onQueueError();
}
