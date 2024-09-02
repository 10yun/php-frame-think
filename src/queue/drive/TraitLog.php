<?php

namespace shiyunQueue\drive;

use think\Container;

trait TraitLog
{
    /**
     * 记录日志
     */
    protected mixed $logData;
    public function addLog($log)
    {
        $this->logData = $log;
        return $this;
    }
    /**
     * 任务失败
     */
    public function addLogFailed(mixed $connection = null, $job = null, $e = null)
    {
        /**
         * 记录失败任务
         * 事件监听处理
         * 格式化队列工作者的状态输出
         */
        // $config = syGetConfig('queue.failed', []);
        // $type = \think\helper\Arr::pull($config, 'type', 'none');

        // $queueFailerObj = Container::getInstance()->invokeClass("\\shiyunQueue\\drive\\{$type}Failed::class", [$config]);
        // $queueFailerObj->addLog(
        //     $consumeQeObj,
        //     $job->getQueueName(),
        //     $job->getJobRawBody(),
        //     $e ?: new RuntimeException('ManuallyFailed'),
        //     'type' => 'error',
        //     'job_id' => $job->getJobId(),
        //     'job_name' => $job->getName(),
        //     'status' => 'Failed',
        //     'date' => date('Y-m-d H:i:s'),
        // );
    }
    /**
     * 任务完成
     */
    public function addLogComplete(mixed $connection = null, $job = null, $e = null)
    {
        // $logArr = [
        //     'type' => 'info',
        //     'job_id' => $job->getJobId(),
        //     'job_name' => $job->getName(),
        //     'status' =>  'Processed', // complete
        //     'date' => date('Y-m-d H:i:s'),
        // ];
    }
    public function clearLogSett()
    {
        $this->logData = null;
    }
}
