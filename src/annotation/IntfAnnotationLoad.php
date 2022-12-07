<?php

declare(strict_types=1);

namespace shiyun\annotation;

interface IntfAnnotationLoad
{
    /**
     * 加载注解处理
     * @access public
     * @return void
     */
    public static function loader(): void;
}
