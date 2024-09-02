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
 * 
 */
function worker_push_socket() {}


/**
 * 异步任务
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
