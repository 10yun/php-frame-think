<?php

namespace shiyunRpc;

class JsonRpcService
{
    protected static $instances = [];
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new static();
        }
        return static::$instances[$class];
    }
}
