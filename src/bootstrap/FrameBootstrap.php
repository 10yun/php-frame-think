<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

class FrameBootstrap extends \think\Service
{
    public function boot()
    {
        /**
         * slb 处理 无法获取是否https
         * （注意：需要在 slb 高级配置里勾选“ 通过X-Forwarded-Proto头字段获取SLB的监听协议 ”）
         */
        $is = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'];
        $this->app->request->server('HTTPS', $is ? 'on' : 'off');

        //
        $this->doConfigFile();
        //
        $this->commands([
            'ConfigPush' => \shiyun\command\ConfigPush::class
        ]);
    }

    protected function doConfigFile()
    {
        $frame_path = preg_replace('/(\/|\\\\){1,}/', '/', __DIR__) . '/';
        $rootPath =  dirname($frame_path, 5) . '/';

        $configDiy = $rootPath . 'config' . DIRECTORY_SEPARATOR . 'shiyun' . DIRECTORY_SEPARATOR;
        if (!is_dir($configDiy)) {
            mkdir($configDiy, 0777, true);
        }
        $confCurrDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        $confFileArr = glob($confCurrDir . '*.php');
        foreach ($confFileArr as $key => $val) {
            $fileInfo = pathinfo($val);
            $source = $val;
            $target = $configDiy . $fileInfo['basename'];
            if (is_file($target)) {
                continue;
            }
            copy($source, $target);
        }
    }
}
