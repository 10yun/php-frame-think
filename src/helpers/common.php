<?php

// use shiyun\libs\SnowflakeId;

/**
 * 开始时间 2024-01-01 00:00:00 （1704038400000）毫秒
 * 返回 201370402230894661 （返回18位）
 * 
 */
function create_snowflake_id()
{

    //$workerNodeId  1 - 32767 （2^15-1） 

    $workerNodeId = (int)frameGetEnv('snowflake_node', 1); // 设置节点 ID ,机器ID，根据实际情况设置  
    $dataCenterId = (int)frameGetEnv('snowflake_center', 1);; // 数据中心ID，根据实际情况设置  
    if (empty($workerNodeId) || empty($dataCenterId)) {
        throw new \Exception('create_snowflake_id 雪花ID配置错误');
    }
    if ($workerNodeId > 32767) {
        throw new \Exception('workerNodeId 不能大于 32767');
    }
    $id = \SnowDrift::NextId($workerNodeId);
    return $id;

    // $generator = new SnowflakeId($workerNodeId, $dataCenterId);
    // $id = $generator->generateId();
    // return $id;
}
