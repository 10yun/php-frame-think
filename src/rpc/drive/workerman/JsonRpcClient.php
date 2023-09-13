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
    protected $addressArray = array();

    /**
     * 异步调用实例
     * @var string
     */
    protected $asyncInstances = array();

    /**
     * 到服务端的socket连接
     * @var resource
     */
    protected $connection = null;

    /**
     * 实例的服务名
     * @var string
     */
    protected $serviceName = '';

    protected $config = [];


    protected $reconnectCount = 1;

    public function init($config)
    {
        $this->config = $config;
        if (!empty($config['rpc_server_address'])) {
            $this->addressArray = $config['rpc_server_address'];
        }
        $this->serviceName    = $config['service_name'];
        $this->reconnectCount = $config['reconnect_count'] ?? 1;
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
        if (str_contains($method, self::ASYNC_SEND_PREFIX)) {
            $real_method  = substr($method, strlen(self::ASYNC_SEND_PREFIX));
            $instance_key = $real_method . serialize($arguments);
            if (isset($this->asyncInstances[$instance_key])) {
                throw new Exception(
                    $this->serviceName . "->$method(" . implode(',', $arguments) . ") have already been called"
                );
            }
            $this->asyncInstances[$instance_key] = (new self());
            $this->asyncInstances[$instance_key]->init($this->config);
            return $this->asyncInstances[$instance_key]->sendData($real_method, $arguments);
        }
        // 如果是异步接受数据
        if (str_contains($method, self::ASYNC_RECV_PREFIX)) {
            $real_method  = substr($method, strlen(self::ASYNC_RECV_PREFIX));
            $instance_key = $real_method . serialize($arguments);
            if (!isset($this->asyncInstances[$instance_key])) {
                throw new Exception(
                    $this->serviceName . "->asend_$real_method(" . implode(',', $arguments) . ") have not been called"
                    // $this->serviceName . "->arecv_$real_method(" . implode(',', $arguments) . ") have not been called"
                );
            }
            $tmp = $this->asyncInstances[$instance_key];
            unset($this->asyncInstances[$instance_key]);
            return $tmp->recvData();
        }
        // 同步发送接收
        $this->sendData($method, $arguments);
        $res = $this->recvData();
        return $res;
    }

    /**
     * 发送数据给服务端
     * @param string $method
     * @param array  $arguments
     */
    public function sendData($method, $arguments)
    {
        $this->openConnection();
        $bin_data = JsonProtocol::encode(array(
            'class'       => $this->serviceName,
            'method'      => $method,
            'param_array' => $arguments,
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
        try {
            $ret = fgets($this->connection);
            $this->closeConnection();
            if (!$ret) {
                throw new Exception("recvData empty");
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        return JsonProtocol::decode($ret);
    }

    /**
     * 打开到服务端的连接
     * @return void
     */
    protected function openConnection()
    {
        $address      = $this->addressArray[array_rand($this->addressArray)];
        $connectCount = 0;

        while (!$this->connection && $connectCount < $this->reconnectCount) {
            try {
                $this->connection = stream_socket_client($address, $err_no, $err_msg);
            } catch (\Throwable $e) {
                $connectCount++;
            }
        }
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
