<?php

namespace shiyunQueue\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;

class QueueWorker extends Command
{
    protected function configure()
    {
        $this->setName('worker:queue')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload|status|connections", 'start')
            //->addOption('d', 'd', Option::VALUE_OPTIONAL, ' daemon ')
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'Run the workerman server in daemon mode.')
            ->setDescription(' Workerman queue start ');
    }

    public function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');

        if (DIRECTORY_SEPARATOR !== '\\') {
            if (!in_array($action, ['start', 'stop', 'reload', 'restart', 'status', 'connections'])) {
                $output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart|reload|status|connections .</error>");
                return false;
            }
            global $argv;
            array_shift($argv);
            array_shift($argv);
            array_unshift($argv, 'think', $action);
        } elseif ('start' != $action) {
            $output->writeln("<error>Not Support action:{$action} on Windows.</error>");
            return false;
        }
        if ('start' == $action) {
            $output->writeln('Starting Workerman server...');
        }
        try {
            ini_set('display_errors', 'on');
            // 设置时区，避免运行结果与预期不一致
            date_default_timezone_set('PRC');
            // 标记是全局启动
            define('GLOBAL_START', 1);
            new \shiyunQueue\process\QueueCrontab();
            new \shiyunQueue\process\QueueRedis();
            // new \shiyunQueue\process\QueueMqtt();
            // new \shiyunQueue\process\QueueAmqp();
            // Worker::$pidFile = syPathRuntime() . 'workerman_queue_pid';
            \Workerman\Worker::runAll();
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
