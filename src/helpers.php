<?php

include_once __DIR__ . "/helpers/define.php";
include_once __DIR__ . "/helpers/frame.php";
include_once __DIR__ . "/helpers/response.php";
include_once __DIR__ . "/helpers/shiyun.php";
include_once __DIR__ . "/helpers/reflection.php";

include_once __DIR__ . "/helpers/analys_calc.php";
include_once __DIR__ . "/helpers/addons.php";
include_once __DIR__ . "/helpers/queue.php";

$functionArr = glob(__DIR__ . '/function/*.php');
foreach ($functionArr as $val) {
    include_once $val;
}
