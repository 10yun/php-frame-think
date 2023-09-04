<?php

namespace shiyunQueue\libs;

use Exception;
use shiyunQueue\drive\Job;

class JobExceptionOccurred
{
    /**
     * The connection name.
     */
    public string $connectionName;

    /**
     * The job instance.
     *
     * @var Job
     */
    public $job;

    /**
     * The exception instance.
     *
     * @var Exception
     */
    public $exception;

    /**
     * Create a new event instance.
     *
     * @param string    $connectionName
     * @param Job       $job
     * @param Exception $exception
     * @return void
     */
    public function __construct($connectionName, $job, $exception)
    {
        $this->job            = $job;
        $this->exception      = $exception;
        $this->connectionName = $connectionName;
    }

    public function handle()
    {
    }
}
