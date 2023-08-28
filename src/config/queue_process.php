<?php

return [
    /**
     * 待删除
     */
    'cache' => [
        //缓存前缀
        'prefix' => [
            'crontab'              => 'crontab:',
            'workerman_start_lock' => 'workerman_start_lock:',
            'repeat_request'       => 'repeat_request:'
        ],
        //过期时间
        'expire' => [
            //默认
            'default_data'          => 600,
            'repeat_request'        => 3,
            // workerman模块
            'workerman_start_lock'  => 10,
        ],
    ]
];
