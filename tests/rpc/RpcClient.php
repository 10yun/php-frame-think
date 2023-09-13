<?php 


// ==以下调用示例==
if (PHP_SAPI == 'cli' && isset($argv[0]) && $argv[0] == basename(__FILE__)) {
    // 服务端列表
    $address_array = array(
        'tcp://127.0.0.1:2015',
        'tcp://127.0.0.1:2015'
    );
    // 配置服务端列表
    RpcClient::config($address_array);

    $uid = 567;
    $user_client = RpcClient::instance('User');
    // ==同步调用==
    $ret_sync = $user_client->getInfoByUid($uid);

    // ==异步调用==
    // 异步发送数据
    $user_client->asend_getInfoByUid($uid);
    $user_client->asend_getEmail($uid);

    /**
     * 这里是其它的业务代码
     * ..............................................
     **/

    // 异步接收数据
    $ret_async1 = $user_client->arecv_getEmail($uid);
    $ret_async2 = $user_client->arecv_getInfoByUid($uid);

    // 打印结果
    var_dump($ret_sync, $ret_async1, $ret_async2);
}