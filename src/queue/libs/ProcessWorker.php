<?php

namespace shiyunQueue\libs;

use Throwable;
use Exception;
use RuntimeException;
use shiyun\support\Cache;
use think\exception\Handle as ExceptHandle;
use shiyunQueue\drive\Job;
use shiyunQueue\exception\MaxAttemptsExceededException;
use shiyunQueue\exception\TimeoutException;
use shiyunQueue\exception\MethodNotException;
use shiyunQueue\exception\JobObjNoMoreException;
use shiyunQueue\exception\JobMsgNoMoreException;
use shiyunQueue\exception\JobFailedException;
use shiyunQueue\libs\JobExceptionOccurred;
use shiyunQueue\libs\JobFailed;
use shiyunQueue\libs\JobProcessed;
use shiyunQueue\libs\JobProcessing;
use shiyunQueue\libs\WorkerStopping;

trait ProcessWorker
{
    /** @var ExceptHandle */
    protected $exceptHandle;
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
    public bool $procDebugInfo = false;

    public function dealInit($consumeClassOpt = [])
    {
        // $this->exceptHandle = new ExceptHandle();
        // $this->cache = app('cache');
    }
    protected function dealItemCheck($file)
    {
        try {
            //code...
            $fileinfo = new \SplFileInfo($file);
            $ext = $fileinfo->getExtension();
            if ($ext === 'php') {
                $pathname = $fileinfo->getPathname();
                $class = str_replace(".php", "", $pathname);
                $class = str_replace('/', "\\", $class);
                // echo substr($file, strlen(_PATH_PROJECT_)) . "\n";
                // $class = substr(substr($file, strlen(_PATH_PROJECT_)), 0, -4);
                // $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, _PATH_PROJECT_);
                // $class = substr($pathname, strlen($filePath) + 0, -4);
                if (!class_exists($class)) {
                    echo str_pad($class, 50, " ")
                        . str_pad('class', 20, " ")
                        . " 不存在\n";
                    // throw new Exception('class不存在');
                    return false;
                }
                // 消费者参数
                $consumeClassOpt = [];
                // 消费者类
                $consumeClassObj = new $class;
                $reflectionClass = new \ReflectionClass($class);
                $properties = $reflectionClass->getProperties();


                foreach ($properties as $property) {
                    $pro_key       = $property->getName();
                    $pro_val       = $property->getValue($consumeClassObj);
                    $consumeClassOpt[$pro_key] = $pro_val;
                }
                /**
                 * 方法是否存在
                 */
                if (!method_exists($consumeClassObj, 'onQueueMessage')) {
                    echo str_pad($class, 50, " ")
                        . str_pad('onQueueMessage', 20, " ")
                        . " 执行方法不存在\n";
                    return false;
                }
                /**
                 * 判断参数是否存在
                 */
                if (
                    empty($consumeClassOpt['connect_name']) || empty($consumeClassOpt['queue_name'])
                ) {
                    echo str_pad($class, 50, " ")
                        . str_pad('connect', 20, " ")
                        . " 参数不存在\n";
                    return false;
                }
                $consumeClassOpt['exchange_name'] = !empty($consumeClassOpt['exchange_name']) ? $consumeClassOpt['exchange_name'] : 'queues';
                // $consumeClassOpt['exchange_name'] = !empty($consumeClassOpt['exchange_name']) ? $consumeClassOpt['exchange_name'] : '';

                $consumeQeObj = \shiyunQueue\QueueFactory::getInstance()
                    ->connection($consumeClassOpt['connect_name']);

                $consumeQeObj
                    ->setExchangeName($consumeClassOpt['exchange_name'])
                    ->setQueueName($consumeClassOpt['queue_name']);

                $that = $this;

                // 是否存在定时器
                if (!empty($consumeClassOpt['execute_timing'])) {
                    \Workerman\Timer::add(
                        intval($consumeClassOpt['execute_timing']),
                        function () use ($that, $consumeClassObj, $consumeClassOpt, $consumeQeObj) {
                            return $that->dealItemData($consumeClassObj, $consumeClassOpt, $consumeQeObj);
                        }
                    );
                    // \Workerman\Timer::add(intval($consumeClassOpt['execute_timing']), [$consumeClassObj, 'onQueueMessage'], [
                    //     ['msg1' => '队列消息的内容', 'msg2' => 2,],
                    //     '参数2'
                    // ]);
                } else {
                    return $that->dealItemData($consumeClassObj, $consumeClassOpt, $consumeQeObj);
                }
                // $consumeQeObj->subscribe($queue, );
                // }
            }
        } catch (\Throwable $th) {
            echo str_pad('--- ProcessWorker @ dealItemCheck ---', 50, " ") . "\n";
            echo $th->getMessage();
            echo  " \n";
            return false;
        } finally {
            return false;
        }
    }
    /**
     * ①、②、③、④、⑤、⑥、⑦、⑧、⑨、⑩、
     * ⑪、⑫、⑬、⑭、⑮、⑯、⑰、⑱、⑲、⑳、
     * ㉑、㉒、㉓、㉔、㉕、㉖、㉗、㉘、㉙、㉚
     */
    protected function dealItemData($consumeClassObj, $consumeClassOpt, $consumeQeObj)
    {
        try {

            $this->dealEchoDebug('① 开始', '', '');

            /**
             * 获取下个任务
             */
            $consumeJobObj = $consumeQeObj
                ->setExchangeName($consumeClassOpt['exchange_name'])
                ->setQueueName($consumeClassOpt['queue_name'])
                ->getPublish();

            if (empty($consumeJobObj) || is_null($consumeJobObj)) {
                throw new JobObjNoMoreException($consumeClassOpt['queue_name'] . ' 队列对象，没有更多');
            }
            // dd($consumeJobObj->getJobRawBody(), $consumeJobObj->getJobReserved());
            $consumeJobMsg = $consumeJobObj->payload();
            if (empty($consumeJobMsg)) {
                throw new JobMsgNoMoreException($consumeClassOpt['queue_name'] . ' 队列消息，没有更多');
            }

            $lastOption = $this->getLastPeizhi($consumeJobObj, $consumeClassOpt);
            /**
             * 判断环境
             */
            if ($this->supportsAsyncSignals()) {
                $this->listenForSignals();
                // 注册超时
                $this->registerTimeoutHandler($consumeJobObj, $lastOption['last_timeout_max']);
            }
            /**
             * 执行事件-进行中
             */
            $this->dealEchoDebug('② 执行事件', 'JobProcessing', '开始...');
            // $jobObj = new JobProcessing($consumeQeObj, $consumeJobObj);
            // $jobObj->handle();

            /**
             * ③ 参数判断
             * 有些任务在到达消费者时，可能已经不再需要执行了
             * 任务名
             */
            $this->dealEchoDebug('③ 参数判断', '', '');


            if (false) {
                $consumeJobObj->delete();
                return;
            }
            if ($consumeJobObj->hasFailedState()) {
                throw new JobFailedException($consumeJobObj->getName() . ' 已错误');
            }

            // 当前时间戳
            $currTime = (new \DateTime())->getTimestamp();
            /**
             * 是否超时
             */
            if ($lastOption['last_timeout_at'] && $currTime <= $lastOption['last_timeout_at']) {
                throw new TimeoutException($consumeJobObj->getName() . ' 作业可能已超时');
            }
            /**
             * 超过重试次数
             * 如果【已经】超过最大尝试次数，则将作业标记为失败
             */
            if (!empty($lastOption['last_tries_max']) || $lastOption['last_tries_attempts'] <= $lastOption['last_tries_max']) {
                throw new MaxAttemptsExceededException($consumeJobObj->getName() . ' 尝试次数过多或运行时间过长');
            }

            // /**
            //  * 如果【即将】超过最大尝试次数，则将作业标记为失败
            //  */
            // if ($peizhiTimeoutAt && $peizhiTimeoutAt <= $currTime) {
            //     $this->failJob($consumeQeObj, $consumeJobObj, $e);
            // }
            // if ($peizhiTriesMax > 0 && $peizhiTriesAttempts >= $peizhiTriesMax) {
            //     $this->failJob($consumeQeObj, $consumeJobObj, $e);
            // }


            /**
             * 执行
             */
            $this->dealEchoDebug('④ 执行回调', 'onQueueMessage', '');
            $queueJobRes = \call_user_func(
                [$consumeClassObj, 'onQueueMessage'],
                $consumeJobMsg,
                $consumeJobObj
            );

            // var_dump($consumeJobObj->getResolvedJob());

            /**
             * 执行事件-已完成
             */
            $this->dealEchoDebug('⑤ 执行事件', 'JobProcessed', '开始...');
            (new JobProcessed($consumeQeObj, $consumeJobObj))->handle();

            $this->dealEchoDebug('⑥ 是否重启', '', '');
            $this->stopIfNecessary($consumeJobObj);

            $this->dealEchoDebug('⑦ 队列结果', $consumeClassOpt['queue_name'], $queueJobRes);


            var_dump("result : {$consumeClassOpt['queue_name']}", $queueJobRes);

            if ($queueJobRes) {
                $this->dealEchoDebug('⑧ 成功-删除', '', '');
                // 删除任务
                $consumeJobObj->delete();
                $this->dealEchoDebug('⑨ 成功-记录日志', '', '');
                // 记录日志
                $this->dealItemLog($consumeClassObj, $consumeJobMsg);
            } else {
                $this->dealEchoDebug('⑧ 失败-重试', '', '');

                /**
                 * 超过重试次数
                 */
                if ($consumeJobObj->getJobAttemptsNum() >= $lastOption['last_error_max'] && $lastOption['last_error_max']) {
                    // echo "超时任务删除" . $job->getJobAttemptsNum() . '\n';
                    // 删除任务
                    $consumeJobObj->delete();
                    // 记录日志    
                    $this->dealItemLog($consumeClassObj, $consumeJobMsg);
                } else {
                    // 从新放入队列
                    $consumeJobObj->release();
                }
            }
        } catch (MaxAttemptsExceededException | TimeoutException $e) {
            //  $this->failJob($consumeQeObj, $consumeJobObj, $e);
        } catch (JobFailedException $e) {
            // (new JobExceptionOccurred($consumeQeObj, $consumeJobObj, $e))->handle();
            // // throw $e;
            // $this->exceptHandle->report($e);
            // $this->sleep(1);
        } catch (Exception | Throwable $e) {
            // echo " Exception  \n";
            // echo $e->getMessage() . "  \n";
        } finally {
            return false;
            // if (!$consumeJobObj->isDeleted() && !$consumeJobObj->isReleased() && !$consumeJobObj->hasFailedState()) {
            //     $consumeJobObj->release($delay);
            // }
        }
    }

