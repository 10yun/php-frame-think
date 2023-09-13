<?php

namespace shiyun\libs;

/**
 * 设计模式 - 单例模式
 */
trait TraitModeInstance
{
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
    public static function getInstance(): static
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;


        // if (is_null(static::$instance)) {
        //     static::$instance = new static();
        // }
        // return static::$instance;


        // if (is_null(static::$instance)) {
        //     static::$instance = new static;
        // }

        // if (static::$instance instanceof Closure) {
        //     return (static::$instance)();
        // }
    }
}
