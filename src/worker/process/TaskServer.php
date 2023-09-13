<?php

namespace shiyunWorker\process;

use shiyunWorker\WorkermanServer;
use Workerman\Worker;
use Workerman\Timer;
use think\cache\driver\Redis;

class TaskServer extends WorkermanServer
{
    protected $processes = 1;
    // 心跳间隔40秒
    protected $heartbeat_time = 40;
    protected $redis = '';
    protected $data = '';
    protected $msg = ['code' => 0, 'msg' => '您的账号已在别处登录'];
    /**
     * 构造函数
     * @access public
     */
    public function __construct()
    {
        // 实例化 Websocket 服务
        $this->worker = new Worker('websocket://0.0.0.0:2346');
        // 设置进程数	
        $this->worker->count = $this->processes;
        $this->init(); //初始化
        // 设置回调
        foreach ($this->event as $event) {
            if (method_exists($this, $event)) {
                $this->worker->$event = [$this, $event];
            }
        }
        // Run worker
        Worker::runAll();
    }
    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        $this->data = $data;
        //登录连接时分配一个全局唯一的uid
        $connection->uid = uniqid('xxx_');
        //追入redis中
        $this->redis->hSet('key', $connection->uid, $data);
        $expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
        //设置键的过期时间
        $this->redis->expireAt('hxt', $expireTime);
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        // redis 不存在则实例化Redis对象， 并连接
        if ($this->redis == '') {
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);
        }
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        if (isset($connection->uid)) {
            //找出userid 对应key
            $cid = $connection->uid;
            //清除redis中对应id
            $this->redis->hDel('key', $cid);
        }
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        //var_dump($worker->connections);
        Timer::add(1, function () use ($worker) {
            $time_now = time();

            if ($this->redis == '') {
                $this->redis = new Redis();
                $this->redis->connect('127.0.0.1', 6379);
            }

            //取出redis中所有连接客户端key
            $arr = $this->redis->hGetAll('key');
            //查询当前登录id是否在redis中
            $true = array_search($this->data, $arr);

            $sum = $this->get_array_repeats($tarr, $this->data);

            foreach ($worker->connections as $connection) {
                if (isset($connection->uid) && $connection->uid == $true && $sum >= 2) {
                    $connection->send(json_encode($this->msg)); // 发送给客户端
                    $connection->close();
                }


                $jicheng = $connection;
                // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间

                if (empty($jicheng->lastMessageTime)) {
                    $jicheng->lastMessageTime = $time_now;
                    continue;
                }
                // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                if ($time_now - $jicheng->lastMessageTime >  $this->heartbeat_time) {
                    // $connection->close();
                    echo "下线";
                }
            }
        });
    }

    //计算$string在$array(需为数组)中重复出现的次数
    public function get_array_repeats(array $array, $string)
    {

        $count = array_count_values($array);
        //统计中重复元素的次数，再重组数组， 

        if (key_exists($string, $count)) {
            return $count[$string];
        } else {
            return 0;
        }
    }
}
