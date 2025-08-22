<?php

namespace shiyun\libs;

use think\Response as thinkResponse;
use SimpleXMLElement;

/**
 * Response 响应输出类
 * @author ctocode-zhw
 * @version 2017-09-05
 * @remarks 
 * 根据接收到的Content-Type，将Request类返回的数组拼接成对应的格式，加上header后输出
 */

class Response
{
    use \shiyun\libs\TraitModeInstance;
    protected $httpVersion = "HTTP/1.1";
    protected $isEncode = true;
    protected $httpCode = 200;
    protected $contentType = 'json';
    protected $contentTypeHeaders = [
        'json' => 'application/json; charset=utf-8',
        'xml'  => 'application/xml; charset=utf-8',
        'html' => 'text/html; charset=utf-8'
    ];
    public function setCode(int $code)
    {
        $this->httpCode = $code;
        return $this;
    }
    public function setEncode(bool $isEncode = true)
    {
        $this->isEncode = $isEncode;
        return $this;
    }
    public function setType(string $type)
    {
        if (!array_key_exists($type, $this->contentTypeHeaders)) {
            throw new \InvalidArgumentException("Unsupported content type: {$type}");
        }
        $this->contentType = $type;
        return $this;
    }
    /**
     * 发送响应并返回ThinkPHP响应对象
     */
    public function sendResponse(mixed $data): ThinkResponse
    {
        $this->sendHeaders();
        $this->sendBody($data);
        return $this->createThinkResponse($data);
    }
    protected function sendHeaders(): void
    {
        // 输出结果
        // header ( $this->httpVersion . " " . $statusCode . " " . $statusMessage );
        // $requestcontentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : $_SERVER['HTTP_ACCEPT'];
        if (!headers_sent()) {
            http_response_code($this->httpCode);
            header("Content-Type: {$this->contentTypeHeaders[$this->contentType]}");
            // switch ($this->contentType) {
            //     case 'json':
            //         header('Content-Type: application/json; charset=utf-8');
            //         break;
            //     case 'xml':
            //         header("Content-Type: application/xml");
            //         break;
            //     case 'html':
            //         header("Content-Type: application/html");
            //         break;
            // }
        }
    }
    protected function sendBody(mixed $data): void
    {
        echo match ($this->contentType) {
            'json' => $this->isEncode ? $this->encodeJson($data) : $data,
            'xml'  => $this->isEncode ? $this->encodeXml($data) : $data,
            'html' => $data,
            default => throw new \RuntimeException("Unsupported content type: {$this->contentType}")
        };
        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
    }
    protected function createThinkResponse(mixed $data): ThinkResponse
    {
        return ThinkResponse::create($data, $this->contentType, $this->httpCode);
    }

    // json格式
    protected function encodeJson(array $data = [])
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            throw new \RuntimeException("JSON encode failed: " . $e->getMessage());
        }
    }
    // xml格式
    protected function encodeXml(array $data = [])
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException("XML data must be an array");
        }
        try {
            $rootNode = $this->determineRootNode($data);
            $xml = new \SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><{$rootNode}/>");
            foreach ($data as $key => $value) {
                $this->addXmlNode($xml, $key, $value);
            }
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());

            // $dom_sxe = $dom->importNode($dom_sxe, true);
            // $dom_sxe = $dom->appendChild($dom_sxe);
            return $dom->saveXML();
        } catch (\DOMException $e) {
            throw new \RuntimeException("XML generation failed: " . $e->getMessage());
        }
        // $rootNode = $this->sanitizeXmlKey($rootNode);
        // $rootNoteXml = "<{$rootNode}></{$rootNode}>";
        // 创建 SimpleXMLElement 对象
        // foreach ($data as $key => $value) {
        //     if (is_array($value)) {
        //         foreach ($value as $k => $v) {
        //             $k = $this->sanitizeXmlKey($k);
        //             $xml->addChild($k, $v);
        //         }
        //     } else {
        //         $key = $this->sanitizeXmlKey($key);
        //         $xml->addChild($key, $value);
        //     }
        // } // 在 XML 的字符串表示中插入换行符
        // $dom = dom_import_simplexml($xml)->ownerDocument;
    }
    protected function determineRootNode(array $data): string
    {
        $keys = array_keys($data);
        $rootNode = count($keys) === 1 ? $keys[0] : 'ctocode';
        return $this->sanitizeXmlKey($rootNode);
    }

    protected function addXmlNode(SimpleXMLElement $xml, $key, $value): void
    {
        $key = $this->sanitizeXmlKey($key);

        if (is_array($value)) {
            $node = $xml->addChild($key);
            foreach ($value as $subKey => $subValue) {
                $this->addXmlNode($node, $subKey, $subValue);
            }
        } else {
            $xml->addChild($key, htmlspecialchars((string)$value, ENT_XML1));
        }
    }
    protected function sanitizeXmlKey(string $key): string
    {
        // 替换无效XML字符
        return preg_replace('/[^a-z0-9_\-]/i', '_', $key);
        // 使用正则表达式匹配驼峰命名规则
        //  if (preg_match('/^(?:[A-Z][a-z]+|[a-z]+)(?:[A-Z][a-z]+)*$/', $string)) {
        // }
        // 驼峰转下划线
        $key = cc_str_tf_snake($keying);
        // 下划线转驼峰
        $key =  cc_str_xhx_ucwords($key);
        return $key;
    }
}
