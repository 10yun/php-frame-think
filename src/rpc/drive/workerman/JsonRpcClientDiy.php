<?php

/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace shiyunRpc\drive\workerman;

use shiyunRpc\drive\InterfaceDriver;
use shiyunWorker\protocols\JsonProtocol;
use Exception;

/**
 * 
 *  RpcClient Rpc客户端
 *  
 *  
 *  示例
 *  // 服务端列表
    $address_array = array(
            'tcp://127.0.0.1:2015',
            'tcp://127.0.0.1:2015'
            );
    // 配置服务端列表
    RpcClient::config($address_array);
    
    $uid = 567;
    $user_client = RpcClient::instance('User');
    // ==同步调用==
    $ret_sync = $user_client->getInfoByUid($uid);
   
    // ==异步调用==
    // 异步发送数据
    $user_client->asend_getInfoByUid($uid);
    $user_client->asend_getEmail($uid);

     这里是其它的业务代码
     ..............................................
     
    // 异步接收数据
    $ret_async1 = $user_client->arecv_getEmail($uid);
    $ret_async2 = $user_client->arecv_getInfoByUid($uid);
 *  
 * @author walkor <worker-man@qq.com>
 */
// class RpcClient
class JsonRpcClient implements InterfaceDriver
{
    /**
     * 发送数据和接收数据的超时时间  单位S
     * @var integer
     */
    const TIME_OUT = 5;

    /**
     * 异步调用发送数据前缀
     * @var string
     */
    const ASYNC_SEND_PREFIX = 'asend_';

    /**
     * 异步调用接收数据
     * @var string
     */
    const ASYNC_RECV_PREFIX = 'arecv_';

    /**
     * 服务端地址
     * @var array
     */
    protected static $addressArray = array();

    /**
     * 异步调用实例
     * @var string
     */
    protected static $asyncInstances = array();

    /**
     * 同步调用实例
     * @var string
     */
    protected static $instances = array();

    /**
     * 到服务端的socket连接
     * @var resource
     */
    protected  $connection = null;

    /**
     * 实例的服务名
     * @var string
     */
    protected $serviceName = '';

    /**
     * 实例的服务名-路径
     * @var string
     */
    protected $serviceFirst = '';

    /**
     * 设置/获取服务端地址
     * @param array $address_array
     */
    public static function config($address_array = array())
    {
        if (!empty($address_array)) {
            self::$addressArray = $address_array;
        }
        return self::$addressArray;
    }

    /**
     * 获取一个实例
     * @param string $service_name
     * @return object instance of RpcClient
     */
    public static function instance($service_name, $serviceFirst = '')
    {
        if (!isset(self::$instances[$service_name])) {
            self::$instances[$service_name] = new self($service_name, $serviceFirst);
        }
        return self::$instances[$service_name];
    }

    /**
     * 构造函数
     * @param string $service_name
     */
    protected function __construct($service_name, $serviceFirst)
    {
        $this->serviceName = $service_name;
        $this->serviceFirst = $serviceFirst;
    }

    /**
     * 调用
     * @param string $method
     * @param array $arguments
     * @throws Exception
     * @return 
     */
    public function __call($method, $arguments)
    {
        // 判断是否是异步发送
        if (0 === strpos($method, self::ASYNC_SEND_PREFIX)) {
            $real_method = substr($method, strlen(self::ASYNC_SEND_PREFIX));
            $instance_key = $real_method . serialize($arguments);
            if (isset(self::$asyncInstances[$instance_key])) {
                throw new Exception($this->serviceName . "->$method(" . implode(',', $arguments) . ") have already been called");
            }
            self::$asyncInstances[$instance_key] = new self($this->serviceName);
            return self::$asyncInstances[$instance_key]->sendData($real_method, $arguments);
        }
        // 如果是异步接受数据
        if (0 === strpos($method, self::ASYNC_RECV_PREFIX)) {
            $real_method = substr($method, strlen(self::ASYNC_RECV_PREFIX));
            $instance_key = $real_method . serialize($arguments);
            if (!isset(self::$asyncInstances[$instance_key])) {
                throw new Exception($this->serviceName . "->asend_$real_method(" . implode(',', $arguments) . ") have not been called");
            }
            $tmp = self::$asyncInstances[$instance_key];
            unset(self::$asyncInstances[$instance_key]);
            return $tmp->recvData();
        }
        // 同步发送接收
        $this->sendData($method, $arguments);
        return $this->recvData();
    }

    /**
     * 发送数据给服务端
     * @param string $method
     * @param array $arguments
     */
    public function sendData($method, $arguments)
    {
        $this->openConnection();
        $bin_data = JsonProtocol::encode(array(
            'serviceFirst'       => $this->serviceFirst,
            'class'              => $this->serviceName,
            'method'         => $method,
            'param_array'  => $arguments,
        ));
        if (fwrite($this->connection, $bin_data) !== strlen($bin_data)) {
            throw new \Exception('Can not send data');
        }
        return true;
    }

    /**
     * 从服务端接收数据
     * @throws Exception
     */
    public function recvData()
    {
        $ret = fgets($this->connection);
        $this->closeConnection();
        if (!$ret) {
            throw new Exception("recvData empty");
        }
        return JsonProtocol::decode($ret);
    }

    /**
     * 打开到服务端的连接
     * @return void
     */
    protected function openConnection()
    {
        $address = self::$addressArray[array_rand(self::$addressArray)];
        $this->connection = stream_socket_client($address, $err_no, $err_msg);
        if (!$this->connection) {
            throw new Exception("can not connect to $address , $err_no:$err_msg");
        }
        stream_set_blocking($this->connection, true);
        stream_set_timeout($this->connection, self::TIME_OUT);
    }

    /**
     * 关闭到服务端的连接
     * @return void
     */
    protected function closeConnection()
    {
        fclose($this->connection);
        $this->connection = null;
    }
}
