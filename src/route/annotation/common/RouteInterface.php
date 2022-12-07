<?php

declare(strict_types=1);

namespace shiyun\route\annotation\common;

interface RouteInterface
{
    /**
     * 获取传入的参数
     * @return array
     */
    public function getArguments(): array;

    /**
     * 获取所有的参数
     * @access public
     * @return array
     */
    public function getParameters(): array;
}
