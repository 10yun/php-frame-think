<?php

namespace shiyun\command;

use ReflectionClass;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use shiyun\route\annotation\RouteFlag;
use shiyun\route\annotation\RouteRestful;

class CreateApiFlag extends Command
{
    protected function configure()
    {
        $this->setName('CreateApiFlag')
            ->addOption('ver', null, Option::VALUE_OPTIONAL, '版本')
            ->setDescription('生成版本api flag');
    }

    protected function execute(Input $input, Output $output)
    {
        $version = $input->getOption('ver') ?? 1;
        // 厂商 + 模块
        $addonsDir1 = glob(_PATH_PROJECT_ . 'addons/*/*/controller/');
        $addonsDir2 = glob(_PATH_PROJECT_ . 'addons/*/controller/');
        $addonsDir = array_merge($addonsDir1, $addonsDir2);
        // dd($addonsDir);
        $commonArr = [];
        $orgArr = [];
        $businessArr = [];
        $agentArr = [];
        $platformArr = [];
        $ucenterArr = [];
        $touristArr = [];

        $need_write = false;
        foreach ($addonsDir as $dirItem) {
            if (!is_dir($dirItem)) {
                continue;
            }
            $this->writeAttrApiFlag($commonArr, $dirItem, 'common', $version, $need_write);
            $this->writeAttrApiFlag($orgArr, $dirItem, 'org', $version, $need_write);
            $this->writeAttrApiFlag($businessArr, $dirItem, 'business', $version, $need_write);
            $this->writeAttrApiFlag($agentArr, $dirItem, 'agent', $version, $need_write);
            $this->writeAttrApiFlag($platformArr, $dirItem, 'platform', $version, $need_write);
            $this->writeAttrApiFlag($ucenterArr, $dirItem, 'ucenter', $version, $need_write);
            $this->writeAttrApiFlag($touristArr, $dirItem, 'tourist', $version, $need_write);
        }

        $this->writeMergeApiFlag('common', $commonArr, $version);
        $this->writeMergeApiFlag('org', $orgArr, $version);
        $this->writeMergeApiFlag('business', $businessArr, $version);
        $this->writeMergeApiFlag('agent', $agentArr, $version);
        $this->writeMergeApiFlag('platform', $platformArr, $version);
        $this->writeMergeApiFlag('ucenter', $ucenterArr, $version);
        $this->writeMergeApiFlag('tourist', $touristArr, $version);
        // 
        echo "\n CreateApiFlag ok \n";
    }
    // 写入聚合
    protected function writeMergeApiFlag($type, $ymlArr, $version = 1)
    {
        @mkdir(_PATH_CONFIG_ . "api", 0777);
        $commonYmlStr = implode("\n", $ymlArr);
        file_put_contents(_PATH_CONFIG_ . "api/v{$version}/{$type}.yml", $commonYmlStr . "\n");

        echo " CreateApiFlag - {$type} -v{$version} \n";
    }
    // 写入
    protected function writeAttrApiFlag(&$typeAllArr, $dirItem, $type, $version = 1, $isWrite = true)
    {
        $dirParent = dirname($dirItem);
        $typeHttp = glob($dirItem . "{$type}/*.php");
        $typeFlag = $this->parseAttrApiFlag($typeHttp);
        if (!empty($typeFlag)) {
            $typeYmlArr = [];
            foreach ($typeFlag as $typeItem) {
                $typeYmlArr[] = "{$typeItem['flag']}: \"{$typeItem['restfule']}\"";
            }
            /**
             * 写入文件
             */
            if ($isWrite) {
                $typeYmlStr = implode("\n", $typeYmlArr);
                @mkdir("{$dirParent}/api", 0777);
                $typeYmlFile = "{$dirParent}/api/v{$version}-{$type}.yml";
                file_put_contents($typeYmlFile, $typeYmlStr . "\n");
            }
            if (!empty($typeYmlArr)) {
                $typeAllArr = array_merge($typeAllArr, $typeYmlArr);
            }
        }
    }
    // 解析
    protected function parseAttrApiFlag($fileArr = [])
    {
        $flagArr = [];
        foreach ($fileArr as $key => $val) {
            $namespace = get_namespace_class_form_file($val);
            if (empty($namespace)) {
                continue;
            }
            if (!str_starts_with("\\", $namespace)) {
                $namespace = "\\$namespace";
            }
            if (!class_exists($namespace)) {
                continue;
            }
            // echo $val . "\n";

            $reflectionClass = new ReflectionClass($namespace);
            $RouteFlagAttrs = $reflectionClass->getAttributes(RouteFlag::class);
            $RouteRestfulAtts = $reflectionClass->getAttributes(RouteRestful::class);

            if (empty($RouteFlagAttrs) || empty($RouteRestfulAtts)) {
                continue;
            }
            $routeFlagStr = '';
            $routeRestfulStr = '';
            foreach ($RouteFlagAttrs as $attribute) {
                // 拿到一个新的 Route 实例
                $route = $attribute->newInstance();
                // 拿到注解上的参数
                $params = $attribute->getArguments();
                // var_dump($route, $params);
                $routeFlagStr = $params[0] ?? '';
            }
            foreach ($RouteRestfulAtts as $attribute) {
                // 拿到一个新的 Route 实例
                $route = $attribute->newInstance();
                // 拿到注解上的参数
                $params = $attribute->getArguments();
                // var_dump($route, $params);    
                $routeRestfulStr = $params[0] ?? '';
            }
            if (empty($routeFlagStr) || empty($routeRestfulStr)) {
                continue;
            }
            $flagArr[] = [
                'flag' => $routeFlagStr,
                'restfule' => $routeRestfulStr
            ];
        }
        return $flagArr;
    }
}
