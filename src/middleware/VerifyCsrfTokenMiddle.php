<?php

declare(strict_types=1);

namespace shiyun\middleware;

class VerifyCsrfTokenMiddle
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // 接口部分
        'api/*',
        // 发布桌面端
        'desktop/publish/',
    ];
}
