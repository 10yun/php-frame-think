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

use think\App;
use shiyunQueue\drive\Job;

class SyncJob extends Job
{
    /**
     * 队列消息数据
     */
    protected string $job;

    public function __construct(App $app, $job, $connection, $queue)
    {
        $this->app        = $app;
        $this->connection = $connection;
        $this->queue      = $queue;
        $this->job        = $job;
    }

    /**
     * 获取尝试作业的次数
     */
    public function getJobAttemptsNum(): int
    {
        return 1;
    }

    /**
     * 获取作业的原始正文字符串
     */
    public function getJobRawBody(): string
    {
        return $this->job;
    }

    /**
     * 获取作业标识符.
     */
    public function getJobId(): string
    {
        return '';
    }

    public function getQueue()
    {
        return 'sync';
    }
}
