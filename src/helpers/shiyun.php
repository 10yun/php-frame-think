<?php

// 项目路径

use shiyun\support\Response;
use shiyun\support\Request;

$frame_path = preg_replace('/(\/|\\\\){1,}/', '/', __DIR__) . '/';
define('_PATH_PROJECT_', dirname($frame_path, 5) . '/');


function sy_vendor_path()
{
    return root_path() . 'vendor/';
}
function sy_template_path()
{
    return root_path() . 'vendor/shiyun/php-think/src/template/';
}

function syGetConfig($get_path = '', $get_def = [])
{
    $configPath = root_path() . 'config/';
    $configOpt = $get_def;
    // $pathArr =  explode(".", $get_path);
    // $pathAll = $configPath;
    // foreach ($pathArr as $pathItem) {
    //     if (is_dir($pathAll . $pathItem)) {
    //         continue;
    //     }
    // }
    $confPath = $configPath . str_replace(".", "/", $get_path);
    $filePath = $confPath . '.php';
    if (is_file($filePath)) {
        $configOpt = include $filePath;
    }
    return $configOpt;
}
function syGetVersion()
{
    return 'ctocode-v6.22.1129';
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
 * 获取yaml的mysql配置
 */
function syGetProjectMysql()
{
    $OpenAppAuthObj = new \shiyun\connection\OpenAppAuth();
    $OpenAppAuthObj->initAuthData();
    $authAppData = $OpenAppAuthObj->getAuthData();

    $settArray = [];
    $yamlData = [];

    if (!empty($authAppData['syOpenAppProject'])) {
        // 是否开发环境配置
        if (syGetEnvironment()) {
            $pathStr = root_path() . 'project/' . $authAppData['syOpenAppProject'] . '/database.dev.yml';
            if (!file_exists($pathStr)) {
                $pathStr = root_path() . 'project/' . $authAppData['syOpenAppProject'] . '/database.yml';
            }
        } else {
            $pathStr = root_path() . 'project/' . $authAppData['syOpenAppProject'] . '/database.yml';
        }
        if (file_exists($pathStr)) {
            $yamlData = yaml_parse_file($pathStr);
        }
    }
    $settArray = [
        'hostname' => $yamlData['MYSQL_HOSTNAME'] ?? '', // 服务器地址
        'hostport' => $yamlData['MYSQL_HOSTPORT'] ?? '3306', // 端口
        'username' => $yamlData['MYSQL_USERNAME'] ?? '', // 用户
        'password' => $yamlData['MYSQL_PASSWORD'] ?? '', // 密码
        'database' =>  $yamlData['MYSQL_DATABASE'] ?? '' // 数据库名
    ];
    return $settArray;
}

function syGetProjectRedis()
{
    $OpenAppAuthObj = new \shiyun\connection\OpenAppAuth();
    $OpenAppAuthObj->initAuthData();
    $authAppData = $OpenAppAuthObj->getAuthData();

    $settArray = [];
    $yamlData = [];
    if (!empty($authAppData['syOpenAppProject'])) {
        // 是否开发环境配置
        if (syGetEnvironment()) {
            $pathStr = root_path() . 'project/' . $authAppData['syOpenAppProject'] . '/database.dev.yml';
            if (!file_exists($pathStr)) {
                $pathStr = root_path() . 'project/' . $authAppData['syOpenAppProject'] . '/database.yml';
            }
        } else {
            $pathStr = root_path() . 'project/' . $authAppData['syOpenAppProject'] . '/database.yml';
        }
        if (file_exists($pathStr)) {
            $yamlData = yaml_parse_file($pathStr);
        }
    }
    $settArray = [
        'host' => $yamlData['REDIS_HOST'] ?? '', // 服务器地址
        'port' => $yamlData['REDIS_PORT'] ?? '63379', // 端口
        'user' => $yamlData['REDIS_USER'] ?? '', // 用户
        'password' => $yamlData['REDIS_PASSWORD'] ?? '', // 密码
    ];
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
