<?php

namespace shiyun\service;

use Exception;
use Generator;
use SwooleClient;
use SwooleCoroutine;
use thinkhelperArr;
use think\Service;
use think\swooleexceptionRpcClientException;
use think\swoolePool;
use think\swoolerpcclientConnector;
use think\swoolerpcclientGateway;
use think\swoolerpcclientProxy;
use think\swoolerpcJsonParser;
use think\swoolerpcPacker;
use Throwable;

class SwooleRpcServiceLoad  extends Service
{
    public $rpcServices = [];
    /**
     * 注册服务 * * @return mixed
     */
    public function register()
    {
        if (php_sapi_name() == 'fpm-fcgi') {
            if (file_exists($rpc = $this->app->getBasePath() . 'rpc.php')) {
                $this->rpcServices = (array)include $rpc;
            }
        }
    }
    /**
     * 执行服务 * * @return mixed
     */
    public function boot()
    {
        if (!empty($clients = config('swoole.rpc.client')) && $this->rpcServices) {
            try {
                foreach ($this->rpcServices as $name => $abstracts) {
                    $parserClass = config("swoole.rpc.client.{$name}.parser", JsonParser::class);
                    $parser = $this->app->make($parserClass);
                    $gateway = new Gateway($this->createRpcConnector($name), $parser);
                    foreach ($abstracts as $abstract) {
                        $this->app->bind($abstract, function () use ($gateway, $name, $abstract) {
                            return $this->app->invokeClass(Proxy::getClassName($name, $abstract), [$gateway]);
                        });
                    }
                }
            } catch (Exception | Throwable $e) {
            }
        }
    }
    protected function createRpcConnector($name)
    {
        return new class($name) implements Connector
        {
            public $name;
            public function __construct($name)
            {
                $this->name = $name;
            }
            public function sendAndRecv($data)
            {
                if (!$data instanceof Generator) {
                    $data = [$data];
                }
                $config = config('swoole.rpc.client.' . $this->name);
                $client = new Client(SWOOLE_SOCK_TCP);
                $host = Arr::pull($config, 'host');
                $port = Arr::pull($config, 'port');
                $timeout = Arr::pull($config, 'timeout', 5);
                $client->set([
                    'open_length_check' => true,
                    'package_length_type' => Packer::HEADER_PACK,
                    'package_length_offset' => 0,
                    'package_body_offset' => 8,
                ]);
                $client->connect($host, $port, $timeout);
                try {
                    foreach ($data as $string) {
                        if (!$client->send($string)) {
                            $this->onError($client);
                        }
                    }
                    $response = $client->recv();
                    if ($response === false || empty($response)) {
                        $this->onError($client);
                    }
                    return $response;
                } finally {
                    $client->close();
                }
            }
            protected function onError(Client $client)
            {
                $client->close();
                throw new RpcClientException(swoole_strerror($client->errCode), $client->errCode);
            }
        };
    }
}
