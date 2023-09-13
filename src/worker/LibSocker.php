<?php

declare(strict_types=1);

namespace shiyunWorker;

/**
 * @todosocket推送
 * @author ctocode-zwj
 * @return string
 */
class LibSocker
{
    protected $socket_host = '0.0.0.0';
    protected $socket_port = 0;

    public function setHost($host = '0.0.0.0')
    {
        $this->socket_host = $host;
        return $this;
    }
    public function setPort($port = 0)
    {
        $this->socket_port = $port;
        return $this;
    }

    public function pushData($data = [])
    {
        // 建立socket连接到内部推送端
        $send_url = "{$this->socket_host}:{$this->socket_port}";
        $client = stream_socket_client($send_url, $errno, $errmsg, 1);
        // 发送数据，注意5556端口是Text协议的端口，Text协议需要在数据末尾加上换行
        fwrite($client, json_encode($data) . "\n");
        // fwrite ( $client, '<ctocode|202|' . $data['shipping_deliveryid'] . '>' . "\n" );
        // 读取推送结
        $result = fread($client, 8192);
        // 读取推送结
        // return fread ( $client, 8192 );
        if ($result == "ok\n") {
            return true;
        } else {
            return false;
        }
    }
    public function worker_push_socket($opt = array())
    {
    }
}
