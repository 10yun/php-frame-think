<?php

declare(strict_types=1);

namespace shiyun\annotation;

interface IntfAnnotationHandle
{
    /**
     * 注解处理
     * @access public
     * @param array $item
     * @return void
     */
    public static function handle(array $item = []): void;
}
