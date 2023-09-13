<?php

namespace shiyun\libs;

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
    protected $ContentType = 'json';
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
        $this->ContentType = $type;
        return $this;
    }
    // 返回结果
    public function sendResponse($data = null)
    {
        // 输出结果
        // header ( $this->httpVersion . " " . $statusCode . " " . $statusMessage );
        // $requestContentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : $_SERVER['HTTP_ACCEPT'];

        if (!headers_sent()) {
            http_response_code($this->httpCode);
            switch ($this->ContentType) {
                case 'json':
                    header('Content-Type: application/json; charset=utf-8');
                    break;
                case 'xml':
                    header("Content-Type: application/xml");
                    break;
                case 'html':
                    header("Content-Type: application/html");
                    break;
            }
        }
        $this->sendData($data);
        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
    }

    protected function sendData($data)
    {
        switch ($this->ContentType) {
            case 'json':
                echo $this->isEncode ? $this->encodeJson($data) : $data;
                break;
            case 'xml':
                echo $this->isEncode ? $this->encodeXml($data) : $data;
                break;
            case 'html':
                echo  $data;
                break;
        }
    }
    // json格式
    protected function encodeJson($data = array())
    {
        return json_encode($data, true);
    }
    // xml格式
    protected function encodeXml($data = array())
    { // 创建 SimpleXMLElement 对象
        /* '<?xml version="1.0"?><site></site>' */
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><rest></rest>');
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $xml->addChild($k, $v);
                }
            } else {
                $xml->addChild($key, $value);
            }
        }
        return $xml->asXML();
    }
}
