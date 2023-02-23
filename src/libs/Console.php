<?php

namespace shiyun\libs;

class Console
{
    /**
     * @var static
     */
    protected static $instance;
    /**
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
    protected $tokens;
    public function __construct($argv = null)
    {
        if ($this->runningInConsole()) {
            if (null === $argv) {
                $argv = $_SERVER['argv'];
                // 去除命令名
                array_shift($argv);
            }
            $this->tokens = $argv;
            $this->getCommandOptions();
        }
    }
    /**
     * 是否运行在命令行下
     * @return bool
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
    protected $options = [];
    public function getOption($name = '')
    {
        return $this->options[$name] ?? '';
    }

    /**
     * 获取所有的选项
     * @return Option[]
     */
    public function getCommandOptions()
    {
        $optAllArr = [];
        foreach ($this->tokens as $key => $val) {
            if (str_starts_with($val, '--')) {
                // 长参数
                $optArr = explode("=", $val);
                $optKey = str_replace("--", "", $optArr[0]);
                $optVal = $optArr[1] ?? '';
                $optAllArr[$optKey] = $optVal;
            } else if (str_starts_with($val, '-')) {
                // 短参数
                $optArr = explode("=", $val);
                $optKey = str_replace("-", "", $optArr[0]);
                $optVal = $optArr[1] ?? '';
                $optAllArr[$optKey] = $optVal;
            }
        }
        $this->options = $optAllArr;
        return $optAllArr;
    }
}
