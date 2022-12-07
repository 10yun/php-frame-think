<?php

namespace shiyun\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class ConfigPush extends Command
{
    protected function configure()
    {
        $this->setName('ConfigPush')
            ->setDescription('Say Hello');
    }

    protected function execute(Input $input, Output $output)
    {

        $configDir = $this->app->getConfigPath();
        $configDiy = $configDir . 'shiyun' . DIRECTORY_SEPARATOR;
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

        $output->writeln("ConfigPush ok! ");
    }
}
