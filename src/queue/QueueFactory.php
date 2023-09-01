<?php

declare(strict_types=1);

namespace shiyunQueue;

use shiyun\support\Config;
use shiyunQueue\drive\Connector;
use shiyunQueue\drive\database\DatabaseConnector;
use shiyunQueue\drive\redis\RedisConnector;
use think\helper\Str;
use think\App;

/**
 * Class Queue
 * @package QueueFactory
 * @see \QueueFactory
 * @method $this allowError(int $allowError) 执行失败次数
 * @method $this log($log) 记录日志
 * @method $this sendPublish(array|string $msg) 发送消息
 * @mixin DatabaseConnector
 * @mixin RedisConnector
 * @mixin Manager
 */
class QueueFactory extends Manager
{
    // use ErrorTrait;
    protected $namespace = 'shiyunQueue\\drive\\';
    /**
     * 获取驱动类
     * @param string $type
     * @return string
     */
    protected function resolveClass(string $type): string
    {
        if ($this->namespace || false !== strpos($type, '\\')) {
            // $class = false !== strpos($type, '\\') ? $type : $this->namespace . Str::studly($type);
            $class = false !== strpos($type, '\\') ? $type : $this->namespace  . $type . "\\" . Str::studly($type) . "Connector";
            if (class_exists($class)) {
                return $class;
            }
        }
        throw new \InvalidArgumentException("Driver [$type] not supported.");
    }
    protected function resolveConfig(string $name)
    {
        return syGetConfig("shiyun.queue.connections.{$name}");
    }
    protected function resolveType(string $name)
    {
        return syGetConfig("shiyun.queue.connections.{$name}.connect_type", 'sync');
    }
    /**
     * 默认驱动
     * @return string
     */
    public function getDefaultDriver()
    {
        return syGetConfig('shiyun.queue.default');
    }
    protected function createDriver(string $name)
    {
        /** @var Connector $driver */
        $driver = parent::createDriver($name);
        // 初始化设置
        $driver->baseInit();
        return $driver->setApp($this->app)
            ->setConnectName($name);
    }
    /**
     * @param null|string $name
     * @return Connector
     */
    public function connection($name = null)
    {
        return $this->driver($name);
    }
    /**
     * 容器对象实例
     * @var static
     */
    protected static $instance;
    /**
     * 容器中的对象实例
     * @var array
     */
    protected $instances = [];
    /**
     * 获取当前容器的实例（单例）
     * @access public
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static(App());
        }
        return self::$instance;
        // if (is_null(static::$instance)) {
        //     static::$instance = new static();
        // }
        // return static::$instance;
    }
    /**
     * 获取参数
     * @param $data
     * @return array
     */
    protected function getValues($data)
    {
        // if ($this->jobFunc != $this->_defaultDo) {
        //     // $prefix = syGetConfig('shiyun.queue.xxx.prefix', 'ctocode_');
        //     // $this->jobServer .= '@' . $prefix . $this->jobFunc;
        // } 
    }
}
