<?php

define('__CTOCODE__', [
    /* 软件名称 */
    '_CTOCODE_FRAME_NAME_' => 'ctocode-php-frame',
    '_CTOCODE_FRAME_VERSION_' => '1.0.0',
    /* 版本号等信息 */
    '_CTOCODE_FRAME_BUILD_' => '2017.07.28.1833',
    '_CTOCODE_FRAME_AUTHOR_' => 'ctocode member 343196936@qq.com',
    '_CTOCODE_FRAME_LINK_' => 'https://ctocode.com',
    /* 版权 */
    '_CTOCODE_COPYRIGHT_' => 'https://ctocode.com',
    /* 许可证 */
    '_CTOCODE_LICENSE_VER' => '20180906-v2',
    /* LICENSE ID */
    '_CTOCODE_LICENSE_ID' => 'V20170129',
    /* LICENSE KEY */
    '_CTOCODE_LICENSE_KEY' => 'ctocodeV20170129'
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
