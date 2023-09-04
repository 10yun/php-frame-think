<?php

namespace shiyunQueue\drive;

abstract class FailedJob
{
    /**
     * 将失败的作业记录到存储中。
     * @param string     $connection
     * @param string     $queue
     * @param string     $payload
     * @param \Exception $exception
     * @return int|null
     */
    abstract public function log($connection, $queue, $payload, $exception);
    /**
     * 获取所有失败作业的列表
     * @return array
     */
    abstract public function all();
    /**
     * 做一次失败的工作。
     * @param mixed $id
     * @return object|null
     */
    abstract public function find($id);
    /**
     * 从存储中删除单个失败的作业。
     * @param mixed $id
     * @return bool
     */
    abstract public function forget($id);
    /**
     * 从存储中清除所有失败的作业。
     * @return void
     */
    abstract public function flush();
}
