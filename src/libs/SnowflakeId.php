<?php

namespace shiyun\libs;

use InvalidArgumentException;
use Exception;

/**
 * 例子使用
 */
/**
$workerNodeId = 1; // (尽量10位)设置节点ID ,机器ID，根据实际情况设置  
$dataCenterId = 1; // (尽量10位)数据中心ID，根据实际情况设置  
$generator = new SnowflakeId($workerNodeId, $dataCenterId);
$id = $generator->generateId();
echo "Generated Snowflake ID: {$id}\n";
var_dump($id);
 * 
 */


/**
 * 雪花ID算法
 */
class SnowflakeId
{
    // 定义雪花ID的各部分位数
    private static $TIMESTAMP_BITS = 41; // 时间戳位数
    private static $NODE_ID_BITS = 10;    // 节点ID位数
    private static $SEQUENCE_BITS = 12;   // 序列号位数

    // 定义起始时间戳（这里以2020-01-01为例）
    private static $EPOCH = 1577836800000;
    // 相当于UTC时间2010年11月4日01:42:54
    // private static $EPOCH = 1288834974657;

    // 定义最大取值范围
    private static $MAX_NODE_ID;
    private static $MAX_SEQUENCE;

    // 定义偏移量
    private static $TIMESTAMP_SHIFT;
    private static $NODE_ID_SHIFT;
    private static $DC_ID_SHIFT;

    private $workerNodeId; // 机器ID，用于区分不同的机器或实例  
    private $dataCenterId; // 数据中心ID，用于区分不同的数据中心  
    private $lastTimestamp = -1;   // 上一次生成ID的时间戳  
    private $sequence = 0; // 序列号，用于同一毫秒内生成多个ID 

    // 构造函数，初始化参数
    public function __construct($workerNodeId, $dataCenterId)
    {
        // 初始化静态属性
        self::$MAX_NODE_ID = (1 << self::$NODE_ID_BITS) - 1;
        self::$MAX_SEQUENCE = (1 << self::$SEQUENCE_BITS) - 1;
        self::$TIMESTAMP_SHIFT = self::$NODE_ID_BITS + self::$SEQUENCE_BITS;
        self::$NODE_ID_SHIFT = self::$SEQUENCE_BITS;
        self::$DC_ID_SHIFT = 17;

        dd(self::$MAX_NODE_ID);
        if ($workerNodeId < 0 || $workerNodeId > self::$MAX_NODE_ID) {
            throw new InvalidArgumentException("Invalid node ID");
        }
        $this->workerNodeId = $workerNodeId;
        $this->dataCenterId = $dataCenterId;
    }

    // 生成雪花ID  
    public function generateId()
    {
        $currentTimestamp = $this->getCurrentTimestamp();

        // 如果当前时间小于上一次ID生成的时间戳，说明系统时钟回退过，直接抛出异常  
        if ($currentTimestamp < $this->lastTimestamp) {
            $diff = $this->lastTimestamp - $currentTimestamp;
            // throw new RuntimeException("Clock moved backwards");
            throw new Exception("Clock moved backwards. Refusing to generate id for {$diff} milliseconds");
        }

        // 如果当前时间与上一次ID生成的时间戳相同，则序列号自增  
        if ($currentTimestamp === $this->lastTimestamp) {
            // $this->sequence = ($this->sequence + 1) & 0xfff; // 序列号最大为4095 (0xfff)  
            $this->sequence = ($this->sequence + 1) & self::$MAX_SEQUENCE;

            // 如果同一毫秒内的序列数已经达到最大值，则等待到下一毫秒  
            if ($this->sequence === 0) {
                $currentTimestamp = $this->untilNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0; // 不同毫秒内，序列号重置为0  
        }

        $this->lastTimestamp = $currentTimestamp;

        // 拼接ID的各个部分  
        $id = (($currentTimestamp - self::$EPOCH) << self::$TIMESTAMP_SHIFT) |
            (($this->dataCenterId << self::$DC_ID_SHIFT) |
                ($this->workerNodeId << self::$NODE_ID_SHIFT) |
                $this->sequence);

        return $id;
    }

    // 获取当前时间戳（毫秒级）  
    private function getCurrentTimestamp()
    {
        // return floor(microtime(true) * 1000);
        return round(microtime(true) * 1000);
    }

    // 等待到下一毫秒  
    private function untilNextMillis($lastTimestamp)
    {
        $timestamp = $this->getCurrentTimestamp();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->getCurrentTimestamp();
        }
        return $timestamp;
    }
}
