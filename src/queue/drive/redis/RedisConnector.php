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

namespace shiyunQueue\drive\redis;

use Closure;
use Exception;
use shiyunQueue\drive\Connector;
use shiyunQueue\libs\InteractsWithTime;
use shiyunQueue\drive\redis\RedisLib;

class RedisConnector extends Connector
{
    use InteractsWithTime;

    /** @var \Redis */
    protected $redis;

    /**
     * 默认队列的名称
     */
    protected string $default = 'default';
    /**
     * 作业的过期时间
     */
    protected int|null $retryAfter = 60;
    /**
     * 作业要阻止的最大秒数
     */
    protected int|null $blockFor = null;

    public function __construct(
        array $config = [],
    ) {
        $this->retryAfter =  $config['retry_after'] ?? 60;
        $this->blockFor   =  $config['block_for'] ?? null;

        $this->createDriver($config);
    }
    public function createDriver($config = [])
    {
        if (!extension_loaded('redis')) {
            throw new Exception('redis扩展未安装');
        }
        $this->redis = new RedisLib($config);
    }
    public function size($queue = null)
    {
        $prefixedQueue = $this->getPrefixQueue($queue);
        return $this->redis->lLen($prefixedQueue)
            + $this->redis->zCard("{$prefixedQueue}:delayed")
            + $this->redis->zCard("{$prefixedQueue}:reserved");
    }

    public function getPublish()
    {
        $prefixedQueue = $this->getPrefixQueue();
        $exchange = $this->getExchangeName();
        $queue = $this->getQueueName();
        $this->migrate($prefixedQueue);
        $nextJob = $this->retrieveNextJob($prefixedQueue);
        if (empty($nextJob)) {
            return;
        }
        [$job, $reserved] = $nextJob;
        if ($reserved) {
            return new RedisJob($this, $job, $reserved, $this->connection, $queue, $exchange);
        }
    }

    /**
     * 将所有延迟或过期的作业迁移到主队列。
     * @param string $prefixedQueue
     * @return void
     */
    protected function migrate($prefixedQueue)
    {
        $this->migrateExpiredJobs($prefixedQueue . ':delayed', $prefixedQueue);
        if (!is_null($this->retryAfter)) {
            $this->migrateExpiredJobs($prefixedQueue . ':reserved', $prefixedQueue);
        }
    }

    /**
     * 移动延迟任务
     *
     * @param string $from
     * @param string $to
     * @param bool $attempt
     */
    public function migrateExpiredJobs($from, $to, $attempt = true)
    {
        $this->redis->watch($from);
        $currTime = $this->currentTime(); // 当前时间
        // $currTime = $this->currentTime() + 600; // 未来10分钟
        $jobs = $this->redis->zRangeByScore($from, '-inf', $currTime);

        // $jobs = $this->redis->zRange($from, 0, -1, ['withscores' => true]);
        if (!empty($jobs)) {
            $this->transaction(function () use ($from, $to, $jobs, $attempt) {
                $this->redis->zRemRangeByRank($from, 0, count($jobs) - 1);
                for ($i = 0; $i < count($jobs); $i += 100) {
                    $values = array_slice($jobs, $i, 100);
                    $this->redis->rPush($to, ...$values);
                }
            });
        }
        $this->redis->unwatch();
    }
    /**
     * redis事务
     * @param Closure $closure
     */
    protected function transaction(Closure $closure)
    {
        $this->redis->multi();
        try {
            call_user_func($closure);
            if (!$this->redis->exec()) {
                // var_dump("Redis 事务执行失败，准备 discard。");
                $this->redis->discard();
            } else {
                // var_dump("Redis 事务执行成功。");
            }
        } catch (Exception $e) {
            // var_dump("Redis 事务抛出异常: " . $e->getMessage());
            $this->redis->discard();
        }
    }
    /**
     * 从队列中检索下一个作业。
     * @param string $prefixedQueue
     * @return array
     */
    protected function retrieveNextJob($prefixedQueue)
    {
        if (!is_null($this->blockFor)) {
            return $this->blockingPop($prefixedQueue);
        }
        $job      = $this->redis->lpop($prefixedQueue);
        $reserved = false;
        if ($job) {
            $reserved = json_decode($job, true);
            $reserved['attempts']++;
            $reserved = json_encode($reserved);
            $this->redis->zAdd($prefixedQueue . ':reserved', $this->availableAt($this->retryAfter), $reserved);
        }
        return [$job, $reserved];
    }

    /**
     * 通过阻止弹出来检索下一个作业。
     * @param string $prefixedQueue
     * @return array
     */
    protected function blockingPop($prefixedQueue)
    {
        $rawBody = $this->redis->blpop($prefixedQueue, $this->blockFor);
        if (!empty($rawBody)) {
            $payload = json_decode($rawBody[1], true);
            $payload['attempts']++;
            $reserved = json_encode($payload);
            $this->redis->zadd($prefixedQueue . ':reserved', $this->availableAt($this->retryAfter), $reserved);
            return [$rawBody[1], $reserved];
        }
        return [null, null];
    }

