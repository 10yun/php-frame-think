<?php

namespace shiyunWorker\process;

use Workerman\Worker;

class StatisticsServer
{
    public function __construct($config = '')
    {
        if (is_string($config)) {
            if ($config == '') {
                $configDiy = syGetConfig('shiyun.rpc_server_statistics');
                $configDef = include_once dirname(__DIR__) . '/config/rpc_server_statistics.php';
                $config  = array_merge($configDef, $configDiy);
            } else {
                $config = include_once $config;
            }
        }
        /**
         * StatisticProvider
         */
        $statistic_provider       = new \Statistics\Bootstrap\StatisticProvider("Text://0.0.0.0:55858");
        $statistic_provider->name = 'StatisticProvider';
        /**
         * StatisticWorker
         */
        $statistic_worker            = new \Statistics\Bootstrap\StatisticWorker(
            "\Statistics\Protocols\Statistic://0.0.0.0:{$config['statistics_port']}"
        );
        $statistic_worker->transport = 'udp';
        $statistic_worker->name      = 'StatisticWorker';
        /**
         * WebServer
         */
        $web       = new \shiyunWorker\WebServer("http://0.0.0.0:{$config['web_port']}");
        $web->name = 'StatisticWeb';
        $web->addRoot($config['host'], dirname(__DIR__) . '/Statistics/Web');
        /**
         * recv udp broadcast
         */
        $udp_finder            = new \Workerman\Worker("Text://0.0.0.0:55858");
        $udp_finder->name      = 'StatisticFinder';
        $udp_finder->transport = 'udp';
        $udp_finder->onMessage = function ($connection, $data) {
            $data = json_decode($data, true);
            if (empty($data)) {
                return false;
            }
            // 无法解析的包
            if (empty($data['cmd']) || $data['cmd'] != 'REPORT_IP') {
                return false;
            }
            // response
            return $connection->send(json_encode(array('result' => 'ok')));
        };
        // 如果不是在根目录启动，则运行runAll方法
        if (!defined('GLOBAL_START')) {
            // \Workerman\Worker::runAll();
        }
    }
    public function start()
    {
        Worker::runAll();
    }
}
