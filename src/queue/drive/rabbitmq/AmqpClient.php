<?php

namespace shiyunQueue\drive\rabbitmq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use shiyunQueue\drive\TraitConnect;
use shiyunQueue\drive\TraitChannel;

class AmqpClient
{
    use TraitConnect,
        TraitChannel;

    public function newConnection()
    {
        $host = '127.0.0.1';
        $port = 5672;
        $user = 'guest';
        $pwd = 'guest';
        $vhost = '/';
        $this->connection = new AMQPStreamConnection($host, $port, $user, $pwd, $vhost);

        $param = config('rabbitmq.AMQP');
        $amqpDetail = config('rabbitmq.email_queue');
        $amqpDetail = config('rabbitmq.direct_queue');
        $connection = new AMQPStreamConnection(
            $param['connect_host'],
            $param['connect_port'],
            $param['connect_user'],
            $param['connect_password'],
            $param['connect_vhost']
        );
        $channel = $connection->channel();
        $this->createChannel();
        return $this;
    }
    public function createChannel()
    {
        $this->channel = $this->connection->channel();
        return $this;
    }
    /**
     * 发送信息
     */
    public function sendPublish($msg = '')
    {

        /**
         * 创建交换机(Exchange)
         * @param name: vckai_exchange// 交换机名称
         * @param type: direct        // 交换机类型，分别为direct/fanout/topic，参考另外文章的Exchange Type说明。
         *		                        direct   直连方式
         *		                        fanout   
         *		                        topic     
         * @param passive: false      // 如果设置true存在则返回OK，否则就报错。设置false存在返回OK，不存在则自动创建
         * @param durable: false      // 是否持久化，设置false是存放到内存中的，RabbitMQ重启后会丢失
         * @param auto_delete: false  // 是否自动删除，当最后一个消费者断开连接之后队列是否自动被删除
         */
        $this->channel->exchange_declare($this->exchangeName, $this->exchangeType, false, true, false);

        /**
         * 创建队列(Queue)
         * @param name: hello         // 队列名称
         * @param passive: false      // 如果设置true存在则返回OK，否则就报错。设置false存在返回OK，不存在则自动创建
         * @param durable: true       // 是否持久化，设置false是存放到内存中RabbitMQ重启后会丢失,
         *                        设置true则代表是一个持久的队列，服务重启之后也会存在，因为服务会把持久化的Queue存放在硬盘上，当服务重启的时候，会重新加载之前被持久化的Queue
         * @param exclusive: false    // 是否排他，指定该选项为true则队列只对当前连接有效，连接断开后自动删除
         *		                队列可以通过其他渠道访问
         * @param auto_delete: false // 是否自动删除，当最后一个消费者断开连接之后队列是否自动被删除,
         *                    通道关闭后，队列不会被删除
         */
        $this->channel->queue_declare($this->queueName, false, true, false, false);

        /**
         * 绑定队列和交换机
         * @param string $queue 队列名称
         * @param string $exchange  交换器名称
         * @param string $routing_key   路由key
         * @param bool $nowait
         * @param array $arguments
         * @param int|null $ticket
         * @throws \PhpAmqpLib\Exception\AMQPTimeoutException if the specified operation timeout was exceeded
         * @return mixed|null
         */
        $this->channel->queue_bind($this->queueName, $this->exchangeName, $this->routeKey);

        /**
         * 创建AMQP消息类型
         * @param $messageBody:消息体
         * @param 
         *      content_type:消息的类型 可以不指定
         *      delivery_mode 消息是否持久化
         *          AMQPMessage::DELIVERY_MODE_NON_PERSISTENT = 1; 不持久化
         *          AMQPMessage::DELIVERY_MODE_PERSISTENT = 2; 持久化
         */
        if (!$this->is_json($msg)) {
            // 将要发送数据变为json字符串
            $messageBody = json_encode($msg);
        }
        $messageBody = $msg;
        $message = new AMQPMessage($messageBody, array(
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ));

        /**
         * 发送消息
         * @param msg            AMQP消息内容
         * @param exchange       交换机名称
         * @param routing key    路由键名称
         */
        $this->channel->basic_publish($message, $this->exchangeName, $this->routeKey);

        /**
         * 关闭连接
         */
        $this->channel->close();
        $this->connection->close();
        return true;
    }

    public function is_json($data = '', $assoc = false)
    {
        $data = json_decode($data, $assoc);
        if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
            return $data;
        }
        return false;
    }
}
