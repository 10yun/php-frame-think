<?php

use shiyun\exception\AddonsLoadException;
use shiyun\libs\Addons;

/**
 * 遗弃
 */
function loadAddonsCurrModel($fileDir = null)
{
    if (empty($fileDir)) {
        $trace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $callArr   = isset($trace[1]) ? $trace[1] : $trace[0];
        if (empty($callArr['file'])) {
            throw new \Exception('loadAddonsCurrModel 错误');
        }
        $filePath = $callArr['file'];
        $fileDir = dirname($filePath);
    }
    $classFile = $fileDir . '/model.php';
    $className = get_class_from_file($classFile);
    require_once $classFile;
    $classObj = new $className();
    return $classObj;
}

/**
 * @var \shiyun\model\ModelExtCache
 * @param string $service  应用名
 * @param string $class    功能名
 * @method static \shiyun\libs\Addons getMapInstance($key=null) 获取映射
 * @method static \shiyun\libs\Addons setType(string $type) 设置类型
 * @method static \shiyun\libs\Addons checkService(string $service, string $class = '') 验证服务
 * @method static string getService() 获取服务名称
 */
function loadAddonsCache($service, $class)
{
    try {
        $className = Addons::getMapInstance()
            ->setType('cache')
            ->checkService($service, $class)
            ->getService();

        // $classObj = new $className();
        /**
         * @var \shiyun\model\ModelExtCache
         */
        $classObj = $className::getMapInstance();
        return $classObj;
    } catch (\Exception $exception) {
        return sendRespError($exception->getMessage());
        // throw new RpcException();
    }
}
/**
 * @var \shiyun\model\ModelExtend
 * @param string $service  应用名
 * @param string $class    功能名
 */
function loadAddonsModel($service = '', $class = '')
{
    try {
        $className = Addons::getMapInstance()
            ->setType('model')
            ->checkService($service, $class)
            ->getService();
        /**
         * @var \shiyun\model\ModelExtend
         */
        $classObj = new $className();
        // $classObj = $className::getMapInstance();
        return $classObj;
    } catch (\Exception $exception) {
        return sendRespError($exception->getMessage());
    }
}
/**
 * 微服务 rpc
 * @param string $service  应用名
 * @param string $class    功能名
 * @param bool $forceOpenRpc    是否强制使用rpc服务
 * @param bool $forceCloseRpc   是否强制使用本地服务
 */
function loadAddonRpc($service = '', $class = '', $forceCloseRpc = false)
{
    try {
        $className = Addons::getMapInstance()
            ->setType('Rpc')
            ->checkService($service, $class)
            ->getService();

        // if (
        //     !$forceCloseRpc
        //     &&  Config::get('ctocode._PRC_OPEN_JSON_', false)
        //     && !empty($rpcAddress)
        // ) {
        //     // 配置服务端列
        //     $rpcObj = loadRpcServer($service, $class);
        //     return $rpcObj;
        // }

        // $classObj = new $className();
        $classObj = $className::getMapInstance();
        return $classObj;
    } catch (\Exception $exception) {
        dd($exception->getMessage());
        // throw new RpcException();
        // 发送数据给客户端，发生异常，调用失败 
    }
}
