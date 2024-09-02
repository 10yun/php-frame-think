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

namespace shiyunQueue\drive\database;

use stdClass;
use think\Db;
use think\db\ConnectionInterface;
use think\db\Query;
use shiyunQueue\drive\Connector;
use shiyunQueue\libs\InteractsWithTime;

class DatabaseConnector extends Connector
{
    use InteractsWithTime;
    protected $db;
    /**
     * The database table that holds the jobs.
     */
    protected string $table;
    /**
     * string 队列名称(在这里为表名字)
     */
    protected string|null $queueName = 'queue';

    /**
     * The expiration time of a job.
     */
    protected int|null $retryAfter = 60;

    public function __construct(array $config = [])
    {
        $this->table      =  $config['table'];
        $this->retryAfter =  $config['retry_after'] ?? 60;
        $this->createDriver($config);
    }
    public function createDriver($config = [])
    {
        if (empty($config['connection'])) {
            throw new \Exception('配置错误');
        }

        $dbObj = new \think\DbManager();
        $dbObj->setConfig([]);
        $dbObj->connect();
        // $connection = Db::connect($config['connection'] ?? null);
        $this->db = $dbObj;
    }

    public function size($queue = null)
    {
        return $this->db
            ->name($this->table)
            ->where('queue', $this->getPrefixQueue($queue))
            ->count();
    }
    public function retryPublish($payload, $queue = null, array $options = [])
    {
        return $this->pushToDatabase($queue, $payload);
    }
    public function sendPublish(?array $data = null, $job = null)
    {
        if (!empty($data)) {
            $this->addMessage($data);
        }
        $payload = $this->createPayload($job);
        if (!empty($this->msgDelay)) {
            return $this->pushToDatabase($this->queueName, $payload,  $this->msgDelay);
        } else {
            return $this->pushToDatabase($this->queueName, $payload);
        }
    }
    public function bulk($jobs, $data = '', $queue = null)
    {
        $queue = $this->getPrefixQueue($queue);
        $availableAt = $this->availableAt();
        return $this->db->name($this->table)->insertAll(collect((array) $jobs)->map(
            function ($job) use ($queue, $data, $availableAt) {
                $this->addMessage($data);
                return [
                    'queue'          => $queue,
                    'attempts'       => 0,
                    'reserve_time'   => null,
                    'available_time' => $availableAt,
                    'create_time'    => $this->currentTime(),
                    'payload'        => $this->createPayload($job),
                ];
            }
        )->all());
    }

    /**
     * 重新发布任务
     *
     * @param string $queue
     * @param StdClass $job
     * @param int $delay
     * @return mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($queue, $job->payload, $delay, $job->attempts);
    }

    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param \DateTime|int $delay
     * @param string|null $queue
     * @param string $payload
     * @param int $attempts
     * @return mixed
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        return $this->db->name($this->table)->insertGetId([
            'queue'          => $this->getPrefixQueue($queue),
            'attempts'       => $attempts,
            'reserve_time'   => null,
            'available_time' => $this->availableAt($delay),
            'create_time'    => $this->currentTime(),
            'payload'        => $payload,
        ]);
    }

    public function getPublish()
    {
        $queue = $this->getPrefixQueue();
        return $this->db->transaction(function () use ($queue) {

            if ($job = $this->getNextAvailableJob($queue)) {

                $job = $this->markJobAsReserved($job);

                return new DatabaseJob($this, $job, $this->connection, $queue);
            }
        });
    }

    /**
     * 获取下个有效任务
     *
     * @param string|null $queue
     * @return StdClass|null
     */
    protected function getNextAvailableJob($queue)
    {

        $job = $this->db
            ->name($this->table)
            ->lock(true)
            ->where('queue', $this->getPrefixQueue($queue))
            ->where(function (Query $query) {
                $query->where(function (Query $query) {
                    $query->whereNull('reserve_time')
                        ->where('available_time', '<=', $this->currentTime());
                });



                $interval = new \DateInterval("PT{$this->retryAfter}S"); // 创建间隔对象，单位为秒
                $future = (new \DateTime())->add($interval); // 计算间隔后的日期和时间
                $expiration = $future->getTimestamp(); // 有效期


                $query->whereOr(function (Query $query) use ($expiration) {
                    $query->where('reserve_time', '<=', $expiration);
                });
            })
            ->order('id', 'asc')
            ->find();

        return $job ? (object) $job : null;
    }

    /**
     * 标记任务正在执行.
     * @param stdClass $job
     * @return stdClass
     */
    protected function markJobAsReserved($job)
    {
        $this->db
            ->name($this->table)
            ->where('id', $job->id)
            ->update([
                'reserve_time' => $job->reserve_time = $this->currentTime(),
                'attempts'     => ++$job->attempts,
            ]);

        return $job;
    }
    /**
     * 删除任务
     *
     * @param string $id
     * @return void
     */
    public function deleteReserved($id)
    {
        $this->db->transaction(function () use ($id) {
            if ($this->db->name($this->table)->lock(true)->find($id)) {
                $this->db->name($this->table)->where('id', $id)->delete();
            }
        });
    }
    protected function getPrefixQueue(?string $queue = null)
    {
        return $queue ?: $this->queueName;
    }
}
