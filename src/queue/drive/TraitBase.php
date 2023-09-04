<?php

namespace shiyunQueue\drive;

trait TraitBase
{
    /**
     * 错误次数
     */
    protected int $allowError = 3;
    /**
     * 初始化
     */
    public function baseInit()
    {
        $this->initMsgSett();
        $this->initJobSett();
    }
    /**
     * 清除数据
     */
    public function baseClean()
    {
        $this->allowError = 3;
        $this->clearChannelSett();
        $this->clearMsgSett();
        $this->clearJobSett();
        $this->clearLogSett();
    }
    public function allowError()
    {
        return $this;
    }
}
