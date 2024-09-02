<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace shiyunQueue\drive;

use shiyunQueue\drive\TraitBase;
use shiyunQueue\drive\TraitConnect;
use shiyunQueue\drive\TraitChannel;
use shiyunQueue\drive\TraitJob;
use shiyunQueue\drive\TraitMessage;
use shiyunQueue\drive\TraitLog;

abstract class Connector
{
    use TraitBase,
        TraitConnect,
        TraitChannel,
        TraitJob,
        TraitMessage,
        TraitLog;

    protected $options = [];

    abstract public function size($queue = null);
    abstract public function retryPublish($payload, $queue = null, array $options = []);

    public function push($job, $data = '', $queue = null) {}
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }
    public function getPublish()
    {
        return [];
    }
}
