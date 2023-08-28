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
     * The queue message data.
     *
     * @var string
     */
    protected $job;

    public function __construct(App $app, $job, $connection, $queue)
    {
        $this->app        = $app;
        $this->connection = $connection;
        $this->queue      = $queue;
        $this->job        = $job;
    }

    /**
     * Get the number of times the job has been attempted.
     * @return int
     */
    public function getJobAttemptsNum()
    {
        return 1;
    }

    /**
     * Get the raw body string for the job.
     * @return string
     */
    public function getJobRawBody()
    {
        return $this->job;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return '';
    }

    public function getQueue()
    {
        return 'sync';
    }
}
