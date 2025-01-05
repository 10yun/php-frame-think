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

namespace shiyunQueue\drive\redis;

use shiyunQueue\drive\Job;

class RedisJob extends Job
{
    /**
     * The redis queue instance.
     * @var RedisConnector
     */
    protected $redis;

    /**
     * The database job payload.
     * @var Object
     */
    protected $job;

    /**
     * The Redis job payload inside the reserved queue.
     */
    protected mixed $reserved;

    public function __construct(RedisConnector $redis, $job, $reserved, $connection = '', $queue = '', $exchange = '')
    {
        $this->redis      = $redis;
        $this->job        = $job;
        $this->reserved   = $reserved;
        $this->connection = $connection;
        $this->queue      = $queue;
        $this->exchange   = $exchange;
    }

    /**
     * Get the number of times the job has been attempted.
     * 获取当前任务尝试次数
     * @return int
     */
    public function getJobAttemptsNum()
    {
        return $this->payload('attempts') + 1;
    }

    /**
     * 删除任务
     *
     * @return void
     */
    public function delete($eName = '', $qName = '')
    {
        parent::delete();
        $this->redis->setExchangeName($this->exchange);
        // $this->redis->setQueueName($this->queue);
        $this->redis->deleteReserved($this->queue, $this);
        // $this->redis->deleteReserved(null, $this);
    }

    /**
     * 重新发布任务
     * @param int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
        $this->redis->setExchangeName($this->exchange);
        $this->redis->deleteAndRelease($this->queue, $this, $delay);
    }

    /**
     * 获取job标识符
     * @return string
     */
    public function getJobId()
    {
        return $this->payload('id');
    }
    /**
     * 获取底层预留的Redis作业
     * @return string
     */
    public function getJobReserved()
    {
        return $this->reserved;
    }
    /**
     * 获取job的数据
     * @return string
     */
    public function getJobRawBody()
    {
        return $this->job;
    }
}
