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
namespace shiyunQueue\drive\database;

use shiyunQueue\drive\Job;

class DatabaseJob extends Job
{
    /**
     * The database queue instance.
     * @var DatabaseQueue
     */
    protected $database;

    /**
     * The database job payload.
     * @var Object
     */
    protected $job;

    public function __construct(DatabaseConnector $database, $job, $connection, $queue)
    {
        $this->job        = $job;
        $this->queue      = $queue;
        $this->database   = $database;
        $this->connection = $connection;
    }

    /**
     * 删除任务
     * @return void
     */
    public function delete()
    {
        parent::delete();
        $this->database->deleteReserved($this->job->id);
    }
    /**
     * 重新发布任务
     * @param int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
        $this->delete();
        $this->database->release($this->queue, $this->job, $delay);
    }
    /**
     * 获取当前任务尝试次数
     * @return int
     */
    public function getJobAttemptsNum()
    {
        return (int) $this->job->attempts;
    }
    /**
     * Get the raw body string for the job.
     * @return string
     */
    public function getJobRawBody()
    {
        return $this->job->payload;
    }
    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->id;
    }
}
