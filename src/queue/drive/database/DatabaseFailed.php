<?php

namespace shiyunQueue\drive\database;

use think\Db;
use shiyunQueue\drive\FailedJob;

class DatabaseFailed extends FailedJob
{
    /** 
     * @var Db 
     */
    protected $db;

    /**
     * The database table.
     */
    protected string $table;

    public function __construct(Db $db, $table)
    {
        $this->db    = $db;
        $this->table = $table;
    }

    public static function __make(Db $db, $config)
    {
        return new self($db, $config['table']);
    }

    /**
     * Log a failed job into storage.
     *
     * @param string     $connection
     * @param string     $queue
     * @param string     $payload
     * @param \Exception $exception
     * @return int|null
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $fail_time = (new \DateTime())->format('Y-m-d H:i:s');

        $exception = (string) $exception;
        return $this->getTable()->insertGetId(compact(
            'connection',
            'queue',
            'payload',
            'exception',
            'fail_time'
        ));
    }

    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function all()
    {
        return collect($this->getTable()->order('id', 'desc')->select())->all();
    }

    /**
     * Get a single failed job.
     *
     * @param mixed $id
     * @return object|null
     */
    public function find($id)
    {
        return $this->getTable()->find($id);
    }

    /**
     * Delete a single failed job from storage.
     *
     * @param mixed $id
     * @return bool
     */
    public function forget($id)
    {
        return $this->getTable()->where('id', $id)->delete() > 0;
    }

    /**
     * Flush all of the failed jobs from storage.
     *
     * @return void
     */
    public function flush()
    {
        $this->getTable()->delete(true);
    }

    protected function getTable()
    {
        return $this->db->name($this->table);
    }
}
