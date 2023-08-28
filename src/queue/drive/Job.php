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
     * The job handler instance.
     * @var object
     */
    private $instance;

    /**
     *  The JSON decoded version of "$job".
     * @var array
     */
    private $payload;

    /**
     * @var App
     */
    protected $app;

    /**
     * The name of the queue the job belongs to.
     * @var string
     */
    protected $queue;

    /**
     * The name of the connection the job belongs to.
     */
    protected $connection;

    /**
     * Indicates if the job has been deleted.
     * @var bool
     */
    protected $deleted = false;

    /**
     * Indicates if the job has been released.
     * @var bool
     */
    protected $released = false;

    /**
     * Indicates if the job has failed.
     *
     * @var bool
     */
    protected $failed = false;

    /**
     * Fire the job.
     * @return void
     */
    public function onQueueMessage()
    {
        $instance = $this->getResolvedJob();
        [, $method] = $this->getParsedJob();
        $instance->{$method}($this, $this->payload('data'));
    }
    /**
     * Process an exception that caused the job to fail.
     *
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
     * Resolve the given job handler.
     * @param string $name
     * @return mixed
     */
    protected function resolve($name, $param)
    {
        // $namespace = $this->app->getNamespace() . '\\job\\';
        $namespace = '\\shiyunQueue\\drive\\';

        $class = false !== strpos($name, '\\') ? $name : $namespace . $name . "\\" . Str::studly($name) . "Job";
        return $this->app->make($class, [$param], true);
    }

    public function getResolvedJob()
    {
        if (empty($this->instance)) {
            [$class] = $this->getParsedJob();
            $this->instance = $this->resolve($class, $this->payload('data'));
        }
        return $this->instance;
    }

    /**
     * Determine if the job has been marked as a failure.
     * 确定作业是否已标记为失败
     * @return bool
     */
    public function hasFailedState()
    {
        return $this->failed;
    }
    /**
     * Mark the job as "failed".
     * 将作业标记为“失败”
     * @return void
     */
    public function setFailedState()
    {
        $this->failed = true;
    }

    /**
     * Get the number of times to attempt a job.
     * 获取尝试某个任务的次数。
     * @return int|null
     */
    public function maxTries()
    {
        return $this->payload('maxTries');
    }

    /**
     * Get the number of seconds the job can run.
     *
     * @return int|null
     */
    public function timeout()
    {
        return $this->payload('timeout');
    }

    /**
     * Get the timestamp indicating when the job should timeout.
     * 获取超市参数的时间戳。
     * @return int|null
     */
    public function getTimeoutAt()
    {
        return $this->payload('timeoutAt');
    }
    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        return $this->payload('job');
    }
    /**
     * Get the name of the queue the job belongs to.
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }
    /**
     * Get the decoded body of the job.
     *
     * @return mixed
     */
    public function payload($name = null, $default = null)
    {
        if (empty($this->payload)) {
            $this->payload = json_decode($this->getJobRawBody(), true);
        }
        // if (!empty($consumeJobMsg) && \shiyunUtils\helper\HelperType::isJson($consumeJobMsg)) {
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
        // if (!empty($infoData) && \shiyunUtils\helper\HelperType::isJson($excuteData)) {
        //     $excuteData = json_decode($excuteData, true);
        // }

        if (empty($name)) {
            return $this->payload;
        }
        return Arr::get($this->payload, $name, $default);
    }
}
