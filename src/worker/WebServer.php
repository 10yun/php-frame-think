<?php

/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace shiyunWorker;

use Workerman\Protocols\Http;
use Workerman\Protocols\HttpCache;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use shiyunWorker\protocols\http\RequestExtend;

/**
 *  WebServer.
 */
class WebServer extends Worker
{
    /**
     * Virtual host to path mapping.
     *
     * demo:['workerman.net'=>'/home', 'www.workerman.net'=>'home/www']
     */
    protected array $serverRoot = array();

    /**
     * Mime mapping.
     */
    protected static array $mimeTypeMap = array();


    /**
     * Used to save user OnWorkerStart callback settings.
     *
     * @var callable
     */
    protected $_onWorkerStart = null;

    /**
     * Add virtual host.
     *
     * @param string $domain
     * @param string $config
     * @return void
     */
    public function addRoot($domain, $config)
    {
        if (\is_string($config)) {
            $config = array('root' => $config);
        }
        $this->serverRoot[$domain] = $config;
    }

    /**
     * Construct.
     *
     * @param string $socket_name
     * @param array  $context_option
     */
    public function __construct($socket_name, array $context_option = array())
    {
        list(, $address) = \explode(':', $socket_name, 2);
        parent::__construct('http:' . $address, $context_option);
        $this->name = 'WebServer';
    }

    /**
     * Run webserver instance.
     *
     * @see Workerman.Worker::run()
     */
    public function run()
    {
        $this->_onWorkerStart = $this->onWorkerStart;
        $this->onWorkerStart  = array($this, 'onWorkerStart');
        $this->onMessage      = array($this, 'onMessage');
        parent::run();
    }

    /**
     * Emit when process start.
     *
     * @throws \Exception
     */
    public function onWorkerStart()
    {
        if (empty($this->serverRoot)) {
            Worker::safeEcho(new \Exception('server root not set, please use WebServer::addRoot($domain, $root_path) to set server root path'));
            exit(250);
        }
        Response::initMimeTypeMap();
        // Init mimeMap. 

        // Try to emit onWorkerStart callback.
        if ($this->_onWorkerStart) {
            try {
                \call_user_func($this->_onWorkerStart, $this);
            } catch (\Exception $e) {
                Worker::stopAll(250, $e);
            } catch (\Error $e) {
                Worker::stopAll(250, $e);
            }
        }
    }

