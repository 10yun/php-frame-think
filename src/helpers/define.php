<?php

define('__CTOCODE__', [
    '_version_' => 'ctocode-v8.24.0520',
    /* 软件名称 */
    '_frame_name_' => 'ctocode-frame',
    /* 版本号等信息 */
    '_frame_build_' => '8.24.0520',
    '_frame_author_' => 'ctocode',
    '_frame_link_' => 'http://ctocode.com/',
    /* 版权 */
    '_copyright_' => 'http://ctocode.com/',
    /* 许可证 */
    '_license_type_' => '',
    '_license_id_' => 'V20230713',
    '_license_key_' => 'ctocodeV20230713',
]);


$frame_path = preg_replace('/(\/|\\\\){1,}/', '/', __DIR__) . '/';
// 项目路径
define('_PATH_PROJECT_', dirname($frame_path, 5) . '/');
// 配置文件路径
define('_PATH_CONFIG_', _PATH_PROJECT_ . 'config/');
// 程序运行产生的文件
define('_PATH_RUNTIME_', _PATH_PROJECT_ . 'runtime/');
// 资源路径 - 无法访问
define('_PATH_STORAGE_', _PATH_PROJECT_ . 'storage/');
// 资源路径 - 可访问
define('_PATH_FILE_', _PATH_PROJECT_ . 'public/storage/');
