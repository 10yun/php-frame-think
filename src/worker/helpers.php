<?php

use shiyunRpc\JsonRpcClient;
use shiyun\support\Cache;

function loadRpcClientInstance($module = '', $service = '')
{
    return \shiyunRpc\JsonRpcClient::instance($module);
}

function loadRpcServer($service, $class)
{
    $rpcAddress = [];
    JsonRpcClient::instance([
        $rpcAddress
    ]);
    $rpcObj = JsonRpcClient::instance($class, $service);
}
/**
 * socket推送
 * @version 2017-12-18
 * @return bool
 */
function worker_push_socket(array $data = [], string|int $socket_port = 5556)
{
    $socket_host = '127.0.0.1';
    $socket_port = '';
    // 建立socket连接到内部推送端
    $send_url = "{$socket_host}:{$socket_port}";
    $client = stream_socket_client($send_url, $errno, $errmsg, 1);
    // 发送数据，注意5556端口是Text协议的端口，Text协议需要在数据末尾加上换行
    fwrite($client, json_encode($data) . "\n");
    // fwrite ( $client, '<ctocode|202|' . $data['shipping_deliveryid'] . '>' . "\n" );
    // 读取推送结
    $result = fread($client, 8192);
    // 读取推送结
    // return fread ( $client, 8192 );
    if ($result == "ok\n") {
        return true;
    } else {
        return false;
    }
}
/**
 * 异步任务
 * @version 2017-12-18
 * @return bool
 */
function worker_async_task_producer(array $data, $Processing, $key = '')
{
    $address = '127.0.0.1:19345';
    if (empty($Processing)) {
        return false;
    }
    if (is_string($Processing)) {
        $obj = \app($Processing);
        if (!method_exists($obj, 'fire')) {
            return false;
        }
    } elseif (is_object($Processing)) {
        if (!method_exists($Processing, 'fire')) {
            return false;
        }
    } else {
        if (!is_callable($Processing)) {
            return false;
        }
    }
    if (!empty($key)) {
        $key_list = 'AsynchronousTaskProducer';
        $cache = \shiyun\support\Cache::get($key_list);
        if (empty($cache)) {
            \shiyun\support\Cache::set($key_list, [$key]);
        } else {
            if (in_array($key, $cache)) {
                return false;
            }
            \shiyun\support\Cache::push($key_list, $key);
        }
        $data['AsynchronousTaskProducerKey'] = $key;
    }
    $data['Processing'] = $Processing;
    $gateway_buffer = json_encode($data);
    $client         = stream_socket_client("tcp://" . $address, $errno, $errmsg, 10, STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT);
    if (strlen($gateway_buffer) == stream_socket_sendto($client, $gateway_buffer)) {
        return true;
    } else {
        return false;
    }
}
/**
 * 重启某个进程
 * status 查看后
 * 在worker里，使用 posix_getpid() 就可以获取到当前 worker 的 pid 了
 */
function worker_restart_pid($pid)
{
    // kill -SIGINT $pid
    // kill -SIGUSR2 PID
}

function worker_restart_all()
{
    // php start.php restart
}
