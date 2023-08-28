<?php

namespace shiyunQueue\drive;

/**
 * 
 * @method $this setConnectName(string $name)  设置连接
 */
trait TraitConnect
{
    // 连接名称
    protected string $connectName;
    // 连接 host
    protected string $connectHost;
    // 连接 port
    protected string|int $connectPort;
    // 连接对象
    protected $connection;
    // 通道对象
    public $channel = null;
    /**
     * @desc 初始化
     */
    public function initialize()
    {
        $this->getConnection();
    }
    /**
     * 获取链接
     */
    public function getConnection()
    {
        if (empty($this->connection)) {
            $this->newConnection();
        }
        return $this->connection;
    }
    public function newConnection()
    {
        return $this;
    }
    /**
     * 获取连接器名称。
     * @return string
     */
    public function getConnectName()
    {
        return $this->connectName;
    }
    /**
     * 设置连接
     * 设置连接器名称
     * @param string 连接名称
     * @return $this
     */
    public function setConnectName($name = '')
    {
        $connectArr = syGetConfig('shiyun.queue.connections');
        if (!empty($connectArr[$name]) && $connectItem = $connectArr[$name]) {
            $this->setConnectHost($connectItem['connect_host']);
            $this->setConnectPort($connectItem['connect_port']);
            $this->setConnectUser($connectItem['connect_user']);
            $this->setConnectPassword($connectItem['connect_password']);
        }
        $this->connectName = $name;
        return $this;
    }
    // 设置连接host
    public function setConnectHost($str = '')
    {
        $this->connectHost = $str;
        return $this;
    }
    // 设置port
    public function setConnectPort($str = '')
    {
        $this->connectPort = $str;
        return $this;
    }
    // 设置user
    public function setConnectUser($str = '')
    {
        $this->connectUser = $str;
        return $this;
    }
    // 设置password
    public function setConnectPassword($str = '')
    {
        $this->connectPassword = $str;
        return $this;
    }
}
