<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

class FrameBootstrap extends \think\Service
{
    public function boot()
    {
        // var_dump('FrameBootstrap   boot'); 
        $this->copyConfigFile();
        //
        $this->commands([
            'ConfigPush' => \shiyun\command\ConfigPush::class
        ]);
    }

    protected function copyConfigFile()
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
