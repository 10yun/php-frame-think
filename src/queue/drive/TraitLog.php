<?php

namespace shiyunQueue\drive;

trait TraitLog
{
    /**
     * 记录日志
     */
    protected mixed $logData;
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
