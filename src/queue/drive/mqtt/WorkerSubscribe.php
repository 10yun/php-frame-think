<?php

use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

$worker = new Worker();
$worker->onWorkerStart = function () {
    // $mqtt = new Workerman\Mqtt\Client('mqtt://test.mosquitto.org:1883');
    $mqtt = new Workerman\Mqtt\Client('mqtt://127.0.0.1:1883');
    $mqtt->onConnect = function ($mqtt) {
        $mqtt->subscribe('test');
    };
    $mqtt->onMessage = function ($topic, $content) {
        var_dump($topic, $content);
    };
    $mqtt->connect();
};
Worker::runAll();
