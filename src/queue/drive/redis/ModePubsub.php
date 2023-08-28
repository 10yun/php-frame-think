<?php

namespace shiyunQueue\drive\redis;

class ModePubsub
{
    protected $driveConnect  = null;

    // 将任务推送到消息队列等待对应的消费者去执行
    public function pub($msg)
    {
        if (empty($msg)) {
            return false;
        }
        //向Redis的send_captcha队列投递数据
        $isPush = $this->driveConnect->lPush('send_captcha', json_encode($send_data));
        $this->driveConnect->publish('chan-1', 'this is a message by chan1');
        $this->driveConnect->publish('chan-2', 'this is a message by chan2111111111111111');
        $status = $this->driveConnect->push($job, $msg, $queue_name);
        return $status;
    }
    public function sub()
    {
        while (true) {
            try {
                if (null === $this->driveConnect) {
                    $this->init();
                }
                $this->driveConnect->subscribe(['chan-1', 'chan-2'], [$this, 'task']);  // 第一个参数为订阅哪个频道，第二个参数为响应回调函数名称
            } catch (\Throwable $e) {
                frameLogs('LOGS_CHANNEL_FILE', date("Y-m-d H:i:s") . ",error:" . $e->getMessage());
                frameLogs('LOGS_CHANNEL_FILE', "redis reconnected 10 seconds later");
                $this->driveConnect = null;
                sleep(10);
            }
        }
    }
    public function task($instance, $channelName, $message)
    {
        switch ($channelName) {
            case 'chan-1':
                var_dump('chan-1' . $message);
                break;
            case 'chan-2':
                var_dump('chan-2' . $message);
                break;
        }
    }
}
