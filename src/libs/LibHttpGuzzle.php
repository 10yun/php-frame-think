<?php

namespace shiyun\libs;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Client as GuzzleHttpClient;
use shiyun\libs\TraitModeInstance;

class LibHttpGuzzle
{
    use TraitModeInstance;
    protected string $baseUrl = '';
    protected array $header = [];
    public function setHeader($option = [])
    {
        $this->header = $option ?? [];
        return $this;
    }
    public function setOption($option = [])
    {
        return $this;
    }
    public function setBaseUrl($baseUrl = '')
    {
        $this->baseUrl = $baseUrl ?? '';
        return $this;
    }
    public function httpGet($url = '', $data = [])
    {
        try {
            $client = new GuzzleHttpClient(
                [
                    'base_url' => $this->baseUrl,
                ]
            );
            $res = $client->request('GET', $url, [
                // 'auth' => ['user', 'pass']
                'query' => $data,
                'headers' => $this->header
            ]);
            $bodyData = $res->getBody();
            $jsonData = json_decode($bodyData, true);
            return $jsonData;
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'code' => $e->getcode(),
                'error' => $e->getMessage()
            ];
        } catch (ClientException $e) {
            /** 不存在 */
            return [
                'success' => false,
                'code' => $e->getcode(),
                'error' => $e->getMessage()
            ];
        } catch (TransferException $e) {
            return [
                'success' => false,
                'code' => $e->getcode(),
                'error' => $e->getMessage()
            ];
        }
    }
    public function httpPost(string $url = '', array $data = [])
    {
        try {
            $client = new GuzzleHttpClient(
                [
                    'base_uri' => $this->baseUrl,
                ]
            );
            $res = $client->request('POST', $url, [
                // 'auth' => ['user', 'pass']
                'form_params' => $data,
                'headers' => $this->header
            ]);
            $bodyData = $res->getBody();
            $jsonData = json_decode($bodyData, true);
            return $jsonData;
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'code' => $e->getcode(),
                'error' => $e->getMessage()
            ];
        } catch (ClientException $e) {
            /** 不存在 */
            return [
                'success' => false,
                'code' => $e->getcode(),
                'error' => $e->getMessage()
            ];
        } catch (TransferException $e) {
            return [
                'success' => false,
                'code' => $e->getcode(),
                'error' => $e->getMessage()
            ];
        }
    }
    public function httpPut($url = '', $data = []) {}
}
