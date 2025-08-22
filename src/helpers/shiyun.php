<?php

// 项目路径

use shiyun\support\Response;
use shiyun\support\Request;
use shiyun\support\Config;
use shiyun\support\Env;

function syPathTemplate()
{
    return _PATH_PROJECT_ . 'vendor/shiyun/php-think/src/template/';
}
function syPathStorage(string|null $str = null)
{
    if (empty($str)) {
        return _PATH_PROJECT_ . 'storage/';
    } else {
        return _PATH_PROJECT_ . 'storage/' . $str;
    }
}
function syPathRuntime()
{
    return _PATH_PROJECT_ . 'runtime/';
}
// 获取配置
function syGetConfig(?string $key = null, $default = [])
{
    $configPath = _PATH_PROJECT_ . 'config/';
    \shiyun\libs\Config::init($configPath);
    $config = \shiyun\libs\Config::get($key, $default);
    return $config;
}
function syGetVersion()
{
    return __CTOCODE__['_brand_'] . '-v' . __CTOCODE__['_version_'];
}
function syGetHeader()
{
    return [
        'syOpenAppProject' => syOpenAppsAuth('syOpenAppProject'),
        'syOpenAppId' => syOpenAppsAuth('syOpenAppId'),
        'syOpenAppSecret' => syOpenAppsAuth('syOpenAppSecret'),
        'syOpenAppRole' => syOpenAppsAuth('syOpenAppRole'),
        'syOpenAppToken' => syOpenAppsAuth('syOpenAppToken'),
    ];
}
function syGetEnvironment()
{
    $envProjectEnvironment = frameGetEnv('PROJECT_ENVIRONMENT');
    if (empty($envProjectEnvironment)) {
        return false;
    }
    if ($envProjectEnvironment !== 'development') {
        return false;
    }
    return true;
}
function syGetEnvArr()
{
    $envFile = '/www/.env';
    $envContent = file_get_contents($envFile);
    $envArray = [];
    if ($envContent !== false) {
        $envLines = explode("\n", $envContent);
        foreach ($envLines as $line) {
            if (str_contains($line, '#')) {
                continue;
            }
            $line = trim($line);
            if (!empty($line) && str_contains($line, '=')) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    // 去除额外的双引号
                    $value = trim($value, '"');
                    $value = stripcslashes($value);
                    $envArray[$key] = $value;
                }
            }
        }
    }
    return $envArray;
}
/**
 * 获取项目路径
 */
function syGetProjectPath($proStr = '', bool $isForcePathOpen = false)
{
    $envProjectPath = Env::get('PROJECT_PATH', 'off');
    if ($isForcePathOpen) {
        return root_path() . '/project/' . $proStr . '/';
    }
    if ($envProjectPath == 'open') {
        return root_path() . '/project/' . $proStr . '/';
    }
    return root_path() . '/project/';
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
function syOpenAppsConfig(?string $name = null)
{

    // $smsConfig = config_global_single_group('SDK_SMS_CONFIG');
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
