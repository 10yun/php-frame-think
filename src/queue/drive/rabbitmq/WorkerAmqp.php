<?php

namespace shiyunQueue\drive\rabbitmq;

use Bunny\Channel;
use Bunny\Message;
use Workerman\Worker;
use Workerman\RabbitMQ\Client;
use shiyunWorker\WorkermanServer;

class WorkerAmqp extends WorkermanServer
{
    //websocket地址，一会用于测试。
    protected $socket = 'websocket://127.0.0.1:2345';
    protected $socket2 = 'tcp://127.0.0.1:6889';
    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    // onWorkerStart
    public function onMessage($connection, $data)
    {
        //websocket发送过来的消息
        $connection->send('我收到你的信息了:' . $data);
        //rabbitMQ配置
        $options = syGetConfig('shiyun.process_queue.connection.AMQP');
        (new Client($options))->connect()->then(function (Client $client) {
            return $client->channel();
        })->then(function (Channel $channel) {
            /**
             * 创建队列(Queue)
             * name: ceshi         // 队列名称
             * passive: false      // 如果设置true存在则返回OK，否则就报错。设置false存在返回OK，不存在则自动创建
             * durable: true       // 是否持久化，设置false是存放到内存中RabbitMQ重启后会丢失,
             *                        设置true则代表是一个持久的队列，服务重启之后也会存在，因为服务会把持久化的Queue存放在硬盘上，当服务重启的时候，会重新加载之前被持久化的Queue
             * exclusive: false    // 是否排他，指定该选项为true则队列只对当前连接有效，连接断开后自动删除
             *  auto_delete: false // 是否自动删除，当最后一个消费者断开连接之后队列是否自动被删除
             */
            return $channel->queueDeclare('ceshi', false, true, false, false)->then(function () use ($channel) {
                return $channel;
            });
            return $channel->queueDeclare('ceshi', false, false, false, false)->then(function () use ($channel) {
                return $channel;
            });
        })->then(function (Channel $channel) use ($data) {
            echo "发送消息内容：" . $data . "\n";
            echo " [x] Sending 'Hello World!'\n";
            /**
             * 发送消息
             * body 发送的数据
             * headers 数据头，建议 ['content_type' => 'text/plain']，这样消费端是springboot注解接收直接是字符串类型
             * exchange 交换器名称
             * routingKey 路由key
             * mandatory
             * immediate
             * @return bool|PromiseInterface|int
             */

            return $channel->publish($data, ['content_type' => 'text/plain'], '', 'ceshi')->then(function () use ($channel) {
                return $channel;
            });
            return $channel->publish('Hello World!', [], '', 'ceshi111')->then(function () use ($channel) {
                return $channel;
            });
        })->then(function (Channel $channel) {
            //echo " [x] Sent 'Hello World!'\n";
            $client = $channel->getClient();
            return $channel->close()->then(function () use ($client) {
                return $client;
            });
        })->then(function (Client $client) {
            $client->disconnect();
        });
    }
}
