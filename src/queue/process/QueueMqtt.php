<?php

namespace shiyunQueue\process;

use shiyunWorker\WorkermanServer;

class QueueMqtt extends WorkermanServer
{
    protected $processes    = 1;
    protected $socket       = 'tcp://0.0.0.0:16040';
    protected $workerName   = 'queue_mqtt';
    // 心跳间隔40秒
    protected $heartbeat_time = 40;
    /**
     * 构造函数
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    }
    protected function init() {}
    public function onWorkerStart()
    {
        // $mqtt = new QueueClient('mqtt://test.mosquitto.org:1883');
        // $mqtt = new Workerman\Mqtt\Client('mqtt://test.mosquitto.org:1883');
        // $mqtt->onConnect = function ($mqtt) {
        //     $mqtt->subscribe('test');
        // };
        // $mqtt->onMessage = function ($topic, $content) {
        //     var_dump($topic, $content);
        // };
        // $mqtt->connect();
    }
}
