<?php

include_once __DIR__ . "/helpers/php8.php";
include_once __DIR__ . "/helpers/define.php";
include_once __DIR__ . "/helpers/frame.php";
include_once __DIR__ . "/helpers/response.php";
include_once __DIR__ . "/helpers/shiyun.php";
include_once __DIR__ . "/helpers/reflection.php";
include_once __DIR__ . "/helpers/create_id.php";

include_once __DIR__ . "/helpers/addons.php";

include_once __DIR__ . "/queue/helpers.php";


$functionArr = glob(__DIR__ . '/function/*.php');
foreach ($functionArr as $val) {
    include_once $val;
}
/**
 * 加载【厂商/模块】、【模块】下的helper助手函数
 */
$addonComModHelper = glob(_PATH_PROJECT_ . '/addons/*/*/helpers.php');
$addonModHelper = glob(_PATH_PROJECT_ . '/addons/*/helpers.php');
$addonHelperAll = array_merge($addonComModHelper, $addonModHelper);
foreach ($addonHelperAll as $val) {
    include_once $val;
}
