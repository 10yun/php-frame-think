<?php

namespace shiyun\extend;

/**
 * 通用状态机核心类
 */
abstract class AbstractStateMachine
{
    // 子类必须定义配置文件相对于项目addons目录的路径，比如：'channel/module/config/state.php'
    protected static string $configFilePath = '';

    // 子类必须定义这些属性
    protected static $transitions = [];
    protected static $initialState = '';
    protected static array $statesMeta = [];
    /**
     * 检查状态转换是否有效
     */
    public static function canTransition(string $from, string $to): bool
    {
        static::ensureInit();

        return isset(static::$transitions[$from])
            && in_array($to, static::$transitions[$from], true);
    }

    /**
     * 获取初始状态
     */
    public static function getInitialState(): string
    {
        static::ensureInit();
        return static::$initialState;
    }

    /**
     * 获取所有可能的状态
     */
    public static function getAllStates(): array
    {
        static::ensureInit();
        return array_keys(static::$transitions);
    }

    /**
     * 获取某个状态允许的转换目标
     */
    public static function getAvailableTransitions(string $from): array
    {
        static::ensureInit();
        return static::$transitions[$from] ?? [];
    }


    /**
     * 获取状态签名对应的 code/message 信息（用于展示或业务逻辑）
     */
    public static function getStateMeta(string $sign): array
    {
        static::ensureInit();
        return static::$statesMeta[$sign] ?? [];
    }

    /**
     * 获取所有状态（含 code 和 message）
     */
    public static function getAllStatesMeta(): array
    {
        static::ensureInit();
        return static::$statesMeta;
    }


    /**
     * 初始化状态机配置，自动加载且只加载一次
     */
    protected static bool $initialized = false;
    protected static function ensureInit(): void
    {
        if (static::$initialized) {
            return;
        }
        static::init();
        static::$initialized = true;
    }

    /**
     * 初始化状态机（建议在模块启动或首次调用时执行）
     */
    public static function init(): void
    {
        if (empty(static::$configFilePath)) {
            throw new \RuntimeException('configFilePath must be set in subclass');
        }
        $fullPath = _PATH_PROJECT_ . 'addons/' . static::$configFilePath;
        if (!file_exists($fullPath)) {
            throw new \RuntimeException("状态配置文件不存在: {$fullPath}");
        }
        $config = include $fullPath;

        static::$transitions = $config['transitions'] ?? [];
        static::$initialState = array_key_first(static::$transitions) ?? '';
        static::$statesMeta = $config['states'] ?? [];
    }
}
