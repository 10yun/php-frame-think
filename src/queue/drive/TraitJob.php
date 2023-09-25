<?php

namespace shiyunQueue\drive;

use think\Cache;
use shiyunQueue\libs\InteractsWithTime;

/**
 * @method $this setJobServer(string $job)     设置任务执行类名
 * @method $this setJobFunc(string $do)        设置任务执行方法
 */
trait TraitJob
{
    use InteractsWithTime;
    /**
     * 任务类名
     */
    protected string $jobServer = '';
    /**
     * 任务执行方法
     */
    protected string $jobFunc = 'onQueueMessage';
    /**
     * 默认任务执行方法名
     */
    protected string $_defaultDo = '';

    // 初始化设置
    public function initJobSett()
    {
        $this->_defaultDo = $this->jobFunc;
    }
    // 清除设置
    public function clearJobSett()
    {
        $this->jobFunc = $this->_defaultDo;
    }
    /**
     * 设置job服务
     */
    public function setJobServer($jobServer)
    {
        $this->jobServer = $jobServer;
        return $this;
    }
    /**
     * 设置job服务
     */
    public function setJobFunc($jobFunc = '')
    {
        $this->jobFunc = $jobFunc;
        return $this;
    }
    /**
     * 重试job单个
     */
    public function retryJobId($id)
    {
        $ids = (array) $id;
        $this->retryJobIds($ids);
    }
    /**
     * 重试job所有
     */
    public function retryJobAll()
    {
        $config = $this->app->config->get('queue.failed', []);
        $type = \think\helper\Arr::pull($config, 'type', 'none');
        $queueFailerObj = $this->app->invokeClass("\\shiyunQueue\\drive\\{$type}Failed::class", [$config]);

        $ids = \think\helper\Arr::pluck($queueFailerObj->all(), 'id');
        $this->retryJobIds($ids);
    }
    protected function retryJobIds($ids)
    {
        foreach ($ids as $id) {
            $config = $this->app->config->get('queue.failed', []);
            $type = \think\helper\Arr::pull($config, 'type', 'none');
            $queueFailerObj = $this->app->invokeClass("\\shiyunQueue\\drive\\{$type}Failed::class", [$config]);

            $job = $queueFailerObj->find($id);
            if (is_null($job)) {
                return  '无法用ID找到失败的作业';
            } else {
                $this->retryJobItem($job);
                // 失败的作业[{$id}]已被推回队列     
                return '失败的作业[{$id}]已被推回队列';
                $config = $this->app->config->get('queue.failed', []);
                $type = \think\helper\Arr::pull($config, 'type', 'none');
                $queueFailerObj = $this->app->invokeClass("\\shiyunQueue\\drive\\{$type}Failed::class", [$config]);

                $queueFailerObj->forget($id);
            }
        }
    }
    /**
     * Retry the queue job.
     * 重试队列作业。
     * @param stdClass $job
     * @return void
     */
    protected function retryJobItem($job)
    {
        $this->retryPublish(
            $this->resetAttempts($job['payload']),
            $job['queue']
        );
    }
    /**
     * Reset the payload attempts.
     * 重置有效负载尝试
     * Applicable to Redis jobs which store attempts in their payload.
     * 适用于Redis的工作，在他们的有效载荷存储尝试
     * @param string $payload
     * @return string
     */
    protected function resetAttempts($payload)
    {
        $payload = json_decode($payload, true);
        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }
        return json_encode($payload);
    }

    /**
     * 在队列工作守护进程的当前作业之后重新启动它们
     */
    public function restartJobQueue(Cache $cache)
    {
        // 广播队列重启信号。
        $cache->set('think:queue:restart', $this->currentTime());
    }
}
