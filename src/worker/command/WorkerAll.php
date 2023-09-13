<?php

namespace shiyunWorker\command;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use Workerman\Worker;

/**
 * Worker 命令行类
 */
class WorkerAll extends Command
{
    public function configure()
    {
        $this->setName('worker:all')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload|status|connections", 'start')
            ->setDescription('Worker all Server for shiyun');
    }
    public function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');
        if ($action == 'start') {
            Worker::runAll();
        } else if ($action == 'stop') {
            // exec("ps aux|grep -i workerman|awk '{print $2}'|xargs kill -9");
            exec("killall -9 php");
            // var_dump('???');
            // Worker::stopAll();
        }
    }
}
