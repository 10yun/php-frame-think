<?php
// +----------------------------------------------------------------------
// | Workerman设置 仅对 php shiyun worker:queue 指令有效
// +----------------------------------------------------------------------
return [
    'process_open' => true,
    'process_count' => 1,
    // 开启注解
    'annotation_open' => true,
    'annotation_include_path' => [
        'addons/*/queues',
        'addons/*/*/queues',
    ],
    // 驱动类型，可选择 sync(默认):同步执行， database:数据库驱动, redis:Redis驱动//或其他自定义的完整的类名
    'default' => 'sync',
    'prefix' => 'shiyun_',
    # 连接信息
    'connections' => [
        'sync'     => [
            'connect_type'      => 'sync',
            'failed_type'       => 'none',
            'failed_table'      => 'failed_jobs',
        ],
        'database' => [
            'connect_type'      => 'database',
            'queue'             => 'default',
            'table'             => 'jobs',
            'connection'        => null,
            'failed_type'       => 'none',
            'failed_table'      => 'failed_jobs',
        ],
        'redis' => [
            'connect_type'      => 'redis',
            'connect_host'      => frameGetEnv('DB_REDIS_HOST', '127.0.0.1'),
            'connect_port'      => frameGetEnv('DB_REDIS_PORT', 6379),
            'connect_username'  => frameGetEnv('DB_REDIS_USER', ''),
            'connect_password'  => frameGetEnv('DB_REDIS_PASSWORD', ''),
            'select'            => 0,
            'timeout'           => 0,
            'persistent'        => false,
            'queue'             => 'default',
        ],
        'mysql' => [
            'connect_type'      => 'mysql',
            'connect_host'      => "mysql",
            'connect_port'      => "3306",
            'connect_username'  => "root",
            'connect_password'  => "root",
            'exchange_name'     => "pay", // mysql中即数据库
            'debug'             => false,
            'prefix'            => "ha_",
        ],
        'queue_connect_redis'   => [
            'connect_type'          => 'redis',
            'connect_host'          => frameGetEnv('DB_REDIS_HOST', '127.0.0.1'),
            'connect_port'          => frameGetEnv('DB_REDIS_PORT', 6379),
            'connect_user'          => frameGetEnv('DB_REDIS_USER', ''),
            'connect_password'      => frameGetEnv('DB_REDIS_PASSWORD', ''),
            'persistent'            => false,
            'select'                => 0,
            // 默认交换机名称，即：redis分区
            'exchange_name'         => frameGetEnv('DB_REDIS_ZONE', 'queues'),
            // 默认队列名
            'queue_name'            => '',
            'timeout'               => 0,
            'expire'                => 0,
            'serialize'             => true,
            'prefix'                => 'pay',
            'failed_type'           => 'none',
            'failed_table'          => 'failed_jobs',
        ],
        //连接rabbitmq,此为安装rabbitmq服务器
        'AMQP1' => [
            'connect_type'           => 'rabbitmq',
            'connect_host'           => '127.0.0.1', // 服务器地址
            'connect_port'           => 5672,        // 端口
            'connect_user'           => 'guest',     // 用户名
            'connect_password'       => 'guest',     // 密码
            'connect_vhost'          => '/',         // 资源隔离
            //
            'read_timeout'           => 30, // 读取超时时间，该设置会影响消费者订阅的阻塞时间，设置为 0 时，不会超时
            'write_timeout'          => 30,
            'connect_timeout'        => 5,
            'retry_exchange_suffix'  => '.retry', // 重试消息交换机的后缀
            'failed_exchange_suffix' => '.failed', // 失败消息交换机的后缀
            'workerman_multiple'     => 1,

            # 邮件队列
            'exchange_name'     => 'direct_exchange',
            'exchange_type'     => 'direct', #直连模式
            'queue_name'        => 'direct_queue',
            'route_key'         => 'direct_roteking',
            'consumer_tag'      => 'direct',
            'consumer_tag'      => 'consumer'
        ],
        'MQTT1' => [
            'connect_type'   => 'emqx',              // 连接 - 类型
            'exchange_name'  => 'redis',             // 交换机 - 名称
            'queue_name'     => 'merchant_account',  // 队列 - 名称
        ]
    ],
    //队列名称
    'queue'   => [
        0 => 'ORDER_NOTIFY',
        1 => 'DAIFU_NOTIFY',
    ],
    'failed'      => [
        'type'  => 'none',
        'table' => 'failed_jobs',
    ],
];
