<?php

namespace shiyunQueue\drive;

trait TraitLog
{
    /**
     * 记录日志
     * @var string|callable|array
     */
    protected $logData;
    public function addLog($log)
    {
        $this->logData = $log;
        return $this;
    }
    public function clearLogSett()
    {
        $this->logData = null;
    }
}
