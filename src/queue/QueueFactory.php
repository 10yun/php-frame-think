<?php

declare(strict_types=1);

namespace shiyunQueue;

use shiyun\support\Config;
use shiyunQueue\drive\Connector;
use shiyunQueue\exception\DriverException;
use shiyunQueue\drive\database\DatabaseConnector;
use shiyunQueue\drive\redis\RedisConnector;
use shiyunQueue\drive\rabbitmq\RabbitmqConnector;
use think\App;
use InvalidArgumentException;

/**
 * Class Queue
 * @package QueueFactory
 * @see \QueueFactory
 * @method $this allowError(int $allowError) 执行失败次数
 * @method $this log($log) 记录日志
 * @method $this sendPublish(array|string $msg) 发送消息
 * @mixin DatabaseConnector
 * @mixin RedisConnector
 * @mixin RabbitmqConnector
 */
class QueueFactory
{
    /** @var App */
    protected $app;
    /**
     * 驱动
     */
    protected array $driversHandle = [];
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    /**
     * 默认驱动
     * @return string|null
     * @return string
     */
    public function getDefaultDriver()
    {
        return syGetConfig('shiyun.queue.default');
    }
    /**
     * 获取驱动实例
     * @param null|string $name 驱动名称
     * @return Connector|mixed
     */
    public function connection($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        if (is_null($name)) {
            throw new InvalidArgumentException(sprintf(
                '【ctocode-queue】无法解析的NULL驱动程序 [%s].',
                static::class
            ));
        }
        if (!empty($this->driversHandle[$name])) {
            /**
             * 获取驱动实例
             */
            return $this->driversHandle[$name];
        } else {
            /**
             * 创建驱动实例
             */
            // 获取驱动参数配置
            $driverConfig = syGetConfig("shiyun.queue.connections.{$name}");
            // 获取驱动类型
            $driverType = $driverConfig['connect_type'] ?? 'sync';
            switch ($driverType) {
                case 'redis':
                    $redisDrive = new RedisConnector($driverConfig);
                    break;
                case 'database':
                    $redisDrive = new DatabaseConnector($driverConfig);
                    break;
                case 'rabbitmq':
                    $redisDrive = new RabbitmqConnector($driverConfig);
                    break;
                default:
                    throw new InvalidArgumentException("【ctocode-queue】Driver [$driverType] 不支持");
                    throw new DriverException('【ctocode-queue】驱动类型错误');
                    break;
            }
            $redisDrive->setApp($this->app);
            $this->driversHandle[$name] = $redisDrive;
            return $this->driversHandle[$name];
        }
    }
    /**
     * 单例实例
     */
    protected static $instance;
    /**
     * 存储单例
     */
    protected array $instances = [];
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

    /**
     * 动态调用
     * @param string $method
     * @param array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
    /**
     * 移除一个驱动实例
     *
     * @param array|string|null $name
     * @return $this
     */
    public function forgetDriver($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();
        foreach ((array) $name as $cacheName) {
            if (isset($this->driversHandle[$cacheName])) {
                unset($this->driversHandle[$cacheName]);
            }
        }
        return $this;
    }
}
