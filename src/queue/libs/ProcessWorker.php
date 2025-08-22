<?php

namespace shiyunQueue\libs;

use Throwable;
use Exception;
use RuntimeException;
use shiyun\support\Cache;
use think\exception\Handle as ExceptHandle;
use shiyunQueue\drive\Connector;
use shiyunQueue\drive\Job;
use shiyunQueue\exception\MaxAttemptsExceededException;
use shiyunQueue\exception\TimeoutException;
use shiyunQueue\exception\QueueException;
use shiyunQueue\exception\JobObjNoMoreException;
use shiyunQueue\exception\JobMsgNoMoreException;
use shiyunQueue\exception\JobFailedException;
use shiyunQueue\libs\JobExceptionOccurred;
use shiyunQueue\libs\ProcessStopping;
use shiyun\libs\LibLogger;

trait ProcessWorker
{
    /** @var Cache */
    protected $cache;
    /**
     * 指示工作是否应退出
     */
    public bool $procShouldQuit = false;
    /**
     * 指示工作进程是否已暂停。
     */
    public bool $procPaused = false;
    /**
     * 开启debug调试信息
     */
    public bool $procDebugInfo = true;
    /**
     * 工作状态（用来存储工作状态，不然会一个任务重复启动）
     */
    public array $jobState = [];
    /**
     * ①、②、③、④、⑤、⑥、⑦、⑧、⑨、⑩、
     * ⑪、⑫、⑬、⑭、⑮、⑯、⑰、⑱、⑲、⑳、
     * ㉑、㉒、㉓、㉔、㉕、㉖、㉗、㉘、㉙、㉚
     */

