<?php

namespace shiyunWorker\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use think\App;

class Task extends Command
{
    public function configure()
    {
        $this->setName('worker:task')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload|status|connections", 'start')
            ->setDescription('Task Server for ThinkPHP');
    }
    public function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');

        if (DIRECTORY_SEPARATOR !== '\\') {
            if (!in_array($action, ['start', 'stop', 'reload', 'restart', 'status', 'connections'])) {
                $output->writeln("Invalid argument action:{$action}, Expected start|stop|restart|reload|status|connections .");
                exit(1);
            }

            global $argv;
            array_shift($argv);
            array_shift($argv);
            array_unshift($argv, 'think', $action);
        }

        if ('start' == $action) {
            $output->writeln('Starting WorkerTask server...');
        }

        $configs = include_once __DIR__ . '/../config/config.php';
        $option = Config::get('worker_task');
        if ($input->hasOption('host')) {
            $host = $input->getOption('host');
        } else {
            $host = !empty($option['host']) ? $option['host'] : $configs['host'];
        }

        if ($input->hasOption('port')) {
            $port = $input->getOption('port');
        } else {
            $port = !empty($option['port']) ? $option['port'] : $configs['port'];
        }
        if (empty($option['count'])) {
            $option['count'] = $configs['count'];
        }
        if (empty($option['name'])) {
            $option['name'] = $configs['name'];
        }
        if (empty($option['reusePort'])) {
            if (isset($option['reusePort'])) {
                $option['reusePort'] = false;
            } else {
                $option['reusePort'] = $configs['reusePort'];
            }
        }
        if (empty($option['error_path'])) {
            $option['error_path'] = $configs['error_path'];
        }
        if ($input->hasOption('daemon')) {
            $option['daemon'] = true;
        }

        $this->start($host, (int) $port, $option);
    }
    public function start(string $host, int $port, array $option = [])
    {
        $task_worker = new \Workerman\Worker('tcp://' . $host . ':' . $port);
        $task_worker->count = $option['count']; //进程数
        $task_worker->name = $option['name']; //名称
        //只有php7才支持task->reusePort，可以让每个task进程均衡的接收任务
        $task_worker->reusePort = $option['reusePort'];
        if (isset($option['daemon']) && $option['daemon']) {
            \Workerman\Worker::$daemonize = true;
        }
        $task_worker->onMessage = function ($connection, $task_data) {
            // 假设发来的是json数据
            $task_data = json_decode($task_data, true);
            $key = null;
            if (isset($task_data['AsynchronousTaskProducerKey']) && $task_data['AsynchronousTaskProducerKey']) {
                $key = $task_data['AsynchronousTaskProducerKey'];
                unset($task_data['AsynchronousTaskProducerKey']);
            }

            try {
                if (isset($task_data['Processing']) && $task_data['Processing']) {
                    if (is_string($task_data['Processing'])) {
                        $obj = \app($task_data['Processing']);
                        if (method_exists($obj, 'onQueueMessage')) {
                            unset($task_data['Processing']);
                            $obj->onQueueMessage($task_data);
                        }
                    } elseif (is_object($task_data['Processing'])) {
                        $obj = $task_data['Processing'];
                        if (method_exists($obj, 'onQueueMessage')) {
                            unset($task_data['Processing']);
                            $obj->onQueueMessage($task_data);
                        }
                    } elseif (is_callable($task_data['Processing'])) {
                        $obj = $task_data['Processing'];
                        unset($task_data['Processing']);
                        call_user_func_array($obj, $task_data);
                    }
                }
            } catch (\Exception $e) {
                if (isset($option['error_path']) && $option['error_path']) {
                    file_put_contents($option['error_path'], $e->getMessage(), FILE_APPEND | LOCK_EX);
                }
            }
            if (!empty($key)) {
                $list = \think\facade\Cache::get('AsynchronousTaskProducer');
                $list = array_flip($list);
                unset($list[$key]);
                $list = array_values(array_flip($list));
                \think\facade\Cache::set('AsynchronousTaskProducer', $list);
            }
            $connection->close();
        };
        \think\facade\Cache::delete('AsynchronousTaskProducer');
        \Workerman\Worker::runAll();
    }
}