    /**
     * @param string    $consumeQeObj
     * @param Job       $job
     * @param Exception $e
     */
    protected function failJob($consumeQeObj, $job, $e)
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
            $jobObj = new JobFailed(
                $consumeQeObj,
                $job,
                $e ?: new RuntimeException('ManuallyFailed')
            );
            $jobObj->handle();
        }
    }
    /**
     * debug调试
     */
    protected function dealEchoDebug($msg1 = '', $msg2 = '', mixed $msg3 = '')
    {
        if ($this->procDebugInfo) {
            echo str_pad($msg1, 50, " ")
                . str_pad($msg2, 20, " ")
                . " {$msg3} \n";
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
     * 获取任务配置参数
     */
    protected function getLastPeizhi($consumeJobObj, $consumeClassOpt = [])
    {
        // 最大错误次数 20
        $errorMaxAllow = $consumeJobObj->payload('allowError') ?? 0;
        // 允许最大的重试 20
        $jobTriesMax = $consumeJobObj->payload('maxTries');
        $peizhiTriesMax = !is_null($jobTriesMax) ? $jobTriesMax : (int) ($consumeClassOpt['tries_max'] ?? 0);
        // 已经重试
        $peizhiTriesAttempts = $consumeJobObj->getJobAttemptsNum();
        // 允许超时
        $peizhiTimeoutAt = $consumeJobObj->getTimeoutAt();
        // 最大的超时 20
        $peizhiTimeoutMax = $consumeClassOpt['timeout_max'] ?? 0;
        // 设置的内存
        $memory = 128;
        return [
            'last_sett_memory'      => $memory,
            'last_error_max'        => $errorMaxAllow,
            'last_tries_attempts'   => $peizhiTriesAttempts,
            'last_tries_max'        => $peizhiTriesMax,
            'last_timeout_at'       => $peizhiTimeoutAt,
            'last_timeout_max'      => $peizhiTimeoutMax,
        ];
    }
    /**
     * 处理错误
     */
    public function dealQueueException()
    {
    }
    /**
     * 确定队列工作程序是否应重新启动
     */
    protected function stopIfNecessary($job)
    {
        // 获取内存
        $memory = $lastOption['last_sett_memory'] ?? 128;
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
     * @param Job|null $job
     * @param int      $timeout
     * @return void
     */
    protected function registerTimeoutHandler($job, $timeout)
    {
        pcntl_signal(SIGALRM, function () {
            $this->killProcess(1);
        });
        // 为给定的作业获取适当的超时。
        $giveTimeroutForJob = $job && !is_null($job->timeout()) ? $job->timeout() : $timeout;
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
        // (new WorkerStopping($status))->handle();
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
        // new WorkerStopping($status)->hanlde();
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
