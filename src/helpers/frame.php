<?php

use think\facade\Config;
use think\facade\Env;
use think\facade\Event;
use think\facade\Session;
use think\facade\Log;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Route;

function frameGetConfig($flag = null)
{
    return Config::get($flag);
}
function frameGetEnv($flag)
{
    return Env::get($flag);
}
function frameEventTrigger($event, $param = null)
{
    return Event::trigger($event, $param);
}
function frameEventUntil($event, $param = null)
{
    return Event::until($event, $param);
}
function frameGetSession($flag)
{
    return Session::get($flag);
}
function frameLogsDebug($info = '')
{
    if (is_string($info)) {
        Log::channel('logs_channel_debug')->write($info);
    } else if (is_array($info)) {
        Log::channel('logs_channel_debug')->info($info);
    } else if (is_object($info)) {
        Log::channel('logs_channel_debug')->info($info);
    }
}
function frameLogsFile($info = '')
{
    if (is_string($info)) {
        Log::channel('logs_channel_file')->write($info);
    } else if (is_array($info)) {
        Log::channel('logs_channel_file')->info($info);
    } else if (is_object($info)) {
        Log::channel('logs_channel_file')->info($info);
    }
}
function tp6LogsPayFile($info = '')
{
    Log::channel('logs_channel_paywx')->info($info);
    // 	Log::save ();
    // 	Log::close ();
}

function tp6RedisGet($key = '', $defVal = null)
{
    $redisCacheHandle = \think\facade\Cache::store('CACHE_STORES_RD2')->handler();
    if (!empty($defVal)) {
        $redisCacheHandle->get($key, $defVal);
    }
    return $redisCacheHandle->get($key);
}
function tp6RedisSet($key = '', $val = '', $time = null)
{
    $redisCacheHandle = \think\facade\Cache::store('CACHE_STORES_RD2')->handler();
    if (!empty($time)) {
        // 缓存在3600秒之后过期
        $redisCacheHandle->set($key, $val, $time);
    } else {
        $redisCacheHandle->set($key, $val);
    }
}

function frameGetDbInit($settData = [], $database = '')
{
    $initData = [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '',
        // 数据库名
        'database' => '',
        // 用户名
        'username' => '',
        // 密码
        'password' => '',
        // 端口
        'hostport' => '3306',
        // 数据库连接参数
        'params' => [],
        // 数据库编码默认采用utf8
        'charset' => 'utf8mb4',
        // 数据库表前缀
        'prefix' => '',

        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy' => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate' => false,
        // 读写分离后 主服务器数量
        'master_num' => 1,
        // 指定从服务器序号
        'slave_no' => '',
        // 是否严格检查字段是否存在
        'fields_strict' => true,
        // 是否需要断线重连
        'break_reconnect' => false,
        // 监听SQL
        'trigger_sql' => true,
        // 开启字段缓存
        'fields_cache' => true,
        // 字段缓存路径
        'schema_cache_path' => root_path() . '/runtime/schema' . DIRECTORY_SEPARATOR
    ];
    return array_merge($initData, array(
        'hostport' => $settData['hostport'] ?? '3306',
        'hostname' => $settData['hostname'], // 服务器地
        'username' => $settData['username'], // 用户
        'password' => $settData['password'], // 密码
        'database' => $database // 数据库名
    ));
}
