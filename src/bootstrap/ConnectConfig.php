<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use shiyun\support\Service as BaseService;

class ConnectConfig extends BaseService
{
    public function register()
    {
        // var_dump('ConnectConfig   register');
    }
    public function boot()
    {
        // var_dump('ConnectConfig   boot');
        $this->setSlbHttps();
    }
    protected function setSlbHttps()
    {
        /**
         * slb 处理 无法获取是否https
         * （注意：需要在 slb 高级配置里勾选“ 通过X-Forwarded-Proto头字段获取SLB的监听协议 ”）
         */
        $HTTP_X_FORWARDED_PROTO = $this->app->request->server('HTTP_X_FORWARDED_PROTO');
        $isHttps = isset($HTTP_X_FORWARDED_PROTO) && 'https' == $HTTP_X_FORWARDED_PROTO;
        $this->app->request->server('HTTPS', $isHttps ? 'on' : 'off');
    }
}
