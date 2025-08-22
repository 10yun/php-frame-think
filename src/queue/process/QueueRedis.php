<?php

namespace shiyunQueue\process;

use Exception;
use shiyunWorker\WorkermanServer;
use shiyun\libs\LibLogger;
use shiyunQueue\libs\ProcessWorker;
use shiyunQueue\exception\QueueException;

class QueueRedis extends WorkermanServer
{
    use ProcessWorker;

    protected $processes    = 3;
    protected $socket       = 'tcp://0.0.0.0:16030';
    protected $workerName   = 'queue_redis';
    // 心跳间隔30秒
    protected $heartbeat_time = 30;
    /**
     * 
     */
    protected $annoHandle;        // 注解工具类
    protected $_consumerDir = ''; // 队列消费目录
    protected array $config = []; // 配置
    /**
     * 构造函数
     * @access public
     */
    public function __construct()
    {
        $this->config = syGetConfig('shiyun.process_queue');
        if (
            !empty($this->config)
            && !empty($this->config['process_open'])
            && $this->config['process_open'] == true
        ) {
            $annoPath = syGetConfig('shiyun.process_queue.annotation_include_path');
            $this->annoHandle = new \shiyunQueue\annotation\AnnotationParse();
            $annoArr = $this->annoHandle->getDir($annoPath);
            $this->_consumerDir = $annoArr;

            if (!empty($this->config['process_count'])) {
                $this->processes = $this->config['process_count'];
            }
            if (!empty($this->config['process_socket'])) {
                $this->socket = $this->config['process_socket'];
            }
            parent::__construct();
        }
    }
    // 在进程开启之时
    public function onWorkerStart()
    {
        try {
            if (!empty($this->_consumerDir) && is_array($this->_consumerDir)) {
                $this->dealInit();
                foreach ($this->_consumerDir as $itemDir) {
                    if (!is_dir($itemDir)) {
                        $this->queueLogError('Consumer', "目录{$itemDir}不存在");
                        return;
                    }
                    $dir_iterator = new \RecursiveDirectoryIterator($itemDir);
                    $iterator = new \RecursiveIteratorIterator($dir_iterator);
                    foreach ($iterator as $file) {
                        if (is_dir($file)) {
                            continue;
                        }
                        if (!$this->dealItemCheck($file)) {
                            continue;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->queueLogError('Exception', $e->getMessage());
        } catch (\Throwable $e) {
            $this->queueLogError('Throwable', $e->getMessage());
        }
    }
    protected function queueLogError(string $type = '', string $msg = '')
    {
        $log = str_pad("{$type}", 40, " ") . $msg;
        LibLogger::getInstance()->setGroup('queue_redis')->writeError($log);
    }
    protected function queueLogInfo(string $type = '', string $msg = '')
    {
        $log = str_pad("{$type}", 40, " ") . $msg;
        LibLogger::getInstance()->setGroup('queue_redis')->writeInfo($log);
    }
    /**
     * 处理-=普通模式
     */
    /**
     * 处理 发布订阅
     */
    public function dealInit($consumeClassOpt = []) {}
    public function dealItemCheck($file)
    {
        $tryState = true;
        try {
            $fileinfo = new \SplFileInfo($file);
            $ext = $fileinfo->getExtension();
            if ($ext !== 'php') {
                throw new QueueException($file . ' 不是php文件');
            }
            $pathname = $fileinfo->getPathname();
            $class = str_replace(".php", "", $pathname);
            $class = str_replace('/', "\\", $class);
            // echo substr($file, strlen(_PATH_PROJECT_)) . "\n";
            // $class = substr(substr($file, strlen(_PATH_PROJECT_)), 0, -4);
            // $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, _PATH_PROJECT_);
            // $class = substr($pathname, strlen($filePath) + 0, -4);
            if (!class_exists($class)) {
                throw new QueueException($class . ' 类不存在');
            }
            // 消费者参数
            $consumeClassOpt = [];
            // 消费者类
            $consumeClassObj = new $class;
            $reflectionClass = new \ReflectionClass($class);
            $properties = $reflectionClass->getProperties();

            foreach ($properties as $property) {
                $pro_key       = $property->getName();
                // 确保可以访问 protected 或 private 属性
                $property->setAccessible(true);
                $pro_val       = $property->getValue($consumeClassObj);
                $consumeClassOpt[$pro_key] = $pro_val;
            }
            /**
             * 方法是否存在
             */
            if (!method_exists($consumeClassObj, 'onQueueMessage')) {
                throw new QueueException($class . ' -> onQueueMessage 执行方法不存在');
            }
            /**
             * 判断参数是否存在
             */
            if (!property_exists($consumeClassObj, 'connect_name')) {
                throw new QueueException($class . ' -> connect_name 参数不存在');
            }
            if (!property_exists($consumeClassObj, 'queue_name')) {
                throw new QueueException($class . ' -> queue_name 参数不存在');
            }
            if (
                empty($consumeClassOpt['connect_name']) || empty($consumeClassOpt['queue_name'])
            ) {
                throw new QueueException($class . ' connect 参数为空');
            }

            $consumeClassOpt['exchange_name'] = !empty($consumeClassOpt['exchange_name']) ? $consumeClassOpt['exchange_name'] : 'queues';
            // $consumeClassOpt['exchange_name'] = !empty($consumeClassOpt['exchange_name']) ? $consumeClassOpt['exchange_name'] : '';

            $consumeQeObj = \shiyunQueue\QueueFactory::getInstance()
                ->connection($consumeClassOpt['connect_name']);

            $that = $this;

            // 是否存在定时器
            if (!empty($consumeClassOpt['execute_timing'])) {
                $execute_timing = intval($consumeClassOpt['execute_timing']);
                \Workerman\Timer::add(
                    $execute_timing,
                    function () use ($that, $consumeClassObj, $consumeClassOpt, $consumeQeObj) {
                        $xxx = $that->dealItemData($consumeClassObj, $consumeClassOpt, $consumeQeObj);
                        // var_dump($xxx);
                        // return $xxx ? 1 : 0;
                        // return $xxx;
                    }
                );
                // \Workerman\Timer::add(intval($execute_timing), [$consumeClassObj, 'onQueueMessage'], [
                //     ['msg1' => '队列消息的内容', 'msg2' => 2,],
                //     '参数2'
                // ]);
            } else {
                return $that->dealItemData($consumeClassObj, $consumeClassOpt, $consumeQeObj);
            }
            // $consumeQeObj->subscribe($queue, );
        } catch (QueueException $e) {
            $tryState = false;
            $this->queueLogError('dealItemCheck__CrontabException', $e->getMessage());
        } catch (\Throwable $e) {
            $tryState = false;
            $this->queueLogError('dealItemCheck__Throwable', $e->getMessage());
        } finally {
            return $tryState;
        }
    }
}
