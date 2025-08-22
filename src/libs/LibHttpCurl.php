<?php

namespace shiyun\libs;

use Exception;

/**
 * http 请求类
 */
class LibHttpCurl
{
    use TraitModeInstance;
    protected array $header = [];
    protected array $option = [];
    protected string $baseUrl = '';
    protected $chHandle = null;
    protected $chCookie = '';
    protected $chTimeout = 20;


    // 私有化构造函数，防止直接创建实例
    private function __construct()
    {
        $this->initHandle();
        $this->setTimeout();
    }
    /**
     * 启动一个CURL会话
     */
    public function initHandle()
    {
        if (empty($this->chHandle)) {
            $this->chHandle = curl_init();
        }
        return $this;
    }
    public function setHeader($option = [])
    {
        $self = (new self());
        self::$header = $option ?? [];
        return $self;
    }
    public function setOption($option = [])
    {
        $self = (new self());
        return $self;
    }
    public function setBaseUrl($baseUrl = '')
    {
        $this->baseUrl = trim($baseUrl);
        return $this;
    }
    /**
     * 设置 Cookie信息
     * 读取储存的Cookie信息
     */
    public function setCookie($str = '')
    {
        $this->chCookie = $str;
        curl_setopt($this->chHandle, CURLOPT_COOKIE, $str);
        return $this;
    }
    /**
     * 设置超时限制防止死循环
     */
    public function setTimeout($time = 20)
    {
        $this->chTimeout = $time;
        curl_setopt($this->chHandle, CURLOPT_TIMEOUT, $this->chTimeout);
        return $this;
    }
    /**
     * 模拟用户使用的浏览器
     */
    public function setUserAgent()
    {
        $useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20100101 Firefox/23.0';
        curl_setopt($this->chHandle, CURLOPT_USERAGENT, $useragent);
        return $this;
    }
    /**
     * 设置HTTP头字段的数组
     */
    public function setHeader($header = [])
    {
        // 模拟获取内容函数
        $header = array(
            // 'Accept: */*',
            // 'Connection: keep-alive',
            // 'Host: mp.weixin.qq.com',
            // 'Referer: ' . $this->referer,
            // 'X-Requested-With: XMLHttpRequest',
            // 'Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3',
            // 'Accept-Encoding: gzip,deflate,sdch',
            // 'Accept-Language: zh-CN,zh;q=0.8',
            // 'Host:' . $this->host,
            // 'Origin:' . $this->origin,
            // 'Referer:' . $this->referer,
        );
        curl_setopt($this->chHandle, CURLOPT_HTTPHEADER, $header);
    }
    /**
     * 发送一个POST请求
     * @param string $url     请求URL
     * @param array  $params  请求参数
     * @param array  $options 扩展参数
     * @return mixed|string
     */
    public static function post($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'POST', $options);
        return $req['ret'] ? $req['msg'] : '';
    }

    /**
     * 发送一个GET请求
     * @param string $url     请求URL
     * @param array  $params  请求参数
     * @param array  $options 扩展参数
     * @return mixed|string
     */
    public static function get($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'GET', $options);
        return $req['ret'] ? $req['msg'] : '';
    }
    /**
     * CURL发送Request请求,含POST和REQUEST
     * @param string $url     请求的链接
     * @param mixed  $params  传递的参数
     * @param string $method  请求的方法
     * @param mixed  $options CURL的参数
     * @return array
     */
    public static function sendRequest($url, $params = [], $method = 'POST', $options = [])
    {
        $method = strtoupper($method);
        $protocol = substr($url, 0, 5);
        $query_string = is_array($params) ? http_build_query($params) : $params;

        $ch = curl_init();
        $defaults = [];
        if ('GET' == $method) {
            $geturl = $query_string ? $url . (stripos($url, "?") !== false ? "&" : "?") . $query_string : $url;
            $defaults[CURLOPT_URL] = $geturl;
        } else {
            $defaults[CURLOPT_URL] = $url;
            if ($method == 'POST') {
                $defaults[CURLOPT_POST] = 1;
            } else {
                $defaults[CURLOPT_CUSTOMREQUEST] = $method;
            }
            $defaults[CURLOPT_POSTFIELDS] = $query_string;
        }

        $defaults[CURLOPT_HEADER] = false;
        $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
        $defaults[CURLOPT_FOLLOWLOCATION] = true;
        $defaults[CURLOPT_RETURNTRANSFER] = true;
        $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
        $defaults[CURLOPT_TIMEOUT] = 3;

        // disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if ('https' == $protocol) {
            $defaults[CURLOPT_SSL_VERIFYPEER] = false;
            $defaults[CURLOPT_SSL_VERIFYHOST] = false;
        }

        curl_setopt_array($ch, (array)$options + $defaults);

        $ret = curl_exec($ch);
        $err = curl_error($ch);

        if (false === $ret || !empty($err)) {
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            return [
                'ret'   => false,
                'errno' => $errno,
                'msg'   => $err,
                'info'  => $info,
            ];
        }
        curl_close($ch);
        return [
            'ret' => true,
            'msg' => $ret,
        ];
    }

    /**
     * GET 请求
     * @param string $url
     * @return mixed
     */
    public function get2($url)
    {
        curl_setopt($this->chHandle, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->chHandle, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($this->chHandle, CURLOPT_FAILONERROR, false);
        curl_setopt($this->chHandle, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        curl_setopt($this->chHandle, CURLOPT_HEADER, false);

        // curl_setopt($this->chHandle, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        // curl_setopt($this->chHandle, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        // curl_setopt($this->chHandle, CURLOPT_HTTPGET, 1); // 发送一个常规的GET请求
        // curl_setopt($this->chHandle, CURLOPT_HEADER, $this->getHeader); // 显示返回的Header区域内容

        if (str_contains('$' . $url, 'https://')) {
            curl_setopt($this->chHandle, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            // curl_setopt($this->chHandle, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
            // curl_setopt($this->chHandle, CURLOPT_SSLVERSION, true); // CURL_SSLVERSION_TLSv1
        }
        /**
         * 执行请求并获取响应
         */
        $curlResponse = curl_exec($this->chHandle);
        $curlInfo = curl_getinfo($this->chHandle);
        if ($curlResponse === false || $curlInfo['http_code'] != 200) {
            $curlError = curl_error($this->chHandle);
            throw new Exception($curlError);
        }
        curl_close($this->chHandle);
        if (is_string($curlResponse) && json_validate($curlResponse)) {
            return json_decode($curlResponse, true);
        }
        return $curlResponse;
    }

    /**
     * post请求
     * @param string $url
     * @param array $params
     * @return mixed|array|string $json
     */
    public function post2($url, $params = [])
    {
        // $chObj = curl_init(); // 启动一个curl会话

        $this->chHandle = curl_init();
        curl_setopt($this->chHandle, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($this->chHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->chHandle, CURLOPT_HEADER, false);
        if (str_contains('$' . $url, 'https://')) {
            curl_setopt($this->chHandle, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($this->chHandle, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($this->chHandle, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($this->chHandle, CURLOPT_POSTFIELDS, $params); // Post提交的数据包

        // curl_setopt($this->chHandle, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        // curl_setopt($this->chHandle, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        // curl_setopt($this->chHandle, CURLOPT_HEADER, $this->getHeader); // 显示返回的Header区域内容
        // curl_setopt($this->chHandle, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        // curl_setopt($this->chHandle, CURLOPT_HTTPHEADER, array(
        //     'Content-Type: application/json; charset=utf-8',
        //     'Content-Length: ' . strlen($sendData)
        // ));
        /**
         * 执行请求并获取响应
         */
        $curlResponse = curl_exec($this->chHandle);
        $curlInfo = curl_getinfo($this->chHandle);
        if ($curlResponse === false || $curlInfo['http_code'] != 200) {
            $curlError = curl_error($this->chHandle);
            throw new Exception($curlError);
        }
        curl_close($this->chHandle);
        if (is_string($curlResponse) && json_validate($curlResponse)) {
            return json_decode($curlResponse, true);
        }
        return $curlResponse;
    }
    /**
     * 异步发送一个请求
     * @param string $url    请求的链接
     * @param mixed  $params 请求的参数
     * @param string $method 请求的方法
     * @return boolean TRUE
     */
    public static function sendAsyncRequest($url, $params = [], $method = 'POST')
    {
        $method = strtoupper($method);
        $method = $method == 'POST' ? 'POST' : 'GET';
        //构造传递的参数
        if (is_array($params)) {
            $post_params = [];
            foreach ($params as $k => &$v) {
                if (is_array($v)) {
                    $v = implode(',', $v);
                }
                $post_params[] = $k . '=' . urlencode($v);
            }
            $post_string = implode('&', $post_params);
        } else {
            $post_string = $params;
        }
        $parts = parse_url($url);
        //构造查询的参数
        if ($method == 'GET' && $post_string) {
            $parts['query'] = isset($parts['query']) ? $parts['query'] . '&' . $post_string : $post_string;
            $post_string = '';
        }
        $parts['query'] = isset($parts['query']) && $parts['query'] ? '?' . $parts['query'] : '';
        //发送socket请求,获得连接句柄
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 3);
        if (!$fp) {
            return false;
        }
        //设置超时时间
        stream_set_timeout($fp, 3);
        $out = "{$method} {$parts['path']}{$parts['query']} HTTP/1.1\r\n";
        $out .= "Host: {$parts['host']}\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if ($post_string !== '') {
            $out .= $post_string;
        }
        fwrite($fp, $out);
        //不用关心服务器返回结果
        //echo fread($fp, 1024);
        fclose($fp);
        return true;
    }


    public function httpGet($apiUrl = '', $reqData = [])
    {
        if (empty($apiUrl)) {
            return '';
        }
        if (empty($reqData)) {
            $apiData = http_build_query($reqData);
            $apiUrl .= '?' . $apiData;
        }
        $http = new \GuzzleHttp\Client();
        $response = $http->request('GET', $apiUrl);
        $result = json_decode($response->getBody(), true);
        return $result;
    }

    public static $way = 0;
    // 手动设置访问方式
    public static function setWay($way)
    {
        self::$way = intval($way);
    }
    public static function getSupport()
    {
        // 如果指定访问方式，则按指定的方式去访问
        if (isset(self::$way) && in_array(self::$way, array(
            1,
            2,
            3
        )))
            return self::$way;

        // 自动获取最佳访问方式
        if (function_exists('curl_init')) {
            // curl方式
            return 1;
        } else if (function_exists('fsockopen')) {
            // socket
            return 2;
        } else if (function_exists('file_get_contents')) {
            // php系统函数file_get_contents
            return 3;
        } else {
            return 0;
        }
    }
    // 通过get方式获取数据
    public static function doGet($url, $timeout = 5, $header = "")
    {
        if (empty($url) || empty($timeout))
            return false;
        if (!preg_match('/^(http|https)/is', $url))
            $url = "http://" . $url;
        $code = self::getSupport();
        switch ($code) {
            case 1:
                return self::curlGet($url, $timeout, $header);
                break;
            case 2:
                return self::socketGet($url, $timeout, $header);
                break;
            case 3:
                return self::phpGet($url, $timeout, $header);
                break;
            default:
                return false;
        }
    }
    // 通过POST方式发送数据
    public static function doPost($url, $post_data = array(), $timeout = 5, $header = "")
    {
        if (empty($url) || empty($post_data) || empty($timeout))
            return false;
        if (!preg_match('/^(http|https)/is', $url))
            $url = "http://" . $url;
        $code = self::getSupport();
        switch ($code) {
            case 1:
                return self::curlPost($url, $post_data, $timeout, $header);
                break;
            case 2:
                return self::socketPost($url, $post_data, $timeout, $header);
                break;
            case 3:
                return self::phpPost($url, $post_data, $timeout, $header);
                break;
            default:
                return false;
        }
    }

    function httpExists($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, 1); // 不下载
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        return (curl_exec($ch) !== false) ? true : false;
    }
    private function xxxxGet()
    {
        $options = array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain'
            )
        );
        curl_setopt_array($ch, $options);
    }


    // 通过curl get数据
    public static function curlGet($url, $timeout = 500, $header = "")
    {
        $header = empty($header) ? self::defaultHeader() : $header;
        $ch = curl_init();

        // 设置头文件的信息作为数据流输出
        // curl_setopt($curl, CURLOPT_HEADER, 1)

        curl_setopt($ch, CURLOPT_URL, $url);
        // 设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $header
        )); // 模拟的header头
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
        return json_decode($result, 1);
    }

    /**
     * curl模拟请求方法
     * @param $url
     * @param $cookie
     * @param array $data
     * @param $method
     * @param array $headers
     * @return mixed
     */
    function http_request($url, $cookie, $data = array(), $method = array(), $headers = array())
    {
        $curl = curl_init();
        if (count($data) && $method == "GET") {
            $data = array_filter($data);
            $url .= "?" . http_build_query($data);
            $url = str_replace(array(
                '%5B0%5D'
            ), array(
                '[]'
            ), $url);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (count($headers)) {
            $head = array();
            foreach ($headers as $name => $value) {
                $head[] = $name . ":" . $value;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $head);
        }
        $method = strtoupper($method);
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        if (!empty($cookie)) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);

        // https://www.cnblogs.com/wicub/p/6669018.html
        $is_errno = curl_errno($curl);
        if ($is_errno) { // 捕抓异常
            // 如果是 docker 下curl ，需要在php 配置 extra_hosts
            // var_dump ( 'Errno' . $is_errno );
            // return 'Errno' . $is_errno;
        }
        curl_close($curl);
        return $output;
    }

    // curl 请求
    protected function curlHttpsRequest($url, $data = null, $header = false, $method = "")
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($header)) {
            // curl_setopt($curl,CURLOPT_HEADER,0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            //			curl_setopt ( $curl, CURLOPT_POSTFIELDS, http_build_query($data) );
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        if (!empty($method)) {
            // 3)设置提交方式
            switch ($method) {
                case "GET":
                    curl_setopt($curl, CURLOPT_HTTPGET, true);
                    break;
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, true);
                    break;
                case "PUT": // 使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请求。这对于执行"DELETE" 或者其他更隐蔽的HTT
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                    break;
                case "DELETE":
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                    break;
            }
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );

        $output = curl_exec($curl);
        $is_errno = curl_errno($curl);
        if ($is_errno) {
            return 'Errno' . $is_errno;
        }
        curl_close($curl);
        return $output;
    }
    /**
     * 向Rest服务器发送请求
     * @param string $http_type http类型,比如https
     * @param string $method 请求方式，比如POST
     * @param string $url 请求的url
     * @return string $data 请求的数据
     */
    public static function httpPost2($http_type, $method, $url, $data)
    {
        $ch = curl_init();
        if (strstr($http_type, 'https')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            $url = $url . '?' . $data;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100000); // 超时时间

        try {
            $ret = curl_exec($ch);
        } catch (\Exception $e) {
            curl_close($ch);
            return json_encode(array(
                'ret' => 0,
                'msg' => 'failure'
            ));
        }
        curl_close($ch);
        return $ret;
    }
    // 通过curl post数据
    public static function curlPost($url, $post_data = array(), $timeout = 5, $header = "")
    {
        $header = empty($header) ? '' : $header;
        $post_string = http_build_query($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $header
        )); // 模拟的header头
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    // 通过socket get数据
    public static function socketGet($url, $timeout = 5, $header = "")
    {
        $header = empty($header) ? self::defaultHeader() : $header;
        $url2 = parse_url($url);
        $url2["path"] = isset($url2["path"]) ? $url2["path"] : "/";
        $url2["port"] = isset($url2["port"]) ? $url2["port"] : 80;
        $url2["query"] = isset($url2["query"]) ? "?" . $url2["query"] : "";
        $host_ip = @gethostbyname($url2["host"]);

        if (($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $timeout)) < 0) {
            return false;
        }
        $request = $url2["path"] . $url2["query"];
        $in = "GET " . $request . " HTTP/1.0\r\n";
        if (false === strpos($header, "Host:")) {
            $in .= "Host: " . $url2["host"] . "\r\n";
        }
        $in .= $header;
        $in .= "Connection: Close\r\n\r\n";

        if (!@fwrite($fsock, $in, strlen($in))) {
            @fclose($fsock);
            return false;
        }
        return self::GetHttpContent($fsock);
    }
    // 通过socket post数据
    public static function socketPost($url, $post_data = array(), $timeout = 5, $header = "")
    {
        $header = empty($header) ? self::defaultHeader() : $header;
        $post_string = http_build_query($post_data);

        $url2 = parse_url($url);
        $url2["path"] = ($url2["path"] == "" ? "/" : $url2["path"]);
        $url2["port"] = ($url2["port"] == "" ? 80 : $url2["port"]);
        $host_ip = @gethostbyname($url2["host"]);
        $fsock_timeout = $timeout; // 超时时间
        if (($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $fsock_timeout)) < 0) {
            return false;
        }
        $request = $url2["path"] . ($url2["query"] ? "?" . $url2["query"] : "");
        $in = "POST " . $request . " HTTP/1.0\r\n";
        $in .= "Host: " . $url2["host"] . "\r\n";
        $in .= $header;
        $in .= "Content-type: application/x-www-form-urlencoded\r\n";
        $in .= "Content-Length: " . strlen($post_string) . "\r\n";
        $in .= "Connection: Close\r\n\r\n";
        $in .= $post_string . "\r\n\r\n";
        unset($post_string);
        if (!@fwrite($fsock, $in, strlen($in))) {
            @fclose($fsock);
            return false;
        }
        return self::GetHttpContent($fsock);
    }

    // 通过file_get_contents函数get数据
    public static function phpGet($url, $timeout = 5, $header = "")
    {
        $header = empty($header) ? self::defaultHeader() : $header;
        $opts = array(
            'http' => array(
                'protocol_version' => '1.0', // http协议版本(若不指定php5.2系默认为http1.0)
                'method' => "GET", // 获取方式
                'timeout' => $timeout, // 超时时间
                'header' => $header
            )
        );
        $context = stream_context_create($opts);
        return @file_get_contents($url, false, $context);
    }
    // 通过file_get_contents 函数post数据
    public static function phpPost($url, $post_data = array(), $timeout = 5, $header = "")
    {
        $header = empty($header) ? self::defaultHeader() : $header;
        $post_string = http_build_query($post_data);
        $header .= "Content-length: " . strlen($post_string);
        $opts = array(
            'http' => array(
                'protocol_version' => '1.0', // http协议版本(若不指定php5.2系默认为http1.0)
                'method' => "POST", // 获取方式
                'timeout' => $timeout, // 超时时间
                'header' => $header,
                'content' => $post_string
            )
        );
        $context = stream_context_create($opts);
        return @file_get_contents($url, false, $context);
    }

    // 默认模拟的header头
    public static function defaultHeader()
    {
        $header = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12\r\n";
        $header .= "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
        $header .= "Accept-language: zh-cn,zh;q=0.5\r\n";
        $header .= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
        return $header;
    }
    // 获取通过socket方式get和post页面的返回数据
    static private function GetHttpContent($fsock = null)
    {
        $out = null;
        while ($buff = @fgets($fsock, 2048)) {
            $out .= $buff;
        }
        fclose($fsock);
        $pos = strpos($out, "\r\n\r\n");
        $head = substr($out, 0, $pos); // http head
        $status = substr($head, 0, strpos($head, "\r\n")); // http status line
        $body = substr($out, $pos + 4, strlen($out) - ($pos + 4)); // page body
        if (preg_match("/^HTTP\/\d\.\d\s([\d]+)\s.*$/", $status, $matches)) {
            if (intval($matches[1]) / 100 == 2) {
                return $body;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('mime_content_type')) {
    /**
	 +----------------------------------------------------------
     * 获取文件的mime_content类型
	 +----------------------------------------------------------
     * @return string
	 +----------------------------------------------------------
     */
    function mime_content_type($filename)
    {
        static $contentType = array(
            'ai' => 'application/postscript',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'asc' => 'application/pgp', // changed by skwashd - was text/plain
            'asf' => 'video/x-ms-asf',
            'asx' => 'video/x-ms-asf',
            'au' => 'audio/basic',
            'avi' => 'video/x-msvideo',
            'bcpio' => 'application/x-bcpio',
            'bin' => 'application/octet-stream',
            'bmp' => 'image/bmp',
            'c' => 'text/plain', // or 'text/x-csrc', //added by skwashd
            'cc' => 'text/plain', // or 'text/x-c++src', //added by skwashd
            'cs' => 'text/plain', // added by skwashd - for C# src
            'cpp' => 'text/x-c++src', // added by skwashd
            'cxx' => 'text/x-c++src', // added by skwashd
            'cdf' => 'application/x-netcdf',
            'class' => 'application/octet-stream', // secure but application/java-class is correct
            'com' => 'application/octet-stream', // added by skwashd
            'cpio' => 'application/x-cpio',
            'cpt' => 'application/mac-compactpro',
            'csh' => 'application/x-csh',
            'css' => 'text/css',
            'csv' => 'text/comma-separated-values', // added by skwashd
            'dcr' => 'application/x-director',
            'diff' => 'text/diff',
            'dir' => 'application/x-director',
            'dll' => 'application/octet-stream',
            'dms' => 'application/octet-stream',
            'doc' => 'application/msword',
            'dot' => 'application/msword', // added by skwashd
            'dvi' => 'application/x-dvi',
            'dxr' => 'application/x-director',
            'eps' => 'application/postscript',
            'etx' => 'text/x-setext',
            'exe' => 'application/octet-stream',
            'ez' => 'application/andrew-inset',
            'gif' => 'image/gif',
            'gtar' => 'application/x-gtar',
            'gz' => 'application/x-gzip',
            'h' => 'text/plain', // or 'text/x-chdr',//added by skwashd
            'h++' => 'text/plain', // or 'text/x-c++hdr', //added by skwashd
            'hh' => 'text/plain', // or 'text/x-c++hdr', //added by skwashd
            'hpp' => 'text/plain', // or 'text/x-c++hdr', //added by skwashd
            'hxx' => 'text/plain', // or 'text/x-c++hdr', //added by skwashd
            'hdf' => 'application/x-hdf',
            'hqx' => 'application/mac-binhex40',
            'htm' => 'text/html',
            'html' => 'text/html',
            'ice' => 'x-conference/x-cooltalk',
            'ics' => 'text/calendar',
            'ief' => 'image/ief',
            'ifb' => 'text/calendar',
            'iges' => 'model/iges',
            'igs' => 'model/iges',
            'jar' => 'application/x-jar', // added by skwashd - alternative mime type
            'java' => 'text/x-java-source', // added by skwashd
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'js' => 'application/x-javascript',
            'kar' => 'audio/midi',
            'latex' => 'application/x-latex',
            'lha' => 'application/octet-stream',
            'log' => 'text/plain',
            'lzh' => 'application/octet-stream',
            'm3u' => 'audio/x-mpegurl',
            'man' => 'application/x-troff-man',
            'me' => 'application/x-troff-me',
            'mesh' => 'model/mesh',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mif' => 'application/vnd.mif',
            'mov' => 'video/quicktime',
            'movie' => 'video/x-sgi-movie',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'mpe' => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpga' => 'audio/mpeg',
            'ms' => 'application/x-troff-ms',
            'msh' => 'model/mesh',
            'mxu' => 'video/vnd.mpegurl',
            'nc' => 'application/x-netcdf',
            'oda' => 'application/oda',
            'patch' => 'text/diff',
            'pbm' => 'image/x-portable-bitmap',
            'pdb' => 'chemical/x-pdb',
            'pdf' => 'application/pdf',
            'pgm' => 'image/x-portable-graymap',
            'pgn' => 'application/x-chess-pgn',
            'pgp' => 'application/pgp', // added by skwashd
            'php' => 'application/x-httpd-php',
            'php3' => 'application/x-httpd-php3',
            'pl' => 'application/x-perl',
            'pm' => 'application/x-perl',
            'png' => 'image/png',
            'pnm' => 'image/x-portable-anymap',
            'po' => 'text/plain',
            'ppm' => 'image/x-portable-pixmap',
            'ppt' => 'application/vnd.ms-powerpoint',
            'ps' => 'application/postscript',
            'qt' => 'video/quicktime',
            'ra' => 'audio/x-realaudio',
            'rar' => 'application/octet-stream',
            'ram' => 'audio/x-pn-realaudio',
            'ras' => 'image/x-cmu-raster',
            'rgb' => 'image/x-rgb',
            'rm' => 'audio/x-pn-realaudio',
            'roff' => 'application/x-troff',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'rtf' => 'text/rtf',
            'rtx' => 'text/richtext',
            'sgm' => 'text/sgml',
            'sgml' => 'text/sgml',
            'sh' => 'application/x-sh',
            'shar' => 'application/x-shar',
            'shtml' => 'text/html',
            'silo' => 'model/mesh',
            'sit' => 'application/x-stuffit',
            'skd' => 'application/x-koan',
            'skm' => 'application/x-koan',
            'skp' => 'application/x-koan',
            'skt' => 'application/x-koan',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'snd' => 'audio/basic',
            'so' => 'application/octet-stream',
            'spl' => 'application/x-futuresplash',
            'src' => 'application/x-wais-source',
            'stc' => 'application/vnd.sun.xml.calc.template',
            'std' => 'application/vnd.sun.xml.draw.template',
            'sti' => 'application/vnd.sun.xml.impress.template',
            'stw' => 'application/vnd.sun.xml.writer.template',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc' => 'application/x-sv4crc',
            'swf' => 'application/x-shockwave-flash',
            'sxc' => 'application/vnd.sun.xml.calc',
            'sxd' => 'application/vnd.sun.xml.draw',
            'sxg' => 'application/vnd.sun.xml.writer.global',
            'sxi' => 'application/vnd.sun.xml.impress',
            'sxm' => 'application/vnd.sun.xml.math',
            'sxw' => 'application/vnd.sun.xml.writer',
            't' => 'application/x-troff',
            'tar' => 'application/x-tar',
            'tcl' => 'application/x-tcl',
            'tex' => 'application/x-tex',
            'texi' => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tgz' => 'application/x-gtar',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'tr' => 'application/x-troff',
            'tsv' => 'text/tab-separated-values',
            'txt' => 'text/plain',
            'ustar' => 'application/x-ustar',
            'vbs' => 'text/plain', // added by skwashd - for obvious reasons
            'vcd' => 'application/x-cdlink',
            'vcf' => 'text/x-vcard',
            'vcs' => 'text/calendar',
            'vfb' => 'text/calendar',
            'vrml' => 'model/vrml',
            'vsd' => 'application/vnd.visio',
            'wav' => 'audio/x-wav',
            'wax' => 'audio/x-ms-wax',
            'wbmp' => 'image/vnd.wap.wbmp',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wm' => 'video/x-ms-wm',
            'wma' => 'audio/x-ms-wma',
            'wmd' => 'application/x-ms-wmd',
            'wml' => 'text/vnd.wap.wml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'wmls' => 'text/vnd.wap.wmlscript',
            'wmlsc' => 'application/vnd.wap.wmlscriptc',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wmz' => 'application/x-ms-wmz',
            'wrl' => 'model/vrml',
            'wvx' => 'video/x-ms-wvx',
            'xbm' => 'image/x-xbitmap',
            'xht' => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'xls' => 'application/vnd.ms-excel',
            'xlt' => 'application/vnd.ms-excel',
            'xml' => 'application/xml',
            'xpm' => 'image/x-xpixmap',
            'xsl' => 'text/xml',
            'xwd' => 'image/x-xwindowdump',
            'xyz' => 'chemical/x-xyz',
            'z' => 'application/x-compress',
            'zip' => 'application/zip'
        );
        $type = strtolower(substr(strrchr($filename, '.'), 1));
        if (isset($contentType[$type])) {
            $mime = $contentType[$type];
        } else {
            $mime = 'application/octet-stream';
        }
        return $mime;
    }
}

if (!function_exists('image_type_to_extension')) {
    function image_type_to_extension($imagetype)
    {
        if (empty($imagetype))
            return false;
        switch ($imagetype) {
            case IMAGETYPE_GIF:
                return '.gif';
            case IMAGETYPE_JPEG:
                return '.jpg';
            case IMAGETYPE_PNG:
                return '.png';
            case IMAGETYPE_SWF:
                return '.swf';
            case IMAGETYPE_PSD:
                return '.psd';
            case IMAGETYPE_BMP:
                return '.bmp';
            case IMAGETYPE_TIFF_II:
                return '.tiff';
            case IMAGETYPE_TIFF_MM:
                return '.tiff';
            case IMAGETYPE_JPC:
                return '.jpc';
            case IMAGETYPE_JP2:
                return '.jp2';
            case IMAGETYPE_JPX:
                return '.jpf';
            case IMAGETYPE_JB2:
                return '.jb2';
            case IMAGETYPE_SWC:
                return '.swc';
            case IMAGETYPE_IFF:
                return '.aiff';
            case IMAGETYPE_WBMP:
                return '.wbmp';
            case IMAGETYPE_XBM:
                return '.xbm';
            default:
                return false;
        }
    }
}
