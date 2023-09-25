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
use RedisException;
use think\helper\Str;
use shiyunQueue\drive\Connector;
use shiyunQueue\libs\InteractsWithTime;

class RedisConnector extends Connector
{
    use InteractsWithTime;

    /** @var  \Redis */
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

        $this->default    = $config['queue_name'] ?? '';
        $this->retryAfter =  $config['retry_after'] ?? 60;
        $this->blockFor   =  $config['block_for'] ?? null;

        $this->createDriver($config);
    }
    public function createDriver($config = [])
    {
        if (!extension_loaded('redis')) {
            throw new Exception('redis扩展未安装');
        }
        $redis = new class($config)
        {
            protected $config;
            protected $client;
            public function __construct($config)
            {
                $this->config = $config;
                $this->client = $this->createClient();
            }
            protected function createClient()
            {
                $config = $this->config;
                $func   = $config['persistent'] ? 'pconnect' : 'connect';

                $client = new \Redis;
                $client->$func($config['connect_host'], $config['connect_port'], $config['timeout']);

                if ('' != $config['connect_password']) {
                    $client->auth($config['connect_password']);
                }

                if (0 != $config['select']) {
                    $client->select($config['select']);
                }
                return $client;
            }

            public function __call($name, $arguments)
            {
                try {
                    return call_user_func_array([$this->client, $name], $arguments);
                } catch (RedisException $e) {
                    if (Str::contains($e->getMessage(), 'went away')) {
                        $this->client = $this->createClient();
                    }

                    throw $e;
                }
            }
        };
        $this->redis = $redis;
    }
    public function size($queue = null)
    {
        $queue = $this->getQueue($queue);
        return $this->redis->lLen($queue) + $this->redis->zCard("{$queue}:delayed") + $this->redis->zCard("{$queue}:reserved");
    }


    public function getPublish($queue = null)
    {
        if (empty($queue)) {
            $queue = $this->getQueueName();
        }
        $this->migrate($prefixed = $this->getQueue($queue));

        if (empty($nextJob = $this->retrieveNextJob($prefixed))) {
            return;
        }
        [$job, $reserved] = $nextJob;
        if ($reserved) {
            return new RedisJob($this->app, $this, $job, $reserved, $this->connection, $queue);
        }
    }

    /**
     * Migrate any delayed or expired jobs onto the primary queue.
     *
     * @param string $queue
     * @return void
     */
    protected function migrate($queue)
    {
        $this->migrateExpiredJobs($queue . ':delayed', $queue);
        if (!is_null($this->retryAfter)) {
            $this->migrateExpiredJobs($queue . ':reserved', $queue);
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
        $jobs = $this->redis->zRangeByScore($from, '-inf', $this->currentTime());
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
     * Retrieve the next job from the queue.
     * 从队列中检索下一个作业。
     * @param string $queue
     * @return array
     */
    protected function retrieveNextJob($queue)
    {
        if (!is_null($this->blockFor)) {
            return $this->blockingPop($queue);
        }
        $job      = $this->redis->lpop($queue);
        $reserved = false;
        if ($job) {
            $reserved = json_decode($job, true);
            $reserved['attempts']++;
            $reserved = json_encode($reserved);
            $this->redis->zAdd($queue . ':reserved', $this->availableAt($this->retryAfter), $reserved);
        }
        return [$job, $reserved];
    }

    /**
     * Retrieve the next job by blocking-pop.
     *
     * @param string $queue
     * @return array
     */
    protected function blockingPop($queue)
    {
        $rawBody = $this->redis->blpop($queue, $this->blockFor);
        if (!empty($rawBody)) {
            $payload = json_decode($rawBody[1], true);
            $payload['attempts']++;
            $reserved = json_encode($payload);
            $this->redis->zadd($queue . ':reserved', $this->availableAt($this->retryAfter), $reserved);
            return [$rawBody[1], $reserved];
        }
        return [null, null];
    }

    /**
     * 删除任务
     *
     * @param string $queue
     * @param RedisJob $job
     * @return void
     */
    public function deleteReserved($queue, $job)
    {
        $this->redis->zRem($this->getQueue($queue) . ':reserved', $job->getJobReserved());
    }

    /**
     * Delete a reserved job from the reserved queue and release it.
     *
     * @param string $queue
     * @param RedisJob $job
     * @param int $delay
     * @return void
     */
    public function deleteAndRelease($queue, $job, $delay)
    {
        $queue = $this->getQueue($queue);
        $reserved = $job->getJobReserved();
        $this->redis->zRem($queue . ':reserved', $reserved);
        $this->redis->zAdd($queue . ':delayed', $this->availableAt($delay), $reserved);

        // $this->redis->rPush($queueName, $msg);
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
                $this->redis->discard();
            }
        } catch (Exception $e) {
            $this->redis->discard();
        }
    }

    protected function createPayload($job, $data = '')
    {
        $payload = is_object($job)
            ? $this->createObjectPayload($job)
            : $this->createPlainPayload($job, $data);

        /**
         * 随机id
         */
        $randomID =  Str::random(32);
        $payload = array_merge($payload, [
            'id'       => $randomID,
            'attempts' => 0,
        ]);
        $payload = json_encode($payload);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('Unable to create payload: ' . json_last_error_msg());
        }
        return $payload;
    }

    /**
     * 获取队列名
     *
     * @param string|null $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        $exchange = $this->exchangeName ?: 'queues';
        $queue = $queue ?: $this->default;
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
    public function sendPublish(?array $msg = null)
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
            $this->setMessage($msg);
        }
        $msgData = $this->getMessage();
        $payload =  $this->createPayload($this->jobServer, $msgData);

        // 判断是否 定时队列
        $queueName = $this->getQueue($this->queueName);
        if (!empty($this->msgDelay) && $this->msgDelay) {
            // return $this->redis->zAdd($queue_delay, $this->msgActualTime, $payload);
            if ($this->redis->zadd("{$queueName}:delayed", $this->availableAt($this->msgDelay), $payload)) {
                $res = json_decode($payload, true)['id'] ?? null;
            }
        } else {
            // return $this->redis->lPush($queueName, $payload);
            // $this->redis->lPush(['some', 'data']);
            if ($this->redis->rPush($queueName, $payload)) {
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
        if ($this->redis->rPush($this->getQueue($queue), $payload)) {
            return json_decode($payload, true)['id'] ?? null;
        }
    }
    /**
     * 创建连接
     */
    public function newConnection()
    {
        // 连接本地的Redis 服务
        $config = syGetConfig('shiyun.queue.connection.redis');
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
    public function subscribe(mixed $queue, callable $callback)
    {
    }
    // 取消订阅
    public function unsubscribe(mixed $queue, callable $callback)
    {
    }
}
