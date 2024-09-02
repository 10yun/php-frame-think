<?php

namespace shiyunQueue\libs;

class ProcessStopping
{
    /**
     * 退出状态
     */
    public int $status;
    /**
     * 创建新的事件实例
     * @param int $status
     * @return void
     */
    public function __construct(int $status = 0)
    {
        $this->status = $status;
    }
    public function handle() {}
}
