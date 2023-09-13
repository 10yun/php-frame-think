<?php

namespace shiyunRpc;

class JsonRpcClient
{
    /**
     * 同步调用实例
     * @var string
     */
    protected static array $instances = array();

    /**
     * 获取一个实例
     * @param string $service_name 服务名
     * @param mixed  $config 配置支持数组或配置文件
     * @return mixed|string
     */
    public static function instance(
        $service_name = '',
        $config = ''
    ) {
        if (is_string($config)) {
            if ($config == '') {
                $configDiy = syGetConfig('shiyun.rpc_client');
                $configDef = include dirname(__DIR__) . '/config/rpc_client.php';
                $config  = array_merge($configDef, $configDiy);
            } else {
                $config = include $config;
            }
        }
        $config['service_name'] = $service_name;
        $type                   = $config['type'] ?? 'workerman';
        if (!isset(self::$instances[$service_name])) {
            $class = str_contains($type, '\\') ? $type :
                '\\shiyunWorker\\rpc\\drive\\' . $type . '\\JsonRpcClient';
            self::$instances[$service_name] = new $class();
        }
        self::$instances[$service_name]->init($config);
        return self::$instances[$service_name];
    }
}
