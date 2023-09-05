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
    protected $httpVersion = "HTTP/1.1";

    /**
     * 
     */
    public static function sendSucc()
    {
    }
    public static function sendError()
    {
    }

    public static function sendListData()
    {
    }



    // 返回结果
    public function sendResponse($result_data = null, $send_type = 'json')
    {
        // $statusCode = $result_data['status'];
        // $data = $result_data['status'];
        // $statusMessage = $this->getHttpStatusMessage ( $statusCode );
        // 输出结果
        // header ( $this->httpVersion . " " . $statusCode . " " . $statusMessage );
        $requestContentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : $_SERVER['HTTP_ACCEPT'];
        // TODO 目前强制为 json 返回
        $requestContentType = 'application/json';

        if (strpos($requestContentType, 'application/json') !== false) {
            header('Content-Type: application/json; charset=utf-8');
            echo $this->encodeJson($result_data);
        } else if (strpos($requestContentType, 'application/') !== false) {
            header("Content-Type: application/xml");
            echo $this->encodeXml($result_data);
            exit();
        } else {
            // header ( 'Content-type: text/html; charset=utf-8' );
            header("Content-Type: application/html");
            echo $this->encodeHtml($result_data);
            exit();
        }
    }
    // json格式
    protected function encodeJson($responseData = array())
    {
        return json_encode($responseData, true);
    }
    // xml格式
    protected function encodeXml($responseData = array())
    { // 创建 SimpleXMLElement 对象
        /* '<?xml version="1.0"?><site></site>' */
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><rest></rest>');
        foreach ($responseData as $key => $value) {
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
    // html格式
    protected function encodeHtml($responseData = array())
    {
        $html = "<table border='1'>";
        foreach ($responseData as $key => $value) {
            $html .= "<tr>";
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $html .= "<td>" . $k . "</td><td>" . $v . "</td>";
                }
            } else {
                $html .= "<td>" . $key . "</td><td>" . $value . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }
}
