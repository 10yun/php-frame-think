<?php

declare(strict_types=1);

namespace shiyun\support;

use think\Facade;

/**
 * Class Captcha
 * @package shiyun\libs\facade
 * @mixin \shiyun\libs\Captcha
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \shiyun\libs\LibCaptcha::class;
    }
}
