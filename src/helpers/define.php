<?php

define('__CTOCODE__', [
    // 品牌
    '_brand_' => 'ctocode',
    // 版本
    '_version_' => '8.25.0115',
    // 官网
    '_website_' => 'https://www.10yun.com/ctocode',
    // 作者
    '_author_' => [
        'ctocode',
        'Titanic',
        '343196936@qq.com'
    ],
    // 版权
    '_copyright_' => 'Copyright © 2017 福州十云科技有限公司',
    /* 许可证 */
    '_license_type_' => '',
    '_license_id_' => 'v20250115',
    '_license_key_' => 'ctocode-v20250115',
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
// 私有路径 - 不可访问
define('_PATH_PRIVATIZATION_', _PATH_PROJECT_ . 'privatization/');
