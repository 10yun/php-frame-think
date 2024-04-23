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

namespace shiyunQueue\drive;

use Exception;
use think\App;
use think\helper\Arr;
use think\helper\Str;

abstract class Job
{

    /**
     * 作业处理程序实例
     */
    private mixed $instance;

    /**
     *  The JSON decoded version of "$job".
     */
    private array $payload;

    /**
     * @var App
     */
    protected $app;

    /**
     * 作业所属队列的名称
     */
    protected string $queue;

    /**
     * 作业所属连接的名称
     */
    protected  $connection;

    /**
     * 指示作业是否已删除
     */
    protected bool $deleted = false;

    /**
     * 指示作业是否已发布
     */
    protected bool $released = false;

    /**
     * 指示作业是否失败
     */
    protected bool $failed = false;

    /**
     * 解雇这份作业
     * @return void
     */
    public function onQueueMessage()
    {
        $instance = $this->getResolvedJob();
        [, $method] = $this->getParsedJob();
        $instance->{$method}($this, $this->payload('data'));
    }
    /**
     * 处理导致作业失败的异常
     * @param Exception $e
     * @return void
     */
    public function failed($e)
    {
        $instance = $this->getResolvedJob();
        if (method_exists($instance, 'failed')) {
            $instance->failed($this->payload('data'), $e);
        }
    }
    /**
     * Delete the job from the queue.
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }
    /**
     * Determine if the job has been deleted.
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Release the job back into the queue.
     * @param int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->released = true;
    }

    /**
     * Determine if the job was released back into the queue.
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * Determine if the job has been deleted or released.
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->isDeleted() || $this->isReleased();
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    abstract public function getJobId();

    /**
     * Get the number of times the job has been attempted.
     * @return int
     */
    abstract public function getJobAttemptsNum();

    /**
     * Get the raw body string for the job.
     * @return string
     */
    abstract public function getJobRawBody();

    /**
     * Parse the job declaration into class and method.
     * @return array
     */
    protected function getParsedJob()
    {
        $job_server = $this->payload('job_server');
        $segments = explode('@', $job_server);

        return count($segments) > 1 ? $segments : [$segments[0], 'fire'];
    }
    /**
     * Resolve the given job handler
     */
    public function getResolvedJob()
    {
        if (empty($this->instance)) {

            [$class] = $this->getParsedJob();

            // var_dump($class);
            $param  = $this->payload('data');

            switch ($class) {
                case 'redis':
                    $this->instance = $this->app->make(\shiyunQueue\drive\redis\RedisJob::class, [$param], true);
                    break;
                case 'database':
                    $this->instance = $this->app->make(\shiyunQueue\drive\database\DatabaseJob::class, [$param], true);
                    break;
                case 'rabbitmq':
                    // $this->instance = $this->app->make(\shiyunQueue\drive\database\Rabb::class, [$param], true);
                    break;
            }
        }
        return $this->instance;
    }

    /**
     * 确定作业是否已标记为失败
     * @return bool
     */
    public function hasFailedState()
    {
        return $this->failed;
    }
    /**
     * 将作业标记为“失败”
     * @return void
     */
    public function setFailedState()
    {
        $this->failed = true;
    }

    /**
     * 获取尝试某个任务的次数。
     * @return int|null
     */
    public function maxTries()
    {
        return $this->payload('maxTries');
    }

    /**
     * 获取作业可以运行的秒数
     * @return int|null
     */
    public function timeout()
    {
        return $this->payload('timeout');
    }

    /**
     * 获取超市参数的时间戳。
     * @return int|null
     */
    public function getTimeoutAt()
    {
        return $this->payload('timeoutAt');
    }
    /**
     * 获取排队作业类的名称
     * @return string
     */
    public function getName()
    {
        return $this->payload('job');
    }
    /**
     * 获取作业所属队列的名称
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }
    /**
     * 获取作业的解码正文
     * @return mixed
     */
    public function payload($name = null, $default = null)
    {
        if (empty($this->payload)) {
            $this->payload = json_decode($this->getJobRawBody(), true);
        }
        // if (!empty($consumeJobMsg) && json_validate($consumeJobMsg)) {
        //     $consumeJobMsg = json_decode($consumeJobMsg, true);
        // }
        // /**
        //  * 数据是否加密
        //  */
        // $encrypt = !empty($consumeJobMsg['encrypt']) ? $consumeJobMsg['encrypt'] : false;
        // $excuteData = !empty($consumeJobMsg['data']) ? $consumeJobMsg['data'] : []; //执行数据
        // if ($encrypt === true) {
        //     // $xxxx = \shiyunUtils\libs\LibEncryptArr::encrypt($jobData);
        //     $excuteData = \shiyunUtils\libs\LibEncryptArr::decrypt($excuteData);
        // }
        // if (!empty($infoData) && json_validate($excuteData)) {
        //     $excuteData = json_decode($excuteData, true);
        // }

        if (empty($name)) {
            return $this->payload;
        }
        return Arr::get($this->payload, $name, $default);
    }
}
