<?php

namespace shiyunQueue\event;

use shiyunQueue\drive\Job;

class JobFailed
{
    /** @var string */
    public $connection;
    /** @var Job */
    public $job;
    /** @var \Exception */
    public $exception;
    public function __construct($connection = null, $job = null, $exception = null)
    {
        $this->connection = $connection;
        $this->job        = $job;
        $this->exception  = $exception;
    }
    // 事件监听处理
    // 格式化队列工作者的状态输出。
    public function handle($eve_data)
    {
        // $logArr = [
        //     'type' => 'error',
        //     'job_id' => $this->job->getJobId(),
        //     'job_name' => $this->job->getName(),
        //     'status' => 'Failed',
        //     'date' => date('Y-m-d H:i:s'),
        // ];
        // $this->logFailedJob();
    }
    /**
     * 记录失败任务
     * @param JobFailed $event
     */
    protected function logFailedJob()
    {
        // $this->app['queue_failer']->addLog(
        app('queue_failer')->addLog(
            $this->connection,
            $this->job->getQueue(),
            $this->job->getJobRawBody(),
            $this->exception
        );
    }
}
