<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

class ConnectConfig extends \think\Service
{
    public function register()
    {
        // var_dump('ConnectConfig   register');
    }
    public function boot()
    {
        // var_dump('ConnectConfig   boot');
        $this->setSlbHttps();
        // $this->setDatabase();
        // $this->setRedis();
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
    /**
     * 配置 database
     */
    protected function setDatabase()
    {
        $settArray = syGetProjectMysql();
        if (!empty($settArray) && !empty($settArray['database'])) {
            $oldSett = $this->app->config->get('database');
            $oldSett['connections']['ctocode_7'] = frameGetDbInit($settArray, $settArray['database']);
            $this->app->config->set($oldSett, 'database');
            $newSett = $this->app->config->get('database');
        }
    }
    /**
     * 配置 cache
     */
    protected function setRedis()
    {
        $settArray = syGetProjectRedis();
        if (!empty($settArray) && !empty($settArray['cache'])) {
            $oldSett = $this->app->config->get('cache');
            var_dump($oldSett);
            // $oldSett['connections']['ctocode_7'] = frameGetDbInit($settArray, $settArray['cache']);
            // $this->app->config->set($oldSett, 'cache');
            // $newSett = $this->app->config->get('cache');
        }
    }
}
