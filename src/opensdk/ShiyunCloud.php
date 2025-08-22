<?php

namespace shiyunOpensdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception;

class ShiyunCloud
{
    use \shiyun\libs\TraitModeInstance;
    private $config;
    private $client;
    private $requestOptions = [
        'headers' => [],
        'query' => [],
        'form_params' => [],
        'json' => null,
        'method' => 'GET',
        'base_uri' => '',
    ];
    /**
     * 构造函数
     * 
     * @param array $config 配置数组
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'api' => '',
            'scheme' => 'https',
            'version' => '1.0',
            'host' => '',
            'timeout' => 10,
        ], $config);

        $this->client = new Client([
            'timeout' => $this->config['timeout'],
            'base_uri' => $this->config['scheme'] . '://' . $this->config['host'],
        ]);
    }

    /**
     * 静态方法创建实例
     * 
     * @param array $config 配置数组
     * @return self
     */
    public static function rpc(array $config = [])
    {
        return new self($config);
    }

    /**
     * 设置产品名称
     * 
     * @param string $product
     * @return $this
     */
    public function product(string $product)
    {
        $this->config['api'] = $product;
        return $this;
    }

    /**
     * 设置协议方案
     * 
     * @param string $scheme
     * @return $this
     */
    public function scheme(string $scheme)
    {
        $this->config['scheme'] = $scheme;
        $this->updateBaseUri();
        return $this;
    }
    /**
     * 设置API版本
     * 
     * @param string $version
     * @return $this
     */
    public function version(string $version)
    {
        $this->config['version'] = $version;
        $this->requestOptions['query']['Version'] = $version;
        return $this;
    }
    /**
     * 设置API动作
     * 
     * @param string $action
     * @return $this
     */
    public function action(string $action)
    {
        $this->requestOptions['query']['Action'] = $action;
        return $this;
    }
    /**
     * 设置HTTP方法
     * 
     * @param string $method
     * @return $this
     */
    public function method(string $method)
    {
        $this->requestOptions['method'] = strtoupper($method);
        return $this;
    }
    /**
     * 设置主机地址
     * 
     * @param string $host
     * @return $this
     */
    public function host(string $host)
    {
        $this->config['host'] = $host;
        $this->updateBaseUri();
        return $this;
    }
    /**
     * 设置请求选项
     * 
     * @param array $options
     * @return $this
     */
    public function options(array $options)
    {
        foreach ($options as $key => $value) {
            if (isset($this->requestOptions[$key]) && is_array($this->requestOptions[$key])) {
                $this->requestOptions[$key] = array_merge($this->requestOptions[$key], $value);
            } else {
                $this->requestOptions[$key] = $value;
            }
        }
        return $this;
    }
    /**
     * 发送请求
     * 
     * @return array
     * @throws \Exception
     */
    public function request()
    {
        try {
            $response = $this->client->request(
                $this->requestOptions['method'],
                '',
                $this->requestOptions
            );
            $result = json_decode($response->getBody(), true);
            if (isset($result['Code']) && $result['Code'] != 'OK') {
                throw new \Exception($result['Message'] ?? 'Unknown error');
            }
            return $result;
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorMessage = 'Request failed';

            if ($response) {
                $body = json_decode($response->getBody(), true);
                $errorMessage = $body['Message'] ?? $errorMessage;
            }
            throw new \Exception($errorMessage, $e->getCode(), $e);
        }
    }
    /**
     * 更新基础URI
     */
    private function updateBaseUri()
    {
        $this->client = new Client([
            'timeout' => $this->config['timeout'],
            'base_uri' => $this->config['scheme'] . '://' . $this->config['host'],
        ]);
    }
}
