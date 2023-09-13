<?php

namespace shiyunWorker\process;

use shiyunWorker\WorkermanServer;
use shiyunWorker\libs\MdgApp;
use shiyunWorker\protocols\JsonNL;
use Statistics\Clients\StatisticClient;
use Workerman\Worker;



// // 开启的端口
// $worker = new Worker('JsonNL://0.0.0.0:2015');
// // 启动多少服务进程
// $worker->count = 16;
// // worker名称，php start.php status 时展示使用
// $worker->name = 'JsonRpc';

class JsonRpcServer extends WorkermanServer
{
    protected $statisticAddress = 'udp://127.0.0.1:55656';
    protected $workerName       = 'JsonRpcServerListener';
    protected $logFile          = '';
    protected $service          = [];

    public function __construct($config = '')
    {
        if (is_string($config)) {
            if ($config == '') {
                $configDiy = syGetConfig('shiyun.rpc_server');
                $configDef = include_once dirname(__DIR__) . '/config/rpc_server.php';
                $config  = array_merge($configDef, $configDiy);
            } else {
                $config = include_once $config;
            }
        }
        $this->processes        = $config['rpc_server']['processes'] ?? 4;
        $this->protocol         = $config['rpc_server']['protocol'] ?? 'http';
        $this->host             = $config['rpc_server']['host'] ?? '0.0.0.0';
        $this->port             = $config['rpc_server']['port'] ?? '2346';
        $this->socket           = $config['rpc_server']['socket'] ?? '';
        $this->workerName       = $config['rpc_server']['worker_name'] ?? 'shiyunWorker';
        $this->logFile          = $config['rpc_server']['log_file'] ?? '';
        $this->service          = $config['rpc_server']['service'] ?? [];

        $this->statisticAddress = $config['statistic_process']['socket'] ?? 'udp://127.0.0.1:55656';
        parent::__construct();
    }

    protected function init()
    {
        $this->worker->name = $this->workerName;
        if (!$this->logFile) {
            Worker::$logFile = $this->logFile;
        }
    }

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        $statistic_address = $this->statisticAddress;

        // 判断数据是否正确
        if (
            //  empty($data['serviceFirst']) ||
            empty($data['class']) || empty($data['method']) || !isset($data['param_array'])
        ) {
            // 发送数据给客户端，请求包错误
            return $connection->send(array('code' => 400, 'msg' => 'bad request', 'data' => null));
        }
        // 获得要调用的类、方法、及参数
        $class       = $data['class'];
        $method      = $data['method'];
        $param_array = $data['param_array'];


        StatisticClient::tick($class, $method);
        $success = false;

        /**
         * 判断类对应文件是否载入
         */
        // $worker_service_dir = root_path() . '/addons/' . $data['serviceFirst'];
        // $rpc_class = '';
        // $rpc_class = $class . 'Rpc';
        // StatisticClient::tick($rpc_class, $method);
        // if (!class_exists($class)) {
        //     $include_file = __DIR__ . "/Services/$class.php";
        //     $include_file = dirname(__DIR__) . "/demo/$class.php";
        //     $include_file = $worker_service_dir . "/$class/Rpc.php";
        //     if (is_file($include_file)) {
        //         require_once $include_file;
        //     }
        //     if (!class_exists($class) || !method_exists($class, $method)) {
        //         $code = 404;
        //         $msg = "class $class or method $method not found";
        //         StatisticClient::report($class, $method, $success, $code, $msg, $statistic_address);
        //         // 发送数据给客户端 类不存在
        //         return $connection->send(array('code' => $code, 'msg' => $msg, 'data' => null));
        //     }
        // }


        // 调用类的方法
        try {
            // 旧的写法
            // $ret = call_user_func_array(array($class, $method), $param_array);

            $ret = MdgApp::getInstance()->$class->$method(...$param_array);
            StatisticClient::report($class, $method, 1, 0, '', $statistic_address);
            // 发送数据给客户端，调用成功，data下标对应的元素即为调用结果
            return $connection->send(array('code' => 200, 'msg' => 'ok', 'data' => $ret));
        }
        // 有异常
        catch (\Throwable $e) {
            // 发送数据给客户端，发生异常，调用失败
            $code = $e->getCode() ? $e->getCode() : 500;
            StatisticClient::report($class, $method, $success, $code, $e, $statistic_address);
            return $connection->send(array('code' => $code, 'msg' => $e->getMessage(), 'data' => $e));
        }
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        //StatisticClient::report($class, $method, $success, $code, $e, $statistic_address);
        echo "error $code $msg\n";
    }
    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        if (!empty($this->service)) {
            foreach ($this->service as $key => $class) {
                MdgApp::getInstance()->bindTo([$key => $class]);
            }
        }
    }
}
