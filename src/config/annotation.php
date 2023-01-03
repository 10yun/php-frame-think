<?php

return [
    'include_paths' => [
        'app/controller',
    ],
    'exclude_paths' => [],
    'route' => [
        'use_default_method' => true,
        /**
         * addons模式使用才好
         * [current]只加载当前模块，[all]加载所有
         */
        'load_type' => 'all',
        'enable'      => true,
        'controllers' => [],
    ],
    'inject' => [
        'enable'     => true,
        'namespaces' => [],
    ],
    'model'  => [
        'enable' => true,
    ],
    'ignore' => [],
    'store'  => null, //缓存store
];
