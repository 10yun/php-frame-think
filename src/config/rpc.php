<?php

return [
    /**
     * 客户端配置
     */
    'rpc_client' => [
        //驱动方式
        'type'               => 'workerman',
        //服务端连接池
        'rpc_server_address' => [
            'tcp://127.0.0.1:2015'
        ],
        //重连次数
        'reconnect_count'    => 1,
    ],
    /**
     * 服务端配置，支持多组 rpc服务
     */
    'rpc_server' => [
        [
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
                // 'User' => \app\test\worker\User::class
                'User' => \addons\iot\Services\User::class,
                'Blog' => \addons\iot\Services\Blog::class,
            ],
            'annotation_include_path' => [
                'addons/*/rpc',
                'addons/*/*/rpc',
            ],
        ],
        [
            'name' => 'tool',
            'workerName' => 'JsonRpc-services-tool',
            'socket' => "tcp://{$localhost_ip}:20030",
            'RPC_worker_db' => !empty($workerDBConfAll['ctocode_db']) ? $workerDBConfAll['ctocode_db'] : null
        ],
        [
            'name' => 'dict',
            'workerName' => 'JsonRpc-services-dict',
            'socket' => "tcp://{$localhost_ip}:20011"
        ],
        [
            'name' => 'xxxx',
            'workerName' => 'JsonRpc-services-xxx',
            'socket' => "tcp://{$localhost_ip}:20003"
        ],

    ],
    'rpc_statistic_process' => [
        //统计数据的协议地址
        'socket' => 'udp://127.0.0.1:9200',
        //web页面端口
        'web_port'        => 55757,
        //接收统计数据端口
        'statistics_port' => 9200,
        //web页面域名配置
        'host'            => '0.0.0.0',
        // 'socket'=> 
        'log_path'        => syPathRuntime() . 'worker_data/'
    ]
];