    /**
     * 删除保留任务
     * @param string $queue
     * @param RedisJob $job
     * @return void
     */
    public function deleteReserved($queue = null, $job = null)
    {
        $prefixedQueue = $this->getPrefixQueue($queue);
        $this->redis->zRem($prefixedQueue . ':reserved', $job->getJobReserved());
    }

    /**
     * 从保留队列中删除保留作业并释放它。
     *
     * @param string $queue
     * @param RedisJob $job
     * @param int $delay
     * @return void
     */
    public function deleteAndRelease($queue = null, $job = null, $delay = 0)
    {
        $prefixedQueue = $this->getPrefixQueue($queue);
        $reserved = $job->getJobReserved();
        $this->redis->zRem($prefixedQueue . ':reserved', $reserved);
        $this->redis->zAdd($prefixedQueue . ':delayed', $this->availableAt($delay), $reserved);
        // $this->redis->rPush($queueName, $msg);
    }

    /**
     * 获取队列名
     *
     * @param string|null $queue
     * @return string
     */
    protected function getPrefixQueue(string|null $queue = '')
    {
        $exchange = $this->exchangeName ?: 'queues';
        $queue = !empty($queue) ? $queue : $this->queueName;
        return "{$exchange}:{$queue}";
    }
    /**
     * 发送\发布消息
     * 放入消息队列
     * @param array|null $data
     * @return mixed
     */
    /**
     * $delay 秒后执行
     * later 延迟执行，单位秒
     * 把任务加入到消息队列，等待被执行
     * 将该任务推送到消息列表，等待对应的消费者去执行
     * 
     * 参数说明
     * @param 【第1参数(非必填)】延迟发送任务 5秒
     *  1.当前任务由哪个类来负责处理，当轮到该任务时，系统将生成该类的实例，并调用其 onQueueMessage 方法
     * @param 【第2参数】任务类 - 执行时调用该类的deal方法
     * @param 【第3参数】数据
     * @param 【第4参数】队列名称
     * 4.当任务归属的队列名称，如果为新队列，会自动创建
     */
    public function sendPublish(array|string|int|null $msg = null)
    {
        $queue_waiting = '{redis-queue}-waiting'; //1.0.5版本之前为redis-queue-waiting
        $queue_delay = '{redis-queue}-delayed'; //1.0.5版本之前为redis-queue-delayed
        $queue_name = $queue_waiting . $this->queueName;

        // if (!$this->jobServer) {
        //     return [
        //         'error' => '需要执行的队列类必须存在'
        //     ];
        // }
        if (!empty($msg)) {
            $this->setMsgData($msg);
        }
        $payload =  $this->createPayload($this->jobServer);
        // 判断是否 定时队列
        $prefixedQueue = $this->getPrefixQueue($this->queueName);
        if (!empty($this->msgDelay) && $this->msgDelay) {
            // return $this->redis->zAdd($queue_delay, $this->msgActualTime, $payload);
            if ($this->redis->zadd("{$prefixedQueue}:delayed", $this->availableAt($this->msgDelay), $payload)) {
                $res = json_decode($payload, true)['id'] ?? null;
            }
        } else {
            // return $this->redis->lPush($prefixedQueue, $payload);
            // $this->redis->lPush(['some', 'data']);
            if ($this->redis->rPush($prefixedQueue, $payload)) {
                $res = json_decode($payload, true)['id'] ?? null;
            }
        }
        $this->baseClean();
        // database 驱动时，返回值为 1|false  ; 
        // redis 驱动时，返回值为 随机字符串|false
        return $res;
    }
    /**
     * 重新发布
     */
    public function retryPublish($payload, $queue = null, array $options = [])
    {
        if ($this->redis->rPush($this->getPrefixQueue($queue), $payload)) {
            return json_decode($payload, true)['id'] ?? null;
        }
    }
    /**
     * 创建连接
     */
    public function newConnection()
    {
        // 连接本地的Redis 服务
        $config = syGetConfig('shiyun.process_queue.connection.redis');
        /**
         * 方式1，tp的redis
         */
        // $redis = new \think\cache\driver\Redis($config);
        // $this->connection = $redis->handler();
        /**
         * 方式2，原生的redis
         * 连接本地的Redis 服务
         */
        $this->connection = new \Redis();
        $this->connection->connection('127.0.0.1', 6379);
        return $this;
        //提取队列中的数据
        $data = $this->connection->rPop('send_captcha');
    }
    // 订阅
    public function subscribe(mixed $queue, callable $callback) {}
    // 取消订阅
    public function unsubscribe(mixed $queue, callable $callback) {}
}
