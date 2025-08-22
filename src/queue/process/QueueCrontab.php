<?php

namespace shiyunQueue\process;

use shiyunWorker\WorkermanServer;
use shiyunWorker\WorkermanCrontab;
use shiyun\libs\LibLogger;
use shiyunQueue\exception\CrontabException;

/**
 * 队列
 * 系统定时任务，限制内网访问
 * Crontab 定时器
 * Crontab 定时任务(每分钟检查运行脚本【分，时，日，月，周】)
 */
class QueueCrontab extends WorkermanServer implements InterfaceProcess
{
    protected $processes    = 1;
    protected $socket       = 'tcp://0.0.0.0:16010';
    protected $workerName   = 'queue_crontab';
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
        $this->config = syGetConfig('shiyun.process_crontab');
        if (
            !empty($this->config)
            && !empty($this->config['process_open'])
            && $this->config['process_open'] == true
        ) {
            $annoPath = $this->config['process_include_path'] ?? [];
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
                        $this->queueLog('Consumer', "目录{$itemDir}不存在");
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
            $this->queueLog('Exception', $e->getMessage());
        } catch (\Throwable $e) {
            $this->queueLog('Throwable', $e->getMessage());
        }
    }
    protected function queueLog(string $type = '', string $msg = '')
    {
        $log = str_pad("__{$type}__", 30, " ") . $msg;
        LibLogger::getInstance()->setGroup('queue_crontab')->writeError($log);
    }
    public function dealInit($consumeClassOpt = []) {}
    public function dealItemCheck($file)
    {
        try {
            $fileinfo = new \SplFileInfo($file);
            $ext = $fileinfo->getExtension();
            if ($ext !== 'php') {
                throw new CrontabException($file . ' 不是php文件');
            }
            $pathname = $fileinfo->getPathname();
            $class = str_replace(".php", "", $pathname);
            $class = str_replace('/', "\\", $class);
            // echo substr($file, strlen(_PATH_PROJECT_)) . "\n";
            // $class = substr(substr($file, strlen(_PATH_PROJECT_)), 0, -4);
            // $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, _PATH_PROJECT_);
            // $class = substr($pathname, strlen($filePath) + 0, -4);
            if (!class_exists($class)) {
                throw new CrontabException($class . ' 类不存在');
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
            if (!method_exists($consumeClassObj, 'onCrontabMessage')) {
                throw new CrontabException($class . ' -> onCrontabMessage 执行方法不存在');
            }
            /**
             * 判断参数是否存在
             */
            if (!property_exists($consumeClassObj, 'execute_timing')) {
                throw new CrontabException($class . ' -> execute_timing 参数不存在');
            }
            /**
             * 空值跳过
             */
            if (empty($consumeClassOpt['execute_timing'])) {
                return false;
            }

            // $consumeQeObj = \shiyunQueue\QueueFactory::getInstance()
            //     ->connection($consumeClassOpt['connect_name']);

            $that = $this;

            // 是否存在定时器
            if (!empty($consumeClassOpt['execute_timing'])) {
                $execute_timing = trim($consumeClassOpt['execute_timing']);
                new WorkermanCrontab(
                    $execute_timing,
                    function () use ($consumeClassObj) {
                        \call_user_func([$consumeClassObj, 'onCrontabMessage']);
                    }
                );
            }
        } catch (CrontabException $e) {
            $this->queueLog('CrontabException', $e->getMessage());
        } catch (\Throwable $e) {
            $this->queueLog('Throwable', $e->getMessage());
        } finally {
            return false;
        }
    }
    public function dealItemData($consumeClassObj, $consumeClassOpt, $consumeQeObj) {}
}
