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
use shiyun\route\annotation\RouteMiddleware;

/**
 * 根据注解生成 FLAG：url
 */
class CreateApiFlag extends Command
{
    // API、FLAG 的版本
    protected $apiVersion = 1;
    // 是否写入到各个模块下
    protected $moduleApiWrite = false;
    protected function configure()
    {
        $this->setName('CreateApiFlag')
            ->addOption('ver', null, Option::VALUE_OPTIONAL, '版本')
            ->setDescription('生成版本api flag');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->apiVersion = $input->getOption('ver') ?? 1;
        // 厂商 + 模块
        $addonsDir1 = glob(_PATH_PROJECT_ . 'addons/*/*/controller/');
        $addonsDir2 = glob(_PATH_PROJECT_ . 'addons/*/controller/');
        $addonsDir = array_merge($addonsDir1, $addonsDir2);

        // dd($addonsDir);
        $need_keys = [
            'base',
            'common',
            'org',
            'business',
            'agent',
            'operator',
            'admin',
            'ucenter',
            'tourist'
        ];
        $data_arr = [];
        foreach ($need_keys as $key_val) {
            $data_arr["{$key_val}_flag"] = [];
            $data_arr["{$key_val}_auth"] = [];
        }
        foreach ($addonsDir as $dirItem) {
            if (!is_dir($dirItem)) {
                continue;
            }
            foreach ($need_keys as $key_val) {
                $dirItemResult = $this->writeAttrApiFlag($dirItem, $key_val);
                $data_arr["{$key_val}_flag"] = array_merge($data_arr["{$key_val}_flag"], $dirItemResult['flag']);
                $data_arr["{$key_val}_auth"] = array_merge($data_arr["{$key_val}_auth"], $dirItemResult['auth']);
            }
        }
        foreach ($need_keys as $key_val) {
            $this->writeMergeApiFlag($key_val, $data_arr["{$key_val}_flag"], $data_arr["{$key_val}_auth"]);
        }

        echo "\n CreateApiFlag ok \n";
    }
    // 写入聚合
    protected function writeMergeApiFlag($type, $flagYmlArr, $authYmlArr = [])
    {
        $version = $this->apiVersion;
        @mkdir(_PATH_CONFIG_ . "api", 0777);
        @mkdir(_PATH_CONFIG_ . "api/v{$version}_auth", 0777);
        file_put_contents(_PATH_CONFIG_ . "api/v{$version}/{$type}.yml", implode("\n", $flagYmlArr) . "\n");
        file_put_contents(_PATH_CONFIG_ . "api/v{$version}_auth/{$type}.yml", implode("\n", $authYmlArr) . "\n");

        echo " CreateApiFlag - {$type} -v{$version} \n";
    }
    // 写入
    protected function writeAttrApiFlag($dirItem, $type)
    {
        $typeDir = $type == 'base' ? '' : $type;

        $dirParent = dirname($dirItem);
        $typeHttp = glob($dirItem . "{$typeDir}/*.php");
        $typeFlag = $this->parseAttrApiFlag($typeHttp);
        $typeYmlArr = [];
        $authYmlArr = [];
        if (!empty($typeFlag)) {
            foreach ($typeFlag as $typeItem) {
                $typeYmlArr[] = "{$typeItem['flag']}: \"{$typeItem['restfule']}\"";
                $auth_app = !empty($typeItem['auth_app']) ? 'true' : 'false';
                $auth_token = !empty($typeItem['auth_token']) ? 'true' : 'false';
                $authYmlArr[] = "{$typeItem['flag']}: \"app={$auth_app},token={$auth_token}\"";
            }
            /**
             * 是否写入文件到各个模块里
             */
            if ($this->moduleApiWrite) {
                $version = $this->apiVersion;
                $baseDir1 = "{$dirParent}/api/v{$version}";
                $baseDir2 = "{$dirParent}/api/v{$version}_auth";
                @mkdir($baseDir1, 0777);
                file_put_contents("$baseDir1/{$type}.yml",  implode("\n", $typeYmlArr) . "\n");
                @mkdir("$baseDir2", 0777);
                file_put_contents("$baseDir2/{$type}.yml", implode("\n", $authYmlArr) . "\n");
            }
        }
        return [
            'flag' => $typeYmlArr,
            'auth' => $authYmlArr
        ];
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
                    // print_r(spl_autoload_functions());
                    // echo file_exists(__DIR__ . '/vendor/autoload.php') ? "Composer autoload 存在" : "Composer autoload 不存在";
                    echo $namespace . ' 类不存在' . "\n";
                    continue;
                }
                // echo $val . "\n";

                $reflectionClass = new ReflectionClass($namespace);
                $RouteFlagAttrs = $reflectionClass->getAttributes(RouteFlag::class);
                $RouteRestfulAttrs = $reflectionClass->getAttributes(RouteRestful::class);
                $RouteMiddleAttrs = $reflectionClass->getAttributes(RouteMiddleware::class);
                $RouteGroupAttrs = $reflectionClass->getAttributes(RouteGroup::class);
                $methods = $reflectionClass->getMethods();

                $classFlagArr = $this->parseFlagClass($RouteFlagAttrs, $RouteRestfulAttrs, $RouteMiddleAttrs);
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
    protected function parseFlagClass($RouteFlagAttrs, $RouteRestfulAttrs, $RouteMiddleAttrs)
    {
        $flagArr = [];
        if (!empty($RouteFlagAttrs) && !empty($RouteRestfulAttrs)) {
            $routeFlagStr = '';
            $routeRestfulStr = '';
            $routeMiddArr = [];
            $routeMiddAuth = false;
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
            foreach ($RouteMiddleAttrs as $attribute) {
                // 拿到注解上的参数
                $params = $attribute->getArguments();
                $routeMiddArr = $params[0] ?? '';
            }
            if (!empty($routeFlagStr) && !empty($routeRestfulStr)) {
                $falgItem = [
                    'flag' => $routeFlagStr,
                    'restfule' => $routeRestfulStr
                ];
                foreach ($routeMiddArr as $middItem) {
                    if ($middItem == 'shiyunOpensdk\middleware\SyAuthAppMiddle') {
                        $falgItem['auth_app'] = true;
                    }
                    if ($middItem == 'shiyunOpensdk\middleware\SyAuthTokenMiddle') {
                        $falgItem['auth_token'] = true;
                    }
                }
                $flagArr[] = $falgItem;
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
