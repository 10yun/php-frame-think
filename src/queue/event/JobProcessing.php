<?php

namespace shiyunQueue\event;

// use shiyunQueue\drive\Job;

class JobProcessing
{
    // /** @var string */
    public $connection = null;
    // /** @var Job */
    public $job = null;

    public function __construct($connection = null, $job = null)
    {
        $this->connection = $connection;
        $this->job        = $job;
    }
    // 事件监听处理
    // 格式化队列工作者的状态输出。
    public function handle($data = [])
    {
        // echo __CLASS__ . " handle  \n";
        //     // $this->connection->addMessage([
        //     //     'aaaa' => 1,
        //     //     'bbbb' => 2,
        //     // ])->sendPublish();

        //     // echo '12312313--handle';
        //     // $logArr = [
        //     //     'type' => 'comment',
        //     //     'job_id' => $this->job->getJobId(),
        //     //     'job_name' => $this->job->getName(),
        //     //     'status' => 'Processing',
        //     //     'date' => date('Y-m-d H:i:s'),
        //     // ];
    }
}
