<?php

namespace shiyunQueue\drive\rabbitmq;

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * 简单模式
 */
class ModeSimple
{

    protected $consumerTag = 'consumer';
    protected $exchangeName = 'router';
    protected $exchangeType = 'direct';
    protected $routeKey = '';
    protected $queueName = 'msgs';

    protected $connection;
    protected $channel;
    public function __construct($connection, $channel)
    {
        /**
         * 建立连接
         */
        // $host = '127.0.0.1';
        // $port = 5672;
        // $user = 'guest';
        // $pwd = 'guest';
        // $vhost = '/';
        // $param = syGetConfig('shiyun..queue.connection.amqp');
        // $connection = new AMQPStreamConnection($host, $port, $user, $pwd, $vhost);
        // $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'zq', '123456', '/');
        // $channel = $connection->channel(); // 创建通道
        $this->connection = $connection;
        $this->channel = $channel;
    }
    /**  
     * @throws \Exception  
     */
    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
    /**
     * @desc  发送消息队列
     * @desc 发送队列消息
     * @param string $queueName 队列名称,队列名  消息队列载体，每个消息都会被投入到一个或多个队列。
     * @param string $msg 发送消息
     * @return bool
     * @throws \Exception
     */
    public function pushMessage($queueName, $msg, $exchange = '', $properties = [])
    {
        // 创建连接
        $connection = $this->connection;
        // 获取信道,创建channel，多个channel可以共用连接
        $channel = $connection->channel();
        // 创建交换机以及队列（如果已经存在，不需要重新再次创建并且绑定）
        // 创建直连的交换机
        //$channel->exchange_declare('amq.direct', 'direct', false, false, false);

        // 队列创建申明 第三个参数为true时，表示队列持久化,需要和客户端一致
        //声明创建队列
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_declare($queueName, false, false, false, false);

        // 交换机跟队列的绑定，
        //$channel->queue_bind($queueName, 'amq.direct', $queueName);

        // $amqpTable = new AMQPTable(["delay" => "1000"]);
        // $body = new AMQPMessage($msg, [
        //     'application_headers' => $amqpTable,
        //     'content_type' => 'text/plain',
        //     'delivery_mode' => 2
        // ]); //生成消息


        // for ($i = 0; $i < 5; ++$i) {
        //     sleep(1); //休眠1秒
        //     //消息内容
        //     $msg = "Hello,Zq Now Time:" . date("h:i:s");
        //     //将我们需要的消息标记为持久化 - 通过设置AMQPMessage的参数delivery_mode = AMQPMessage::DELIVERY_MODE_PERSISTENT
        //     $body = new AMQPMessage($msg, array(
        //         'content_type' => 'text/plain',
        //         'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        //     ));
        //     //发送消息
        //     $channel->basic_publish($body, $exchange, $queueName);
        //     echo "Send Message:" . $i . "\n";
        // }

        $body = new AMQPMessage(
            $msg,
            $properties
        );
        $channel->basic_publish($body, $exchange, $queueName);

        $channel->close();  //关闭信道
        $connection->close(); //关闭连接
    }
    /**
     * 接收消息队列
     * @desc 消费消息
     * @param string $queueName 队列名称
     * @param callback $callback 回调方法
     * @throws \ErrorException
     */
    public function receive($queueName, $callback = false)
    {
        try {
            //创建连接
            $connection = $this->connection;
            // $connection = $this->getConnection();
            //创建channel，多个channel可以共用连接
            $channel = $connection->channel();

            /**
             * 设置消费者（Consumer）客户端同时只处理一条队列
             * 这样是告诉RabbitMQ，再同一时刻，不要发送超过1条消息给一个消费者（Consumer），
             * 直到它已经处理了上一条消息并且作出了响应。这样，RabbitMQ就会把消息分发给下一个空闲的消费者（Consumer）。
             */
            $channel->basic_qos(null, 1, null);
            $channel->basic_qos(0, 1, false);
            /**
             * 同样是创建路由和队列，以及绑定路由队列，注意要跟publisher的一致
             * 这里其实可以不用，但是为了防止队列没有被创建所以做的容错处理
             */
            $channel->exchange_declare($this->exchangeName, $this->exchangeType, false, true, false);
            // 声明创建队列
            // $channel->queue_declare($queueName, false, false, false, false);
            // $channel->queue_declare($queueName, false, true, false, false);
            // $channel->queue_bind($this->queueName, $this->exchangeName, $this->routeKey);



            //消息消费
            //echo " [*] Waiting for messages. To exit press CTRL+C\n";
            if (empty($callback)) {
                /**
                 * 这边改成业务自己的代码
                 */
                $callback = function ($msg) {
                    $result = json_decode($msg->body, true);
                    //$result = call_user_func($callback, $msg);
                    echo ' [x] Received ', $msg->body, "\n";
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    // if ($result) {
                    //     $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    // } else {
                    // }
                };
            }
            // 启动队列消费者 消费者设置手动ack，在声明no_ack参数时指定false
            $channel->basic_consume($queueName, '', false, true, false, false, $callback);
            $channel->basic_consume($queueName, '', false, false, false, false, $callback);


            /**
             *   queue: 从哪里获取消息的队列
             * @param string  queueName
             * @param string  consumer_tag 消费者标识符,用于区分多个客户端
             * @param boolean no_local: 不接收此使用者发布的消息
             * @param boolean no_ack: 设置为true，则使用者将使用自动确认模式。详情请参见.
             *               自动ACK：消息一旦被接收，消费者自动发送ACK
             *               手动ACK：消息接收后，不会发送ACK，需要手动调用
             * @param string exclusive 请独占使用者访问，这意味着只有这个使用者可以访问队列
             *               exclusive:是否排他，即这个队列只能由一个消费者消费。适用于任务不允许进行并发处理的情况下
             *	
             * @param boolean nowait: 不返回执行结果，但是如果排他开启的话，则必须需要等待结果的，如果两个一起开就会报错
             *
             * @param callback $callback 回调逻辑处理函数,
             */
            // $channel->basic_consume(
            //     $this->queueName,
            //     $this->consumerTag,
            //     false,
            //     false,
            //     false,
            //     false,
            //     array($this, 'process_message')
            // );

            // echo "[Datetime: " . date('Y-m-d H:i:s') . "]\n";
            // echo "[Received: channel->callbacks:" . count($channel->callbacks) . "]\n";;
            // echo "[Received: " . date('Y-m-d H:i:s') . '$channel->callbacks:' . count($channel->callbacks) . "]\n";;


            // register_shutdown_function(array($this, 'shutdown'), $channel, $connection);
            /**
             *  阻塞队列监听事件
             */
            // while ($channel->is_consuming()) {
            while (count($channel->callbacks)) {
                try {
                    $channel->wait();
                    // $channel->wait(null, false, 180);
                    // $channel->wait();
                    // $channel->wait(null, false, 120);
                } catch (\Exception $e) {
                    echo "[Datetime: " . date('Y-m-d H:i:s') . "]\n";
                    echo "[Received: Exception Message:" . $e->getMessage() . "]\n";
                    $channel->close();
                    $connection->close();
                }
            }
            //关闭信道
            $channel->close();
            //关闭连接
            $connection->close();
        } catch (\Exception $e) {
            echo 'ModeSimple 遇到执行错误：' . $e->getMessage() . "\n";
            $channel->close();
            $connection->close();
        }
    }

    /**
     * 关闭
     * 消费端 消费端需要保持运行状态实现方式
     * 1 linux上写定时任务每隔5分钟运行下该脚本，保证访问服务器的ip比较平缓，不至于崩溃
     * 2 nohup php index.php index/Message_Consume/start &  用nohup命令后台运行该脚本
     **/
    function shutdown($channel, $connection)
    {
        $channel->close();
        $connection->close();
        // Log::write("closed", 3);
    }
    /**
     * 回调处理信息
     * 消息处理
     */
    function process_message($message)
    {
        //休眠两秒
        //sleep(2);
        echo  $message->body . "\n";
        //自定义日志为rabbitmq-consumer
        // Log::write($message->body, 'rabbitmq-consumer');
        //[2021-01-14T16:14:17+08:00][rabbitmq-consumer] {"time":1610612057,"order":85}


        if ($message->body !== 'quit') {
            print_r($message->body);
        }
        //手动应答
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        // Send a message with the string "quit" to cancel the consumer.
        if ($message->body === 'quit') {
            $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        }
    }
}
