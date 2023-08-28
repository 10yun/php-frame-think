<?php

namespace shiyunQueue\event;

use shiyunQueue\drive\Job;

class JobProcessed
{
    /** @var string */
    public $connection;

    /** @var Job */
    public $job;

    public function __construct($connection = null, $job = null)
    {
        $this->connection = $connection;
        $this->job        = $job;
    }
    // 事件监听处理
    // 格式化队列工作者的状态输出。
    public function handle($eve_data = [])
    {
        // $logArr = [
        //     'type' => 'info',
        //     'job_id' => $this->job->getJobId(),
        //     'job_name' => $this->job->getName(),
        //     'status' =>  'Processed',
        //     'date' => date('Y-m-d H:i:s'),
        // ];
    }
}
