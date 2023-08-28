<?php

namespace shiyunQueue\drive;

/**
 * @method $this setExchangeName(string $name) 设置交换机
 * @method $this setQueueName(string $name)    设置队列名称
 */
trait TraitChannel
{
    /**
     * 交换机名称
     * @var null
     */
    protected $exchangeName = null;
    /**
     * 交换机类型
     * @var null
     */
    protected $exchangeType = 'direct';
    /**
     * string 队列名称
     * @var null
     */
    protected $queueName = null;
    /**
     * 路由key
     */
    protected $routeKey = null;



    public function initChannelSett()
    {
    }
    public function clearChannelSett()
    {
        $this->queueName = null;
    }

    /**
     * 设置交换机
     */
    public function setExchangeName($name = '')
    {
        $this->exchangeName = $name;
        return $this;
    }
    /**
     * 设置交换机类型
     */
    public function setExchangeType($str = '')
    {
        $this->exchangeType = $str;
        return $this;
    }
    /**
     * 设置路由key
     */
    public function setRouteKey($str = '')
    {
        $this->routeKey = $str;
        return $this;
    }
    /**
     * 设置队列名
     * @param $queue
     * @return $this
     */
    public function setQueueName($qName = '')
    {
        $this->queueName = $qName;
        return $this;
    }
    public function getQueueName()
    {
        return $this->queueName;
    }
}
