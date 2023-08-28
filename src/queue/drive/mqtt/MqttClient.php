<?php

use PhpMqtt\Client\MqttClient;

class MqttClient
{
    public function __construct()
    {
        $server   = 'broker-cn.emqx.io';
        $port     = 1883;
        $clientId = rand(5, 15);
        $username = 'emqx_user';
        $password = null;
        $clean_session = false;

        $connectionSettings  = new ConnectionSettings();
        $connectionSettings
            ->setUsername($username)
            ->setPassword(null)
            ->setKeepAliveInterval(60)
            // Last Will 设置
            ->setLastWillTopic('emqx/test/last-will')
            ->setLastWillMessage('client disconnect')
            ->setLastWillQualityOfService(1);
    }
    // 订阅消息
    public function subscribe($topic, $message)
    {
        // 订阅
        $mqtt->subscribe('emqx/test', function ($topic, $message) {
            printf("Received message on topic [%s]: %s\n", $topic, $message);
        }, 0);
    }

    // 发送消息
    public function send()
    {
        for ($i = 0; $i < 10; $i++) {
            $payload = array(
                'protocol' => 'tcp',
                'date' => date('Y-m-d H:i:s'),
                'url' => 'https://github.com/emqx/MQTT-Client-Examples'
            );
            $mqtt->publish(
                // topic
                'emqx/test',
                // payload
                json_encode($payload),
                // qos
                0,
                // retain
                true
            );
            printf("msg $i send\n");
            sleep(1);
        }

        // 客户端轮询以处理传入消息和重发队列
        $mqtt->loop(true);
    }
}
