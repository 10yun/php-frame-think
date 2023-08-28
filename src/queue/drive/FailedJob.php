<?php

namespace shiyunQueue\drive;

abstract class FailedJob
{
    /**
     * Log a failed job into storage.
     * 将失败的作业记录到存储中。
     * @param string     $connection
     * @param string     $queue
     * @param string     $payload
     * @param \Exception $exception
     * @return int|null
     */
    abstract public function log($connection, $queue, $payload, $exception);
    /**
     * Get a list of all of the failed jobs.
     * Get a list of all of the failed jobs.
     * @return array
     */
    abstract public function all();
    /**
     * Get a single failed job.
     * 做一次失败的工作。
     * @param mixed $id
     * @return object|null
     */
    abstract public function find($id);
    /**
     * Delete a single failed job from storage.
     * 从存储中删除单个失败的作业。
     * @param mixed $id
     * @return bool
     */
    abstract public function forget($id);
    /**
     * Flush all of the failed jobs from storage.
     * 从存储中清除所有失败的作业。
     * @return void
     */
    abstract public function flush();
}
