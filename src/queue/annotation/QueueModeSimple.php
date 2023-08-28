<?php

namespace shiyun\queue\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class QueueModeSimple
{
    public function __construct(
        string $connect_name = '', // 连接名称
        string $exchange_name = '', // 交换机名称
        string $queue_name = '', // 队列名称
        int $execute_timing = 0, // 执行时间,0：执行一次，n：每n秒执行一次
        int $execute_timeout = 0, // 执行超时
        /**
         * 
         */
        string $connection = '', // 要工作的队列连接的名称
        string $queueName  = '', // 排队收听
        string $once       = null, // 只处理队列上的下一个作业 
        int    $delay      = 0, // 延迟失败作业的时间
        int    $force      = null, // 强制worker即使在维护模式下也要运行
        int    $memory     = 128, // 内存限制(兆字节)
        int    $timeout    = 60, // 子进程可以运行的秒数
        int    $sleep      = 3, // 当没有可用作业时休眠的秒数
        int    $maxTries,
        int    $tries      = 0, // 在记录作业失败之前尝试作业的次数
    ) {
    }
}
