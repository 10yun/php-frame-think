<?php

declare(strict_types=1);

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
use shiyun\route\annotation\RouteGroup;
use shiyun\route\annotation\RouteGet;
use shiyun\route\annotation\RoutePost;
use shiyun\route\annotation\RoutePut;
use shiyun\route\annotation\RoutePatch;
use shiyun\route\annotation\RouteDelete;

/**
 * 根据注解生成 FLAG：url
 */
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
        $baseArr = [];
        $commonArr = [];
        $orgArr = [];
        $businessArr = [];
        $agentArr = [];
        $operatorArr = [];
        $adminArr = [];
        $ucenterArr = [];
        $touristArr = [];

        $need_write = false;
        foreach ($addonsDir as $dirItem) {
            if (!is_dir($dirItem)) {
                continue;
            }
            $this->writeAttrApiFlag($baseArr, $dirItem, '', $version, $need_write);
            $this->writeAttrApiFlag($commonArr, $dirItem, 'common', $version, $need_write);
            $this->writeAttrApiFlag($orgArr, $dirItem, 'org', $version, $need_write);
            $this->writeAttrApiFlag($businessArr, $dirItem, 'business', $version, $need_write);
            $this->writeAttrApiFlag($agentArr, $dirItem, 'agent', $version, $need_write);
            $this->writeAttrApiFlag($operatorArr, $dirItem, 'operator', $version, $need_write);
            $this->writeAttrApiFlag($adminArr, $dirItem, 'admin', $version, $need_write);
            $this->writeAttrApiFlag($ucenterArr, $dirItem, 'ucenter', $version, $need_write);
            $this->writeAttrApiFlag($touristArr, $dirItem, 'tourist', $version, $need_write);
        }
        $this->writeMergeApiFlag('base', $baseArr, $version);
        $this->writeMergeApiFlag('common', $commonArr, $version);
        $this->writeMergeApiFlag('org', $orgArr, $version);
        $this->writeMergeApiFlag('business', $businessArr, $version);
        $this->writeMergeApiFlag('agent', $agentArr, $version);
        $this->writeMergeApiFlag('operator', $operatorArr, $version);
        $this->writeMergeApiFlag('admin', $adminArr, $version);
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

        $error_controller = '';
        try {
            foreach ($fileArr as $key => $val) {
                $error_controller = $val;
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
                $RouteRestfulAttrs = $reflectionClass->getAttributes(RouteRestful::class);
                $RouteGroupAttrs = $reflectionClass->getAttributes(RouteGroup::class);
                $methods = $reflectionClass->getMethods();

                $classFlagArr = $this->parseFlagClass($RouteFlagAttrs, $RouteRestfulAttrs);
                $methodFlagArr = $this->parseFlagMethod($RouteGroupAttrs, $methods);

                $flagArr = array_merge($flagArr, $classFlagArr, $methodFlagArr);
            }
        } catch (\Throwable $th) {
            echo "{$error_controller} " . $th->getMessage() . "\n";
            //throw $th;
        }
        return $flagArr;
    }
    // 处理【类级别】的注解
    // 处理【类】上的 RouteFlag 和 RouteRestful 注解
    protected function parseFlagClass($RouteFlagAttrs, $RouteRestfulAttrs)
    {
        $flagArr = [];
        if (!empty($RouteFlagAttrs) && !empty($RouteRestfulAttrs)) {
            $routeFlagStr = '';
            $routeRestfulStr = '';
            foreach ($RouteFlagAttrs as $attribute) {
                // 拿到一个新的 Route 实例
                // $route = $attribute->newInstance();
                // 拿到注解上的参数
                $params = $attribute->getArguments();
                $routeFlagStr = $params[0] ?? '';
            }
            foreach ($RouteRestfulAttrs as $attribute) {
                // 拿到一个新的 Route 实例
                // $route = $attribute->newInstance();
                // 拿到注解上的参数
                $params = $attribute->getArguments();
                $routeRestfulStr = $params[0] ?? '';
            }
            if (!empty($routeFlagStr) && !empty($routeRestfulStr)) {
                $flagArr[] = [
                    'flag' => $routeFlagStr,
                    'restfule' => $routeRestfulStr
                ];
            }
        }
        return $flagArr;
    }
    // 处理【方法级别】的注解
    protected function parseFlagMethod($RouteGroupAttrs, $methods)
    {
        $flagArr = [];
        $groupPath = '';
        if (!empty($RouteGroupAttrs)) {
            foreach ($RouteGroupAttrs as $attribute) {
                $params = $attribute->getArguments();
                $groupPath = $params[0] ?? '';
            }
        }
        foreach ($methods as $method) {
            $RouteFlagAttrs = $method->getAttributes(RouteFlag::class);
            $RouteGetAttrs = $method->getAttributes(RouteGet::class);
            $RoutePostAttrs = $method->getAttributes(RoutePost::class);
            $RoutePutAttrs = $method->getAttributes(RoutePut::class);
            $RoutePatchAttrs = $method->getAttributes(RoutePatch::class);
            $RouteDeleteAttrs = $method->getAttributes(RouteDelete::class);

            if (empty($RouteFlagAttrs)) {
                continue;
            }

            $routeFlagStr = '';
            $routeMethodStr = '';
            foreach ($RouteFlagAttrs as $attribute) {
                $params = $attribute->getArguments();
                $routeFlagStr = $params[0] ?? '';
            }

            if (!empty($RouteGetAttrs)) {
                foreach ($RouteGetAttrs as $attribute) {
                    $params = $attribute->getArguments();
                    $routeMethodStr = $groupPath . '/' . ($params[0] ?? '');
                }
            } elseif (!empty($RoutePostAttrs)) {
                foreach ($RoutePostAttrs as $attribute) {
                    $params = $attribute->getArguments();
                    $routeMethodStr = $groupPath . '/' . ($params[0] ?? '');
                }
            } elseif (!empty($RoutePutAttrs)) {
                foreach ($RoutePutAttrs as $attribute) {
                    $params = $attribute->getArguments();
                    $routeMethodStr = $groupPath . '/' . ($params[0] ?? '');
                }
            } elseif (!empty($RoutePatchAttrs)) {
                foreach ($RoutePatchAttrs as $attribute) {
                    $params = $attribute->getArguments();
                    $routeMethodStr = $groupPath . '/' . ($params[0] ?? '');
                }
            } elseif (!empty($RouteDeleteAttrs)) {
                foreach ($RouteDeleteAttrs as $attribute) {
                    $params = $attribute->getArguments();
                    $routeMethodStr = $groupPath . '/' . ($params[0] ?? '');
                }
            }

            if (!empty($routeFlagStr) && !empty($routeMethodStr)) {
                $flagArr[] = [
                    'flag' => $routeFlagStr,
                    'restfule' => $routeMethodStr
                ];
            }
        }
        return $flagArr;
    }
}
