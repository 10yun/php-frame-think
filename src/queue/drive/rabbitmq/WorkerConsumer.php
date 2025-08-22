<?php

namespace shiyunQueue\drive\rabbitmq;

use Bunny\Channel;
use Bunny\Message;
use Workerman\RabbitMQ\Client;
use shiyunWorker\WorkermanServer;
use Workerman\Worker;

#[QueueModeSimple(type: 'rabbitmq', socket: 'tcp://127.0.0.1:2346')]
class AmqpConsumer extends WorkermanServer
{
    protected $socket = 'tcp://127.0.0.1:2346';
    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
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
        })->then(function (Channel $channel) {
            echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
            $channel->consume(
                function (Message $message, Channel $channel, Client $client) {
                    echo " [x] Received ", $message->content, "\n";
                    echo "接收消息内容：", $message->content, "\n";
                },
                'ceshi',
                '',
                false,
                true
            );
        });
    }
}
