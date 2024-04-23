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

use DateTimeInterface;
use InvalidArgumentException;
use think\App;
use think\helper\Str;
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

    /** @var App */
    protected $app;
    protected $options = [];

    abstract public function size($queue = null);

    public function push($job, $data = '', $queue = null)
    {
    }
    abstract public function retryPublish($payload, $queue = null, array $options = []);
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }
    public function getPublish($queue = null)
    {
        return [];
    }
    protected function createPayload($job, $data = '')
    {
        if (is_object($job)) {
            $payload =  [
                'job'       => 'shiyunQueue\drive\CallQueuedHandler@call',
                'maxTries'  => $job->tries ?? null,
                'timeout'   => $job->timeout ?? null,
                'timeoutAt' => $this->getJobExpiration($job),
                'data'      => [
                    'commandName' => get_class($job),
                    'command'     => serialize(clone $job),
                ],
            ];
        } else {
            $payload = array_merge([
                'job'      => $job,
                'maxTries' => null,
                'timeout'  => null,
            ], $data);
        }
        /**
         * 随机id
         */
        $randomID = Str::random(32);
        $payload = array_merge($payload, [
            'id'       => $randomID,
            'attempts' => 0,
        ]);
        $payload = json_encode($payload);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Unable to create payload: ' . json_last_error_msg());
        }
        return $payload;
    }

    public function getJobExpiration($job)
    {
        if (!method_exists($job, 'retryUntil') && !isset($job->timeoutAt)) {
            return;
        }
        $expiration = $job->timeoutAt ?? $job->retryUntil();
        return $expiration instanceof DateTimeInterface
            ? $expiration->getTimestamp() : $expiration;
    }
    protected function setMeta($payload, $key, $value)
    {
        $payload       = json_decode($payload, true);
        $payload[$key] = $value;
        $payload       = json_encode($payload);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('【queue】Unable to create payload: ' . json_last_error_msg());
        }
        return $payload;
    }
    public function setApp(App $app)
    {
        $this->app = $app;
        return $this;
    }
}
