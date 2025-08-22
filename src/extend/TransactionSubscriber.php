<?php

declare(strict_types=1);

namespace shiyun\extend;

use shiyun\extend\TransactionManager;
use think\Event;
use think\Response;

class TransactionSubscriber
{
    protected $manager;

    public function __construct(TransactionManager $manager)
    {
        $this->manager = $manager;
    }
    /**
     * 注册与事件
     */
    public function subscribe(Event $event)
    {
        // 请求开始时检查是否需要开启事务
        // $event->listen('app_init', [$this, onTaskStart]);
        // $event->listen('dbStartTask', [$this, 'onTaskStart']);
        $event->listen('http_start', [$this, 'onTaskStart']);
        $event->listen('http_end', [$this, 'onRequestEnd']);
        // 请求成功时提交事务
        $event->listen('dbCommit', [$this, 'onTaskSuccess']);
        // 请求异常时回滚事务
        $event->listen('dbRollback', [$this, 'onTaskFail']);
        $event->listen('app_error', [$this, 'onTaskFail']);
    }
    public function onTaskStart()
    {
        if ($this->manager->shouldStartTransaction()) {
            $this->manager->begin();
        }
    }
    public function onRequestEnd($response)
    {
        // if (request()->isSuccess()) { // 需要定义成功条件
        //     $this->onTaskSuccess();
        // }
        $this->manager->checkResponse($response);
    }
    public function onTaskSuccess()
    {
        $this->manager->commit();
    }
    public function onTaskFail()
    {
        $this->manager->rollback();
    }
}
