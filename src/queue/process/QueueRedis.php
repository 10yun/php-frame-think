<?php

namespace shiyunQueue\process;

use shiyunWorker\WorkermanServer;
use Workerman\Worker;
use shiyunQueue\libs\ProcessWorker;

class QueueRedis extends WorkermanServer
{
    use ProcessWorker;

    protected $processes    = 3;
    protected $socket       = 'tcp://0.0.0.0:16020';
    protected $workerName   = 'queue_redis';
    // 心跳间隔40秒
    protected $heartbeat_time = 40;
    /**
     * 
     */
    protected $annoHandle;        // 注解工具类
    protected $_consumerDir = ''; // 队列消费目录
    /**
     * 构造函数
     * @access public
     */
    public function __construct()
    {
        $annoPath = syGetConfig('shiyun.queue.annotation_include_path');
        $this->annoHandle = new \shiyunQueue\annotation\AnnotationParse();
        $annoArr = $this->annoHandle->getDir($annoPath);
        $this->_consumerDir = $annoArr;
        parent::__construct();
    }
    //在进程开启之时
    public function onWorkerStart()
    {
        try {
            if (!empty($this->_consumerDir) && is_array($this->_consumerDir)) {
                $this->dealInit();
                foreach ($this->_consumerDir as $itemDir) {
                    if (!is_dir($itemDir)) {
                        echo "Consumer directory {$itemDir} not exists\r\n";
                        return;
                    }
                    $dir_iterator = new \RecursiveDirectoryIterator($itemDir);
                    $iterator = new \RecursiveIteratorIterator($dir_iterator);
                    foreach ($iterator as $file) {
                        if (is_dir($file)) {
                            continue;
                        }
                        if (
                            !($itemRes = $this->dealItemCheck($file))
                        ) {
                            continue;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            frameLogsQueue('执行消息队列发生错误,错误原因: Throwable ' . $e->getMessage());
        } catch (\PDOException $e) {
            frameLogsQueue('执行消息队列发生错误,错误原因: PDOException ' . $e->getMessage());
        } catch (\Exception $e) {
            frameLogsQueue('执行消息队列发生错误,错误原因: Exception ' . $e->getMessage());
        }
    }
    /**
     * 处理-=普通模式
     */
    /**
     * 处理 发布订阅
     */
}