    protected $item_logs = "";
    /**
     * 获取下个任务
     * @param Job $consumeJobObj
     */
    protected function dealItemData($consumeClassObj, $consumeClassOpt, $consumeQeObj)
    {
        $tryState = true;
        $this->item_logs = '';

        try {
            /**
             * @var Connector $consumeQeObj
             * @var Job $consumeJobObj
             */
            $consumeJobObj = $consumeQeObj
                ->setExchangeName($consumeClassOpt['exchange_name'])
                ->setQueueName($consumeClassOpt['queue_name'])
                ->getPublish();

            // if ($consumeClassOpt['queue_name'] == 'QueueCicdStart') {
            //     var_dump(
            //         '---$consumeJobObj----',
            //         $consumeJobObj
            //     );
            //     // var_dump($consumeJobObj->getJobRawBody(), $consumeJobObj->getJobReserved(), $consumeJobObj->payload());
            //     var_dump($consumeJobObj->payload());
            // }

            if (empty($consumeJobObj) || is_null($consumeJobObj)) {
                throw new JobObjNoMoreException($consumeClassOpt['queue_name'] . ' 队列对象，没有更多');
            }
            $consumeJobMsg = $consumeJobObj->payload();

            if (empty($consumeJobMsg)) {
                throw new JobMsgNoMoreException($consumeClassOpt['queue_name'] . ' 队列消息，没有更多');
            }
            $class_name = get_class($consumeClassObj);
            $job_id = $consumeJobObj->getJobId();
            $job_name = $consumeJobObj->getName();

            if (!empty($this->jobState[$job_id]) && $this->jobState[$job_id] == 'ing') {
                throw new JobMsgNoMoreException($consumeClassOpt['queue_name'] . ' 队列消息，在进行中');
            }
            $this->dealEchoDebug($class_name, '① 开始', '');

            /**
             * 获取任务配置参数
             */

            // 最大错误次数 20
            $errorMaxAllow = $consumeJobObj->payload('allowError', 0);
            // 允许最大的重试 20
            $jobTriesMax = $consumeJobObj->payload('maxTries');
            $_last_TriesMax = !is_null($jobTriesMax) ? $jobTriesMax : (int) ($consumeClassOpt['tries_max'] ?? 0);
            // 已经重试
            $_last_TriesAttempts = $consumeJobObj->getJobAttemptsNum();
            // 允许超时
            $_last_TimeoutAt = $consumeJobObj->getTimeoutAt();
            // 最大的超时 20
            $_last_TimeoutMax = $consumeClassOpt['timeout_max'] ?? 0;


            /**
             * 判断环境
             */
            if ($this->supportsAsyncSignals()) {
                $this->listenForSignals();
                // 注册超时
                $this->registerTimeoutHandler($consumeJobObj, $_last_TimeoutMax);
            }
            /**
             * ② 参数判断
             * 有些任务在到达消费者时，可能已经不再需要执行了
             * 任务名
             */
            $this->dealEchoDebug($class_name, '② 参数判断', '');
            if ($consumeJobObj->hasFailedState()) {
                throw new JobFailedException($job_name . ' 已错误');
            }

            // 当前时间戳
            $currTime = (new \DateTime())->getTimestamp();
            /**
             * 是否超时
             */
            if ($_last_TimeoutAt && $currTime <= $_last_TimeoutAt) {
                throw new TimeoutException($job_name . ' 作业可能已超时');
            }
            /**
             * 超过重试次数
             * 如果【已经】超过最大尝试次数，则将作业标记为失败
             */
            if (!empty($_last_TriesMax) || $_last_TriesAttempts <= $_last_TriesMax) {
                throw new MaxAttemptsExceededException($job_name . ' 尝试次数过多或运行时间过长');
            }
            /**
             * 如果【即将】超过最大尝试次数，则将作业标记为失败
             */

            // if ($_last_TimeoutAt && $_last_TimeoutAt <= $currTime) {
            //     $this->failJob($consumeQeObj, $consumeJobObj, $e);
            // }
            // if ($_last_TriesMax > 0 && $_last_TriesAttempts >= $_last_TriesMax) {
            //     $this->failJob($consumeQeObj, $consumeJobObj, $e);
            // }


            /**
             * 执行事件-进行中
             * 执行
             */
            $this->dealEchoDebug($class_name, '③ 执行回调事件 onQueueMessage', '');
            $this->jobState[$job_id] = 'ing';

            $queueJobRes = \call_user_func(
                [$consumeClassObj, 'onQueueMessage'],
                $consumeJobMsg,
                $consumeJobObj
            );
            // var_dump($consumeJobObj->getResolvedJob());

            /**
             * 执行事件-已完成
             */
            $this->dealEchoDebug($class_name, '④ 执行事件', 'JobComplete 开始...');
            $consumeQeObj->addLogComplete($consumeQeObj, $consumeJobObj);

            $this->dealEchoDebug($class_name, '⑤ 是否重启', '');
            $this->stopIfNecessary($consumeJobObj);

            $this->dealEchoDebug($class_name, "⑥ 队列结果 {$consumeClassOpt['exchange_name']} {$consumeClassOpt['queue_name']}", $queueJobRes);

            // var_dump("{$consumeClassOpt['exchange_name']} {$consumeClassOpt['queue_name']} => {$queueJobRes}");

            if ($queueJobRes) {
                $this->dealEchoDebug($class_name, '⑦ 成功-删除任务、记录日志', '');

                $consumeJobObj->delete();
                $this->dealItemLog($consumeClassObj, $consumeJobMsg);
                if (!empty($this->jobState[$job_id]) && $this->jobState[$job_id] == 'ing') {
                    unset($this->jobState[$job_id]);
                }
            } else {
                $currJobAttemptsNum  = $consumeJobObj->getJobAttemptsNum();
                if ($currJobAttemptsNum >= $errorMaxAllow && $errorMaxAllow) {
                    /**
                     * 超过重试次数
                     */
                    $this->dealEchoDebug($class_name, '⑦ 失败-超过重试次数 ' . $currJobAttemptsNum, '');

                    $consumeJobObj->delete();
                    $this->dealItemLog($consumeClassObj, $consumeJobMsg);
                    if (!empty($this->jobState[$job_id])) {
                        unset($this->jobState[$job_id]);
                    }
                } else {
                    /**
                     * 从新放入队列
                     */
                    $this->dealEchoDebug($class_name, '⑦ 失败-重试', '');
                    $consumeJobObj->release();
                    $this->jobState[$job_id] = 'retry';
                    // if (!empty($this->jobState[$job_id]) && $this->jobState[$job_id] == 'ing') {
                    //     unset($this->jobState[$job_id]);
                    // }
                }
            }
        } catch (MaxAttemptsExceededException $e) {
            $tryState = false;
            $this->queueLogError('dealItemData__MaxAttemptsExceededException', $e->getMessage());
        } catch (TimeoutException $e) {
            //  $this->failJob($consumeQeObj, $consumeJobObj, $e);
            $tryState = false;
            $this->queueLogError('dealItemData__TimeoutException', $e->getMessage());
        } catch (JobObjNoMoreException $e) {
            $tryState = false;
            // $this->queueLogError('JobObjNoMoreException', $e->getMessage());
        } catch (JobFailedException $e) {
            // (new JobExceptionOccurred($consumeQeObj, $consumeJobObj, $e))->handle();
            // // throw $e;
            // $this->sleep(1);         
            $tryState = false;
            $this->queueLogError('dealItemData__JobFailedException', $e->getMessage());
        } catch (Exception | Throwable $e) {
            $tryState = false;
            $this->queueLogError('dealItemData__Exception', $e->getMessage());
        } finally {
            if (method_exists($consumeClassObj, 'onQueueEnd')) {
                \call_user_func(
                    [$consumeClassObj, 'onQueueEnd'],
                    $consumeJobMsg,
                    $consumeJobObj
                );
            }
            $this->item_logs = trim($this->item_logs);
            if (!empty($this->item_logs)) {
                LibLogger::getInstance()->setGroup('queue_redis')->writeInfo($this->item_logs);
            }
            return $tryState;
            // if (!$consumeJobObj->isDeleted() && !$consumeJobObj->isReleased() && !$consumeJobObj->hasFailedState()) {
            //     $consumeJobObj->release($delay);
            // }
        }
    }
    /**
     * 任务失败
     * @param string    $consumeQeObj
     * @param Job       $job
     * @param Exception $e
     */
    // public function jobFailed() {}
    protected function failJob($consumeQeObj, $job, Exception $e)
    {
        // 标记为错误
        $job->setFailedState();
        // 判断是否删除
        if ($job->isDeleted()) {
            return;
        }
        try {
            $job->delete();
            $job->failed($e);
        } finally {
            // $consumeQeObj->addLogFailed($consumeQeObj, $job, $e);
        }
    }
    /**
     * debug调试
     */
    protected function dealEchoDebug($msg1 = '', $msg2 = '', mixed $msg3 = '')
    {
        if ($this->procDebugInfo) {
            $log = str_pad($msg1, 50, " ")
                . str_pad($msg2, 20, " ")
                . " {$msg3} \n";
            $this->item_logs .= $log;
        }
    }
    /**
     * 记录日志
     */
    protected function dealItemLog($consumeClassObj, $consumeJobMsg = null)
    {
        if (method_exists($consumeClassObj, 'onQueueLog')) {
            \call_user_func([$consumeClassObj, 'onQueueLog'], $consumeJobMsg);
        }
    }
    /**
     * 确定队列工作程序是否应重新启动
     */
    protected function stopIfNecessary($job)
    {
        // 获取内存
        $memory = 128;
        // 获取队列重启时间
        $lastRestartTimer = null;
        $cacheRestartTimer = null;
        $isRestart = false;
        if (!empty($this->cache)) {
            // $cacheRestartTimer = $this->cache->get('shiyun:queue:restart');
        }
        $isRestart = $cacheRestartTimer != $lastRestartTimer;
        if ($this->procShouldQuit || $isRestart) {
            $this->stopProcess();
        } elseif ($this->memoryExceeded($memory)) {
            \Workerman\Worker::stopAll();
            $this->stopProcess(12);
        }
    }
    /**
     * 确定是否已超过内存限制。
     * @param int $memoryLimit
     * @return bool
     */
    protected function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $memoryLimit;
    }
    /**
     * Register the worker timeout handler.
     * 注册工作超时处理程序。
     * @param Job|null $jobObj
     * @param int      $timeout
     * @return void
     */
    protected function registerTimeoutHandler($jobObj, $timeout)
    {
        pcntl_signal(SIGALRM, function () {
            $this->killProcess(1);
        });
        // 为给定的作业获取适当的超时。
        $giveTimeroutForJob = $jobObj && !is_null($jobObj->timeout()) ? $jobObj->timeout() : $timeout;
        pcntl_alarm(
            max($giveTimeroutForJob, 0)
        );
    }
    /**
     * Stop listening and bail out of the script.
     * 停止监听，退出脚本。
     * @param int $status
     * @return void
     */
    public function stopProcess($status = 0)
    {
        // (new ProcessStopping($status))->handle();
        exit($status);
        die;
    }

    /**
     * Kill the process.
     * 终止进程。
     * @param int $status
     * @return void
     */
    public function killProcess($status = 0)
    {
        /**
         * 终止进程。
         */
        // new ProcessStopping($status)->hanlde();
        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }
        exit($status);
    }
    /**
     * Determine if "async" signals are supported.
     * 确定是否支持“异步”信号。
     * @return bool
     */
    protected function supportsAsyncSignals()
    {
        return extension_loaded('pcntl');
    }
    /**
     * Enable async signals for the process.
     * 为进程启用异步信号。
     * @return void
     */
    protected function listenForSignals()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function () {
            $this->procShouldQuit = true;
        });
        pcntl_signal(SIGUSR2, function () {
            $this->procPaused = true;
        });
        pcntl_signal(SIGCONT, function () {
            $this->procPaused = false;
        });
    }
    /**
     * Sleep the script for a given number of seconds.
     * 将脚本休眠给定的秒数。
     * @param int $seconds
     * @return void
     */
    public function sleep($seconds)
    {
        if ($seconds < 1) {
            usleep($seconds * 1000000);
        } else {
            sleep($seconds);
        }
    }
}
