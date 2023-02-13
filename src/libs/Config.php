<?php

namespace shiyun\libs;

class Config
{
    /**
     * @var array
     */
    protected static $_config = [];

    /**
     * @var string
     */
    protected static $_configPath = '';

    /**
     * @var bool
     */
    protected static $_loaded = false;

    public static function init($path)
    {
        self::$_configPath = $path;
    }
    /**
     * @param string|null $key
     * @param mixed $default
     * @return array|mixed|void|null
     */
    public static function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return static::$_config;
        }
        $key_array = \explode('.', $key);
        $value = static::$_config;
        $found = true;
        foreach ($key_array as $index) {
            if (!isset($value[$index])) {
                if (static::$_loaded) {
                    return $default;
                }
                $found = false;
                break;
            }
            $value = $value[$index];
        }
        if ($found) {
            return $value;
        }
        return static::read($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return array|mixed|null
     */
    protected static function read(string $key, $default = null)
    {
        $path = static::$_configPath;
        if ($path === '') {
            return $default;
        }
        $keys = $key_array = \explode('.', $key);
        foreach ($key_array as $index => $section) {
            unset($keys[$index]);
            if (\is_file($file = "$path/$section.php")) {
                $config = include $file;
                return static::find($keys, $config, $default);
            }
            if (!\is_dir($path = "$path/$section")) {
                return $default;
            }
        }
        return $default;
    }

    /**
     * @param array $key_array
     * @param mixed $stack
     * @param mixed $default
     * @return array|mixed
     */
    protected static function find(array $key_array, $stack, $default)
    {
        if (!\is_array($stack)) {
            return $default;
        }
        $value = $stack;
        foreach ($key_array as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }
}
