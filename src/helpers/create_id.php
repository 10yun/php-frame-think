<?php

/**
 * PHP生成唯一RequestID类
 * Date:    2018-04-10
 * Author:  fdipzone
 * Version: 1.0
 *
 * Description:
 * PHP实现生成唯一RequestID类，使用 session_create_id ()与uniqid()方法实现，保证唯一性。
 *
 * Func:
 * public  generate 生成唯一请求id
 * private format   格式化请求id
 */

// use shiyun\libs\SnowflakeId;

/**
 * 开始时间 2024-01-01 00:00:00 （1704038400000）毫秒
 * 返回 201370402230894661 （返回18位）
 * 
 */
function create_id_snowflake()
{
    //$workerNodeId  1 - 32767 （2^15-1） 

    $workerNodeId = (int)frameGetEnv('snowflake_node', 1); // 设置节点 ID ,机器ID，根据实际情况设置  
    $dataCenterId = (int)frameGetEnv('snowflake_center', 1);; // 数据中心ID，根据实际情况设置  
    if (empty($workerNodeId) || empty($dataCenterId)) {
        throw new \Exception('create_id_snowflake 雪花ID配置错误');
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
function create_id_uuid()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((float) microtime() * 10000); //optional for php 4.2.0 and up.随便数播种，4.2.0以后不需要了。
        $charid = strtoupper(md5(uniqid(rand(), true))); //根据当前时间（微秒计）生成唯一id.
        $hyphen = chr(45); // "-"
        $uuid = '' . //chr(123)// "{"
            substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
        //.chr(125);// "}"
        return $uuid;
    }
}

function create_id_guid($namespace = '')
{
    $guid = '';
    $uid = uniqid("", true);

    $data = $namespace;
    $serverArr = request()->server();
    $data .= $serverArr['REQUEST_TIME'];
    $data .= $serverArr['HTTP_USER_AGENT'];
    $data .= $serverArr['LOCAL_ADDR'] ?? '';
    $data .= $serverArr['LOCAL_PORT'] ?? '';
    $data .= $serverArr['REMOTE_ADDR'] ?? '';
    $data .= $serverArr['REMOTE_PORT'] ?? '';
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $guid =
        substr($hash, 0, 8) .
        '-' .
        substr($hash, 8, 4) .
        '-' .
        substr($hash, 12, 4) .
        '-' .
        substr($hash, 16, 4) .
        '-' .
        substr($hash, 20, 12);
    return $guid;
}
/**
 * session_create_id
 * 生成唯一请求id
 * @return String
 */
function create_id_session()
{
    // 使用 session_create_id()方法创建前缀
    $prefix =  session_create_id(date('YmdHis'));
    // 使用uniqid()方法创建唯一id
    $request_id = strtoupper(md5(uniqid($prefix, true)));
    // 格式化请求id
    /**
     * 格式化请求id
     * @param  String $request_id 请求id
     * @param  Array  $format     格式
     * @return String
     */
    function _format($request_id, $format = '8,4,4,4,12')
    {

        $tmp = array();
        $offset = 0;
        $cut = explode(',', $format);
        // 根据设定格式化
        if ($cut) {
            foreach ($cut as $v) {
                $tmp[] = substr($request_id, $offset, $v);
                $offset += $v;
            }
        }
        // 加入剩余部分
        if ($offset < strlen($request_id)) {
            $tmp[] = substr($request_id, $offset);
        }
        return implode('-', $tmp);
    }
    return _format($request_id);
}
