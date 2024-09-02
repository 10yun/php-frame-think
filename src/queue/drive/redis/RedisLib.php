<?php

namespace shiyunQueue\drive\redis;

use RedisException;
use think\helper\Str;

class RedisLib
{
    protected $config;
    protected $client;
    public function __construct($config)
    {
        $this->config = $config;
        $this->client = $this->createClient();
    }
    protected function createClient()
    {
        $config = $this->config;
        $func   = $config['persistent'] ? 'pconnect' : 'connect';

        $client = new \Redis;
        $client->$func($config['connect_host'], $config['connect_port'], $config['timeout']);

        if ('' != $config['connect_password']) {
            $client->auth($config['connect_password']);
        }

        if (0 != $config['select']) {
            $client->select($config['select']);
        }
        return $client;
    }

    public function __call($name, $arguments)
    {
        try {
            return call_user_func_array([$this->client, $name], $arguments);
        } catch (RedisException $e) {
            if (Str::contains($e->getMessage(), 'went away')) {
                $this->client = $this->createClient();
            }

            throw $e;
        }
    }
}
