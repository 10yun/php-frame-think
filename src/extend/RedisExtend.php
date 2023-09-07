<?php

namespace shiyun\extend;

class RedisExtend
{
    protected static $_dbCache = false;
    protected static $_dbCacheConf = array();


    protected static function setDbCache($dbCache = array())
    {
        $redisConf = array();

        $redisObj = new Redis();
        if ($redisObj->connect($redisConf['host'], $redisConf['port']) == false) {
            die($redisObj->getLastError());
        }
        // if($redisObj->auth ( $redisConf['password'] ) == false){
        // die ( $redisObj->getLastError () );
        // }
        /* 认证后就可以进行数据库操作，详情文档参考https://github.com/phpredis/phpredis */
        if ($redisObj->set("foo", "bar") == false) {
            die($redisObj->getLastError());
        }
    }
    public static $_redisObj = NULL;
    /* redis连接对象 */
    protected $_classObj = NULL;
    /* redis配置 */
    protected $_config = NULL;
    /**
     * @action 构造函数
     */
    public function __construct()
    {
        if ($this->_classObj == NULL) {
            $this->_config = $this->getConfig();
            $this->_classObj = $this->connect();
        }
    }
    /**
     * @action 初始化
     */
    public function initialize()
    {
    }
    /**
     * 获取配置
     * @return object db_redis
     */
    protected function getConfig()
    {
        if (class_exists('shiyun\support\Config')) {
            // tp5配置
            $db_redis = Config::get('cache');
        } else {
            // platform 配置
            $redisConfig = include_once root_path() . '/config/database/redis_base.php';
            $db_redis = $redisConfig;
            unset($redisConfig);
        }
        return $db_redis;
    }
    /**
     * 连接
     * @return object classObj
     */
    protected function connect()
    {
        $redisObj = new Redis();
        $function = empty($this->_config['persistent']) ? "connect" : "pconnect";
        if ($redisObj->$function($this->_config['host'], $this->_config['port'], $this->_config['timeout']) == false) {
            die($redisObj->getLastError());
        }

        /* 认证后就可以进行数据库操作，详情文档参考https://github.com/phpredis/phpredis */
        // if($redisObj->auth ( $redisConf['password'] ) == false){
        // die ( $redisObj->getLastError () );
        // }

        $redisObj->select($this->_config['select']);
        return $redisObj;
    }
    protected static function getDbCache($dbCache = array())
    {
        $redisConf = array();

        $redisObj = new Redis();
        if ($redisObj->connect($redisConf['host'], $redisConf['port']) == false) {
            die($redisObj->getLastError());
        }
        // if($redisObj->auth ( $redisConf['password'] ) == false){
        // die ( $redisObj->getLastError () );
        // }
        return $redisObj->get("foo");
    }
    /**
     * 获取缓存
     * @return object Obj
     */
    public function getDbCache($key, $index = '', $type = '', $prefix = '')
    {
        $result = FALSE;
        $diy_key = (empty($prefix) ? $this->_config['prefix'] : $prefix) . $key;
        switch ($type) {
            case 'list':
                // 有问题
                $result = $this->_classObj->lget($diy_key, $index);
                break;
            case 'hash':
                $result = $this->_classObj->hget($diy_key, $index);
                break;
            case 'string':
            default:
                $result = $this->_classObj->get($diy_key);
                break;
        }
        return $result;
    }
    /**
     * 缓存数据
     * @return BOOL 成功则返回 TRUE，失败则返回 FALSE
     */
    public function setDbCache($key, $data = array(), $type = '', $prefix = '', $expire = '')
    {
        $result = FALSE;
        $diy_key = (empty($prefix) ? $this->_config['prefix'] : $prefix) . $key;
        $diy_expire = empty($expire) ? $this->_config['expire'] : $expire;
        switch ($type) {
            case 'list':
                // 有问题
                $result = $this->_classObj->lSet($diy_key, $data['index'], $data['value']);
                break;
            case 'hash':
                $result = $this->_classObj->hSet($diy_key, $data['index'], $data['value']);
                break;
            case 'string':
            default:
                $result = $this->_classObj->set($diy_key, $data['value']);
                if ($result == false) {
                    die($this->_classObj->getLastError());
                }
                break;
        }

        if (!empty($diy_expire)) {
            $this->_classObj->expire($diy_key, $diy_expire);
        }
        return $result;
    }
    /**
     * 删除缓存
     * @param $key string 缓存名称
     * @return BOOL 成功则返回 TRUE，失败则返回 FALSE
     */
    public function rmDbCache($key, $index = '', $type = '', $num = 0, $prefix = '')
    {
        $result = FALSE;
        $diy_key = (empty($prefix) ? $this->_config['prefix'] : $prefix) . $key;
        switch ($type) {
            case 'list':
                // 有问题
                $result = $this->_classObj->lrem($diy_key, $index, $num);
                break;
            case 'hash':
                $result = $this->_classObj->hdel($diy_key, $index);
                break;
            case 'string':
            default:
                $result = $this->_classObj->delete($diy_key);
                break;
        }
        return $result;
    }

    /**
     * 关闭
     * @return BOOL 成功则返回 TRUE，失败则返回 FALSE
     */
    public function close()
    {
        return $this->_classObj->close();
    }
    /**
     *  初始化
     */
    public static function redis_instance()
    {
        if (self::$_redisObj == NULL) {
            self::$_redisObj = new self();
        }
        return self::$_redisObj;
    }

    /**
     * string 字符串
     * @param string $key
     * @param string $index
     * @return object|boolean
     */
    public static function get($key)
    {
        $redis = self::redis_instance();
        return $redis->getDbCache($key, '', 'string');
    }
    public static function set($key, $value, $expires)
    {
        $content = array(
            'value' => $value
        );
        $redis = self::redis_instance();
        return $redis->setDbCache($key, $content, 'string', '', $expires);
    }
    /**
     * hash 类型
     * @param string $key
     * @param string $index
     * @return object|boolean
     */
    public static function getHash($key = '', $index = null)
    {
        $redis = self::redis_instance();
        return $redis->getDbCache($key, $index, 'hash');
    }
    public static function setHash($key, $index, $value)
    {
        $content = array(
            'index' => $index,
            'value' => $value
        );
        $redis = self::redis_instance();
        return $redis->setDbCache($key, $content, 'hash');
    }
    /**
     * @param string $key
     * @param string $index
     * @return object|boolean
     */
    public static function setList($key, $index, $value)
    {
        $content = array(
            'index' => $index,
            'value' => $value
        );
        $redis = self::redis_instance();
        return $redis->setDbCache($key, $content, 'list');
    }
}
