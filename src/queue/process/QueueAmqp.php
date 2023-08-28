<?php

namespace shiyunQueue\process;

use Workerman\Timer;
use Workerman\Worker;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use shiyun\support\Cache;
use shiyun\support\Config;
use shiyunWorker\WorkermanServer;

class QueueAmqp extends WorkermanServer
{
    protected $processes    = 1;
    protected $socket       = 'tcp://0.0.0.0:16060';
    protected $workerName   = 'queue_amqp';
    //
    protected $cliCmd;
    protected $cache;
    protected $cachePrefix;
    // 所有配置
    protected $connectConfig = [];
    // 当前配置
    protected $queue;
    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        $this->cliCmd = $_SERVER['argv'][1];
        // $this->cliCmd = $_SERVER['argv'][2];
        $this->connectConfig = syGetConfig('shiyun.queue.connections');

        $this->queue = $this->connectConfig['amqp1'] ?? [];
        parent::__construct();
    }

    /**
     * 初始化设置
     */
    public function init()
    {
        // $this->processes = count($this->queue);
        $this->processes = 1;
        $multiple =  $this->queue['workerman_multiple'] ?? 1;
        $this->worker->count = $this->processes * $multiple;

        //创建日志目录
        $runtime = syPathRuntime();
        $pidPath = $runtime . 'queue_run/';
        $logPath = $runtime . 'workerman/';
        !is_dir($logPath) && mkdir($logPath, 0755, true);
        !is_dir($pidPath) && mkdir($pidPath, 0755, true);

        // Worker::$daemonize   = true;
        // Worker::$logFile     = $logPath . 'workerman.log';
        // Worker::$stdoutFile  = $logPath . 'workerman_stdout.log';
        // Worker::$pidFile     = $pidPath . 'workerman.pid';

        return true;
    }
    /**
     * 每个子进程启动
     * 子进程里面的代码可以用 php workerman.php reload 平滑重启
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        $queueId = $worker->id % $this->processes;
        $queueName = $this->queue[$queueId] ?? 'asdasd';
        // Timer::add(0.8, [$this, 'notification'], [$queueName]);
        // $this->notification($queueName);
    }
    /**
     * @desc 多进程订单通知
     * @param $part
     * @return mixed
     */
    public function notification($part)
    {
        echo '我是一个定时器__' . $part . "\n";
        // return \AmqpCustomer2::handle($part);
    }
    protected $connection;
    protected $channel;
    protected $queueName;
    public function newConnection()
    {
        // 获取配置
        $connectConfig = syGetConfig('shiyun.queue_amqp.channel.AMQP');
        // 建立连接
        $this->connection = new AMQPStreamConnection(
            $connectConfig['connect_host'],
            $connectConfig['connect_port'],
            $connectConfig['connect_user'],
            $connectConfig['connect_password'],
            $connectConfig['connect_vhost'], // 这个可以不用
        );
        // 获取信道
        $this->channel = $this->connection->channel();
        return $this->connection;
    }
    protected function delaySimpleSend()
    {
        $connection = '';
        $channel = '';
        $delayConsumer = new \shiyunQueue\drive\rabbitmq\ModeSimple($connection, $channel);

        $ttl = 1000 * 100; //订单100s后超时
        $delayExName = 'delay-order-exchange'; //超时exchange
        $delayQueueName = 'delay-order-queue'; //超时queue
        $queueName = 'ttl-order-queue'; //订单queue
        $this->createQueue(
            $ttl,
            $delayExName,
            $delayQueueName,
            $queueName
        );
        //100个订单信息，每个订单超时时间都是10s
        for ($i = 0; $i < 100; $i++) {
            $data = [
                'omain_id' => $i + 1,
                'remark' => 'this is a order test'
            ];
            $delayConsumer->pushMessage($queueName, json_encode($data));
            sleep(1);
        }
    }
    protected function delaySimpleCustomer()
    {
        // 消费者，看看消费之后的，过一会会观察到，已经有到期message被push到了delay_order_queue
        // 消费者也消费到了message
        // 消费者
        $connection = '';
        $channel = '';
        $delayConsumer = new \shiyunQueue\drive\rabbitmq\ModeSimple($connection, $channel);
        /**
         * 消费已经超时的订单信息，进行处理
         */
        // // 创建直连的交换机
        // $channel->exchange_declare('amq.direct', 'direct', false, false, false);
        // // 队列创建申明 第三个参数为true时，表示队列持久化,需要和客户端一致
        // $channel->queue_declare($this->queueName, false, true, false, false);
        // // 交换机跟队列的绑定，
        // $channel->queue_bind($this->queueName, 'amq.direct', $queueName);


        $callback = function ($msg) {
            echo $msg->body . PHP_EOL;
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            //处理订单超时逻辑，给用户推送提醒等等。。。
            sleep(10);
        };
        $delayConsumer->receive('delay-order-queue', $callback);
    }

    /**
     * 创建延时队列
     * 使用RabbitMQ实现延时队列功能
     * Class DelayQueue
     * @package RabbitMQ
     */
    /** 
     * 创建延时队列 
     * @param $ttl
     * @param $delayExName
     * @param $delayQueueName
     * @param $queueName
     */
    public function createQueue($ttl, $delayExName, $delayQueueName, $queueName)
    {
        $args = new AMQPTable([
            'x-dead-letter-exchange' => $delayExName,
            'x-message-ttl' => $ttl, //消息存活时间
            'x-dead-letter-routing-key' => $queueName
        ]);
        $this->channel->queue_declare($queueName, false, true, false, false, false, $args);
        //绑定死信queue
        $this->channel->exchange_declare($delayExName, AMQPExchangeType::DIRECT, false, true, false);
        $this->channel->queue_declare($delayQueueName, false, true, false, false);
        $this->channel->queue_bind($delayQueueName, $delayExName, $queueName, false);
    }
}
