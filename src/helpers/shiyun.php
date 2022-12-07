<?php
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
/**
 * 获取项目配置
 */
function syGetProjectSett($diy_name = '')
{
    if (empty($diy_name)) {
        return [];
    }
    $settArray = [];
    $sett_path = root_path() . '/project/' . $diy_name . '/project.php';
    if (file_exists($sett_path)) {
        $settArray = include $sett_path;
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
 * @param $key 
 */
function syOpenAppsConfig($key = '')
{
    $data = app('SyOpenAppsConfig')->getSett();
    if (!empty($key)) {
        return $data[$key] ?? '';
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
