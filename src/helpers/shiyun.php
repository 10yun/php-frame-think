<?php

// 项目路径

use shiyun\support\Response;
use shiyun\support\Request;
use shiyun\support\Config;

$frame_path = preg_replace('/(\/|\\\\){1,}/', '/', __DIR__) . '/';
define('_PATH_PROJECT_', dirname($frame_path, 5) . '/');
define('_PATH_RUNTIME_', _PATH_PROJECT_ . 'runtime/');
define('_PATH_STORAGE_', _PATH_PROJECT_ . 'storage/');

function syPathVendor()
{
    return _PATH_PROJECT_ . 'vendor/';
}
function syPathTemplate()
{
    return _PATH_PROJECT_ . 'vendor/shiyun/php-think/src/template/';
}
function syPathConfig()
{
    return _PATH_PROJECT_ . 'config/';
}
function syPathStorage()
{
    return _PATH_PROJECT_ . 'storage/';
}
function syPathRuntime()
{
    return _PATH_PROJECT_ . 'runtime/';
}
// 获取配置
function syGetConfig(string $key = null, $default = [])
{
    $configPath = _PATH_PROJECT_ . 'config/';
    \shiyun\libs\Config::init($configPath);
    $config = \shiyun\libs\Config::get($key, $default);
    return $config;
}
function syGetVersion()
{
    return 'ctocode-v7.23.0115';
}
function syGetHeader()
{
    return [
        'syOpenAppProject' => syOpenAppsAuth('syOpenAppProject'),
        'syOpenAppId' => syOpenAppsAuth('syOpenAppId'),
        'syOpenAppKey' => syOpenAppsAuth('syOpenAppKey'),
        'syOpenAppRole' => syOpenAppsAuth('syOpenAppRole'),
        'syOpenAppToken' => syOpenAppsAuth('syOpenAppToken'),
    ];
}
function syGetEnvironment()
{
    $environment = frameGetEnv('ctocode.environment');
    if (empty($environment)) {
        return false;
    }
    if ($environment !== 'development') {
        return false;
    }
    return true;
}
/**
 * 获取项目配置
 */
function syGetProjectSett($diy_name = '')
{
    if (empty($diy_name)) {
        return [];
    }
    $settArray = [];
    $sett_path1 = root_path() . '/project/' . $diy_name . '/project.php';
    if (file_exists($sett_path1)) {
        $settArray1 = include $sett_path1;
        $settArray = array_merge($settArray, $settArray1);
    }
    $sett_path2 = root_path() . '/project/' . $diy_name . '/project.yml';
    if (file_exists($sett_path2)) {
        $settArray2 = yaml_parse_file($sett_path2);
        $settArray = array_merge($settArray, $settArray2);
    }
    return $settArray;
}
/**
 * 获取应用数组
 */
function syGetAppsArr()
{
    $pathStr = root_path() . '/project/*/apps/*';
    $pathArr = glob($pathStr);
    $appsArr = [];
    foreach ($pathArr as $val) {
        // $appsArr[] = basename($val, '.yml');
        $appsArr[] = pathinfo($val, PATHINFO_FILENAME);
    }
    return $appsArr;
}
/**
 * 获取应用配置
 */
function syGetAppsSett($diy_name = '')
{
    if (empty($diy_name)) {
        return [];
    }
    $settArray = [];
    if (!empty($diy_name)) {
        $configPath = root_path() . '/project/*/apps/' . $diy_name . '.yml';
        $configArr = glob($configPath);
        if (!empty($configArr[0])) {
            $sett_path = $configArr[0];
            if (file_exists($sett_path)) {
                // $xmlFile = file_get_contents($sett_path);
                // $settArray = \shiyunUtils\libs\LibXml::xmlToArr($xmlFile);
                $settArray = yaml_parse_file($sett_path);
                if (empty($settArray)) {
                    $settArray = [];
                }
                unset($settArray['comment']);
            }
        }
    }
    return $settArray;
}

function syOpenAppsAuth($key = '')
{
    $data = app('SyOpenAppsAuth')->getAuthData();
    if (!empty($key)) {
        return $data[$key] ?? '';
    }
    return $data;
}
/**
 * 获取应用配置
 * 支持多级获取 A.b.c
 * @param $name 
 */
function syOpenAppsConfig(string $name = null)
{
    $data = app('SyOpenAppsConfig')->getSett();
    if (is_null($name)) {
        return $data;
    }
    // 是否包含.
    if (str_contains($name, ".")) {
        $nameArr = explode(".", $name);
        $lastData = $data;
        foreach ($nameArr as $val) {
            if (isset($lastData[$val])) {
                $lastData = $lastData[$val];
            }
        }
        return $lastData;
    } else {
        return $data[$name] ?? '';
    }
    return $data;
}
function syOpenAccess($key = '')
{
    $data = app('SyOpenAppsAccess')->getAccessData();
    if (!empty($key)) {
        return $data[$key] ?? '';
    }
    return $data;
}
