<?php

namespace shiyun\libs;

class HttpLibClient
{
    protected static $baseUrl = '';
    protected static $header = [];
    public static function setHeader($option = [])
    {
        $self = (new self());
        self::$header = $option ?? [];
        return $self;
    }
    public static function setOption($option = [])
    {
        $self = (new self());
        return $self;
    }
    public static function setBaseUrl($baseUrl = '')
    {
        $self = (new self());
        self::$baseUrl = $baseUrl ?? '';
        return $self;
    }
    public static function httpGet($url = '', $data = [])
    {
        try {
            $client = new \GuzzleHttp\Client(
                [
                    'base_url' => self::$baseUrl,
                ]
            );
            $res = $client->request('GET', $url, [
                // 'auth' => ['user', 'pass']
                'query' => $data,
                'headers' => self::$header
            ]);
            $bodyData = $res->getBody();
            $jsonData = json_decode($bodyData, true);
            return $jsonData;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            /** 不存在 */
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    public static function httpPost(string $url = '', array $data = [])
    {
        try {
            $client = new \GuzzleHttp\Client(
                [
                    'base_uri' => self::$baseUrl,
                ]
            );
            $res = $client->request('POST', $url, [
                // 'auth' => ['user', 'pass']
                'form_params' => $data,
                'headers' => self::$header
            ]);
            $bodyData = $res->getBody();
            $jsonData = json_decode($bodyData, true);
            return $jsonData;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            /** 不存在 */
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    public static function httpPut($url = '', $data = [])
    {
    }
}
