<?php

declare(strict_types=1);

namespace shiyun\middleware\safety;

use shiyun\support\Route;

/**
 * 白名单
 */
class SafetyWhiteMiddle
{

    public function handle($request, \Closure $next)
    {
        //  request()->server();
        //request()->header();
    }
}
