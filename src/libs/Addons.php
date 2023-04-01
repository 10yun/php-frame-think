<?php

namespace shiyun\libs;

use shiyun\exception\AddonsLoadException;

/**
 * @see \shiyun\libs\Addons
 * @package shiyun\libs\Addons
 * @method static \shiyun\libs\Addons getInstance get(mixed $name = '', string $default = null) 获取cookie
 * @method static \shiyun\libs\Addons setType(string $type) 设置类型
 * @method static \shiyun\libs\Addons checkService(string $service, string $class = '') 验证服务
 * @method static string getService() 获取服务名称
 */
class Addons
{
    protected static $instances = [];
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }
        return self::$instances[$class];
    }
    protected $addons_type = '';
    protected $addons_file = '';
    protected $addons_class = '';
    protected $config_rpc = [];
    public function __construct()
    {
        $this->loadConfig();
    }
    protected function loadConfig()
    {
        $batchPathArr1 = glob(_PATH_PROJECT_ . '/addons/*/config/rpc.php');
        $batchPathArr2 = glob(_PATH_PROJECT_ . '/addons/*/*/config/rpc.php');
        $batchPathArr = array_merge($batchPathArr1, $batchPathArr2);
        $appMapArr = [];
        if (!empty($batchPathArr)) {
            foreach ($batchPathArr as $itemPath) {
                $itemData = include_once $itemPath;
                if (empty($itemData) || !is_array($itemData)) {
                    continue;
                }
                if (!empty($itemData['rpc_service'])) {
                    $appMapArr = array_merge($appMapArr, $itemData['rpc_service']);
                }
            }
        }
        $this->config_rpc = $appMapArr;
    }
    protected function getConfigDir($serName)
    {
        // $a = filemtime("log.txt");
        $serDir = '';
        if (!empty($this->config_rpc[$serName])) {
            return $this->config_rpc[$serName];
        }
        return $serDir;
    }
    public function setType($type)
    {
        if (!in_array($type, ['cache', 'model', 'rpc'])) {
            throw new AddonsLoadException("【addons】[{$this->addons_type}服务]： 服务类型错误 ");
        }
        $this->addons_type = strtolower($type);
        return $this;
    }
    /**
     * @param string $service  应用名
     * @param string $class    功能名
     */
    public function checkService($service = '', $class = '')
    {
        if (empty($service)) {
            throw new AddonsLoadException("【addons】[{$this->addons_type}服务]： {$service} service参数undefined ");
        }
        if (empty($class)) {
            throw new AddonsLoadException("【addons】[{$this->addons_type}服务]： {$class} class参数undefined ");
        }
        $serDir = '';
        if (strpos($service, "/") !== false) {
            $serviceArr = explode("/", $service);
            $serDir = $this->getConfigDir($serviceArr[0]);
            $serDir = $serDir . "{$serviceArr[1]}/";
        } else {
            $serDir = $this->getConfigDir($service);
        }
        $serDir = root_path() . $serDir;
        if (!is_dir($serDir)) {
            throw new AddonsLoadException("【addons】[{$this->addons_type}服务]： {$service} 应用服务不存在 ");
        }
        $className = $class . ucfirst($this->addons_type);
        $className = '';
        $classFile = $serDir . "/{$class}/{$this->addons_type}.php";
        // if (is_file($classFile)) {
        if (!file_exists($classFile)) {
            throw new AddonsLoadException("【addons】[{$this->addons_type}服务]： {$class} 文件不存在");
        }
        $classFile = str_replace("//", "/", $classFile);
        $className = get_class_from_file($classFile);
        require_once $classFile;

        // $classFile = str_replace("/www//", "", $classFile);
        // $classFile = str_replace("//", "\\", $classFile);
        // $classFile = str_replace("/", "\\", $classFile);
        // $classClass = $classAddon . $className;
        // $classClass::class;
        if (!class_exists($className)) {
            throw new AddonsLoadException("【addons】[{$this->addons_type}服务]： {$className} _类名错误");
        }
        $this->addons_class = $className;
        return $this;
    }
    public function getService()
    {
        return $this->addons_class;
    }
}
