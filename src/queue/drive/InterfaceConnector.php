<?php

namespace shiyunQueue\drive;

interface InterfaceConnector
{
    /**
     * 获取发布的内容
     */
    public function getPublish();
    /**
     * 发布内容
     */
    public function sendPublish(array|string|int|null $msg = null);
}