    /**
     * Emit when http message coming.
     *
     * @param TcpConnection $connection
     * @return void
     */
    public function onMessage(TcpConnection $connection, $request)
    {
        //$request->get();
        //$request->post();
        // $request->header(); 
        //$request->cookie();
        //$request->session();
        //$request->uri();
        //$request->path();
        //$request->method(); 
        $serverData = RequestExtend::server($connection, $request);
        $workerman_url_info = \parse_url('http://' . $serverData['HTTP_HOST'] . $serverData['REQUEST_URI']);
        if (!$workerman_url_info) {
            // Http::header('HTTP/1.1 400 Bad Request');
            if (\strtolower($serverData['HTTP_CONNECTION']) === "keep-alive") {
                $connection->send(new Response(400, array(), '<h1>400 Bad Request</h1>'));
            } else {
                $connection->close('<h1>400 Bad Request</h1>');
            }
            return;
        }

        $workerman_path = isset($workerman_url_info['path']) ? $workerman_url_info['path'] : '/';
        $workerman_path_info      = \pathinfo($workerman_path);
        $workerman_file_extension = isset($workerman_path_info['extension']) ? $workerman_path_info['extension'] : '';
        if ($workerman_file_extension === '') {
            $workerman_path           = ($len = \strlen($workerman_path)) && $workerman_path[$len - 1] === '/'
                ? $workerman_path . 'index.php' : $workerman_path . '/index.php';
            $workerman_file_extension = 'php';
        }

        $workerman_siteConfig = isset($this->serverRoot[$serverData['SERVER_NAME']]) ? $this->serverRoot[$serverData['SERVER_NAME']] : \current($this->serverRoot);
        $workerman_root_dir = $workerman_siteConfig['root'];
        $workerman_file = "$workerman_root_dir/$workerman_path";

        if (isset($workerman_siteConfig['additionHeader'])) {
            // Http::header($workerman_siteConfig['additionHeader']);
            Response::header($workerman_siteConfig['additionHeader']);
        }
        if ($workerman_file_extension === 'php' && !\is_file($workerman_file)) {
            $workerman_file = "$workerman_root_dir/index.php";
            if (!\is_file($workerman_file)) {
                $workerman_file           = "$workerman_root_dir/index.html";
                $workerman_file_extension = 'html';
            }
        }
        // File exsits.
        if (\is_file($workerman_file)) {
            // Security check.
            if ((!($workerman_request_realpath = \realpath($workerman_file))
                    || !($workerman_root_dir_realpath = \realpath($workerman_root_dir)))
                || 0 !== \strpos(
                    $workerman_request_realpath,
                    $workerman_root_dir_realpath
                )
            ) {
                // Http::header('HTTP/1.1 400 Bad Request');
                if (\strtolower($serverData['HTTP_CONNECTION']) === "keep-alive") {
                    $connection->send(new Response(400, array(), '<h1>400 Bad Request</h1>'));
                } else {
                    $connection->close('<h1>400 Bad Request</h1>');
                }
                return;
            }

            $workerman_file = \realpath($workerman_file);

            // Request php file.
            if ($workerman_file_extension === 'php') {
                $workerman_cwd = \getcwd();
                \chdir($workerman_root_dir);
                \ini_set('display_errors', 'off');

                \ob_start();
                // Try to include php file.
                try {
                    include $workerman_file;
                } catch (\Exception $e) {
                    // Jump_exit?
                    if ($e->getMessage() !== 'jump_exit') {
                        Worker::safeEcho($e);
                    }
                }
                $content = \ob_get_clean();


                // $content = self::exec_php_file($workerman_file);
                \ini_set('display_errors', 'on');
                if (\strtolower($serverData['HTTP_CONNECTION']) === "keep-alive") {
                    $connection->send($content);
                } else {
                    $connection->close($content);
                }
                \chdir($workerman_cwd);
                return;
            }
            // Send file to client.
            return self::sendFile($connection, $workerman_file, $serverData);
        } else {
            // 404
            // Http::header("HTTP/1.1 404 Not Found"); 
            if (isset($workerman_siteConfig['custom404']) && \file_exists($workerman_siteConfig['custom404'])) {
                $html404 = \file_get_contents($workerman_siteConfig['custom404']);
            } else {
                $html404 = '<html><head><title>404 File not found</title></head><body><center><h3>404 Not Found</h3></center></body></html>';
            }
            if (\strtolower($serverData['HTTP_CONNECTION']) === "keep-alive") {
                $connection->send(new Response(404, array(), $html404));
            } else {
                $connection->close($html404);
            }
            return;
        }
    }
    public static function exec_php_file($file)
    {
        \ob_start();
        // Try to include php file.
        try {
            include $file;
        } catch (\Exception $e) {
            // echo $e;
            // Jump_exit?
            if ($e->getMessage() !== 'jump_exit') {
                Worker::safeEcho($e);
            }
        }
        return \ob_get_clean();
    }
    public static function sendFile($connection, $file_path, $serverData)
    {
        // Check 304.
        $info = \stat($file_path);
        $modified_time = $info ? \date('D, d M Y H:i:s', $info['mtime']) . ' ' . \date_default_timezone_get() : '';
        if (!empty($serverData['HTTP_IF_MODIFIED_SINCE']) && $info) {
            // Http 304.
            if ($modified_time === $serverData['HTTP_IF_MODIFIED_SINCE']) {
                // 304
                // Http::header('HTTP/1.1 304 Not Modified'); 
                // Send nothing but http headers..
                if (\strtolower($serverData['HTTP_CONNECTION']) === "keep-alive") {
                    // $connection->send(new Response(304, array(), 'Not Modified'));
                    $connection->send(new Response(304, array(), ''));
                } else {
                    $connection->close('');
                }
                return;
            }
        }

        // Http header.
        if ($modified_time) {
            $modified_time = "Last-Modified: $modified_time\r\n";
        }
        $file_size = \filesize($file_path);
        $file_info = \pathinfo($file_path);
        $extension = isset($file_info['extension']) ? $file_info['extension'] : '';
        $file_name = isset($file_info['filename']) ? $file_info['filename'] : '';
        $start = 0;
        $content_length = $file_size;

        if (isset($serverData['HTTP_RANGE']) && !empty($serverData['HTTP_RANGE'])) {
            list(, $range) = explode('=', $serverData['HTTP_RANGE'], 2);
            list($start, $end) = explode('-', $range);
            $end = is_numeric($end) ? $end : $file_size - 1;
            $content_length = $end - $start + 1;

            $header = "HTTP/1.1 206 Partial Content\r\n";
            $header .= "Accept-Ranges: bytes\r\n";
            $header .= "Content-Length: {$content_length}\r\n";
            $header .= "Content-Range: bytes {$start}-{$end}/{$file_size}\r\n";
        } else {
            $header = "HTTP/1.1 200 OK\r\n";
            $header .= "Content-Length: {$content_length}\r\n";
        }

        if (isset(self::$mimeTypeMap[$extension])) {
            $header .= "Content-Type: " . self::$mimeTypeMap[$extension] . "\r\n";
        }

        $header .= "Content-Type: application/octet-stream\r\n";
        $header .= "Cache-Control: public\r\n";
        $header .= "Pragma: public\r\n";
        $header .= "Content-Disposition:attachment; filename={$file_name}\r\n";
        $header .= "Connection: keep-alive\r\n";
        $header .= $modified_time;
        $header .= "\r\n";

        $connection->send($header, true);

        // Read file content from disk piece by piece and send to client.
        $connection->fileHandler = \fopen($file_path, 'r');
        \fseek($connection->fileHandler, $start);
        $do_write = function () use ($connection, $content_length) {
            $step_length = 8192;
            // Send buffer not full.
            while (empty($connection->bufferFull)) {
                $read_length = $content_length > $step_length ? $step_length : $content_length;
                // Warning: fread(): Length parameter must be greater than 0
                if ($read_length <= 0) {
                    $read_length = $step_length;
                }
                $content_length -= $read_length;
                // Read from disk.
                $buffer = \fread($connection->fileHandler, $read_length);
                // Read eof.
                if ($buffer === '' || $buffer === false) {
                    return;
                }
                if ($content_length === 0) {
                    $connection->close($buffer, true);
                    return;
                } else {
                    $connection->send($buffer, true);
                }
            }
        };
        // Send buffer full.
        $connection->onBufferFull = function ($connection) {
            $connection->bufferFull = true;
        };
        // Send buffer drain.
        $connection->onBufferDrain = function ($connection) use ($do_write) {
            $connection->bufferFull = false;
            $do_write();
        };
        $do_write();
    }
}
