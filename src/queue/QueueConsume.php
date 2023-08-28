<?php

namespace shiyunQueue\drive;

use stdClass;

interface QueueConsume
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
