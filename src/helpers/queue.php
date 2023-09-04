<?php

use shiyun\support\Log;

/**
 * 加载队列生产者
 */
if (!function_exists('queue_producer')) {
    /**
     * 添加到队列
     * @param string        $connectName   连接名称
     * @param string        $exchangeName  交换机名称
     * @param string|array  $queueName     队列名称
     * @param string|array  $msg           队列数据
     * @param int           $delay         延迟时间
     */
    function queue_producer(
        string $connectName,
        string $exchangeName = '',
        string|array $queueName = null,
        string|array|int $msg = null,
        int $delay = 0
    ) {
        \shiyunQueue\QueueFactory::getInstance()
            ->connection($connectName)
            ->setConnectName($connectName)
            ->setExchangeName($exchangeName)
            ->setQueueName($queueName)
            ->addMessage($msg)
            ->setMsgDelay($delay)
            ->setMsgEncrypt(false)
            ->sendPublish();
    }
}
/**
 * 响应错误
 */
function queue_resp_error(string $msg = '')
{
    echo "{$msg} \n";
    return false;
}
/**
 * 响应队列成功
 */
function queue_resp_succ(string $msg = '')
{
    echo "{$msg} \n";
    return true;
}

function frameLogsQueue($info = '')
{
    if (is_string($info)) {
        Log::channel('logs_channel_queue')->write($info);
    } else if (is_array($info)) {
        Log::channel('logs_channel_queue')->info($info);
    } else if (is_object($info)) {
        Log::channel('logs_channel_queue')->info($info);
    }
}
