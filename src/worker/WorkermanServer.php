<?php

namespace shiyunWorker;

use Workerman\Worker;

/**
 * Worker控制器扩展类
 */
abstract class WorkermanServer
{
    protected $worker;
    protected $workerName = '';
    protected $socket   = '';
    protected $protocol = 'http';
    protected $host     = '0.0.0.0';
    protected $port     = '2346';
    protected $processes = 1;
    protected $option   = [];
    protected $context  = [];
    protected $workerEvent = [
        'onWorkerStart', 'onConnect', 'onMessage', 'onClose', 'onError',
        'onBufferFull', 'onBufferDrain',
        'onWorkerStop', 'onWorkerReload', 'onWebSocketConnect'
    ];
    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        // 实例化 Websocket 服务
        $this->worker = new Worker($this->socket ?: $this->protocol . '://' . $this->host . ':' . $this->port, $this->context);
        // 设置进程数
        $this->worker->count = $this->processes;
        // 设置参数
        $this->worker->name  = $this->workerName;
        if (!empty($this->option)) {
            foreach ($this->option as $key => $val) {
                $this->worker->$key = $val;
            }
        }
        // 设置回调
        foreach ($this->workerEvent as $event) {
            if (method_exists($this, $event)) {
                $this->worker->$event = [$this, $event];
            }
        }
        // 初始化
        $this->init();
    }
    protected function init()
    {
    }
    // 设置进程数
    public function setCount(int $count = 1)
    {
        $this->worker->count = $count;
        return $this;
    }
    public function start()
    {
        Worker::runAll();
    }
    public function __set($name, $value)
    {
        $this->worker->$name = $value;
    }
    public function __call($method, $args)
    {
        call_user_func_array([$this->worker, $method], $args);
    }
}
