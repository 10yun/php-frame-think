<?php
// +----------------------------------------------------------------------
// | Workerman设置 仅对 php shiyun worker:crontab 指令有效
// +----------------------------------------------------------------------
return [
    /**
     * Crontab 定时器、定时任务、生成队列
     */
    'process_open' => true,
    'process_count' => 1,
    // 'process_socket' => 'tcp://0.0.0.0:16015',
    //  处理 crontab目录下的定时器。 
    'process_include_path' => [
        'addons/*/crontab',
        'addons/*/*/crontab',
    ],
];
