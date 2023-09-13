<?php

return [
    //服务端配置
    'rpc_server' => [
        //worker进程数
        'processes'         => 1,
        //通信协议
        'protocol'          => '\shiyunWorker\protocols\JsonNL',
        //地址
        'host'              => '0.0.0.0',
        //端口
        'port'              => 2015,
        'socket'            => '',
        
        'worker_name'       => 'jsonRpcServer',
        'log_file'          => syPathRuntime() . 'workerman/log.log',
        //服务
        'service'           => [
            'User' => \app\test\worker\User::class
        ]
    ],
    // 服务端统计
    'statistic_process' => [
    	//统计数据的协议地址
        'socket' => 'udp://127.0.0.1:9200'
    ],

    'server_listeners' => [
        'annotation_include_path' => [
            // 'addons/*/rpc',
            // 'addons/*/*/rpc',
        ],
    ],
    'server_statistics' => []
];
