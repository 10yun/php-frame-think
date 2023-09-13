<?php

if (PHP_SAPI == 'cli' && isset($argv[0]) && $argv[0] == basename(__FILE__)) {
    StatisticClient::tick("TestModule", 'TestInterface');
    usleep(rand(10000, 600000));
    $success = rand(0, 1);
    $code = rand(300, 400);
    $msg = '这个是测试消息';
    var_export(StatisticClient::report('TestModule', 'TestInterface', $success, $code, $msg));;
}
