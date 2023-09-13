<?php

namespace shiyunWorker\process;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use shiyunWorker\WorkermanServer;
use Workerman\Connection\TcpConnection;
use Workerman\Timer;
use Workerman\Protocols\Http as WorkerHttp;
use Workerman\Worker;
use shiyunWorker\Application;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use shiyunWorker\protocols\http\RequestExtend;

/**
 * Worker http server 命令行服务类
 */
class HttpServer extends WorkermanServer
{
    protected $app;
    protected $rootPath;
    protected $root;
    protected $appInit;
    protected $monitor;
    protected $lastMtime;
    /** @var array Mime mapping. */
    protected static $mimeTypeMap = [];

    protected $responseObj;
    /**
     * 架构函数
     * @access public
     * @param  string $host 监听地址
     * @param  int    $port 监听端口
     * @param  array  $context 参数
     */
    public function __construct($host, $port, $context = [])
    {
        $this->worker = new Worker('http://' . $host . ':' . $port, $context);

        // 设置回调
        foreach ($this->event as $event) {
            if (method_exists($this, $event)) {
                $this->worker->$event = [$this, $event];
            }
        }
    }

    public function setRootPath($path)
    {
        $this->rootPath = $path;
    }

    public function appInit(\Closure $closure)
    {
        $this->appInit = $closure;
    }

    public function setRoot($path)
    {
        $this->root = $path;
    }

    public function setStaticOption($name, $value)
    {
        Worker::${$name} = $value;
    }

    public function setMonitor($interval = 2, $path = [])
    {
        $this->monitor['interval'] = $interval;
        $this->monitor['path']     = (array) $path;
    }

    /**
     * 设置参数
     * @access public
     * @param  array    $option 参数
     * @return void
     */
    public function option(array $option)
    {
        // 设置参数
        if (!empty($option)) {
            foreach ($option as $key => $val) {
                $this->worker->$key = $val;
            }
        }
    }

    /**
     * onWorkerStart 事件回调
     * @access public
     * @param  \Workerman\Worker $worker
     * @return void
     */
    public function onWorkerStart($worker)
    {
        Response::initMimeTypeMap();
        $this->app = new Application($this->rootPath);

        if ($this->appInit) {
            call_user_func_array($this->appInit, [$this->app]);
        }

        $this->app->initialize();

        $this->lastMtime = time();

        $this->app->workerman = $worker;

        $this->app->bind([
            'think\Cookie' => Cookie::class,
        ]);

        if (0 == $worker->id && $this->monitor) {
            $paths = $this->monitor['path'];
            $timer = $this->monitor['interval'] ?: 2;

            Timer::add($timer, function () use ($paths) {
                foreach ($paths as $path) {
                    $dir      = new RecursiveDirectoryIterator($path);
                    $iterator = new RecursiveIteratorIterator($dir);

                    foreach ($iterator as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                            continue;
                        }

                        if ($this->lastMtime < $file->getMTime()) {
                            echo '[update]' . $file . "\n";
                            posix_kill(posix_getppid(), SIGUSR1);
                            $this->lastMtime = $file->getMTime();
                            return;
                        }
                    }
                }
            });
        }
    }

    /**
     * onMessage 事件回调
     * @access public
     * @param  TcpConnection $connection
     * @param  mixed         $data
     * @return void
     */
    public function onMessage($connection, $request)
    {
        $serverData = RequestExtend::server($connection, $request);

        $uri  = parse_url($serverData['REQUEST_URI']);
        $path = $uri['path'] ?? '/';

        $file = $this->root . $path;

        if (!is_file($file)) {
            $this->app->worker($connection, $data, $serverData);
        } else {
            $this->sendFile($connection, $file, $serverData);
        }
    }

    /**
     * 访问资源文件
     * @access protected
     * @param  TcpConnection $connection
     * @param  string        $file 文件名
     * @return string
     */
    protected function sendFile($connection, $file, $serverData)
    {
        $Response = new Response();

        $info        = stat($file);
        $modifiyTime = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';

        if (!empty($serverData['HTTP_IF_MODIFIED_SINCE']) && $info) {
            // Http 304.
            if ($modifiyTime === $serverData['HTTP_IF_MODIFIED_SINCE']) {
                // 304
                // WorkerHttp::header('HTTP/1.1 ');
                $Response->withStatus(304)->withBody('<h3>304 Not Modified</h3>');
                $Response->header('Connection', 'keep-alive');
                // Send nothing but http headers..
                return $connection->close('');
            }
        }

        $mimeType = $this->getMimeType($file);

        // WorkerHttp::header('HTTP/1.1 200 OK');
        // WorkerHttp::header('Connection: ');

        $Response->header('Connection', 'keep-alive');
        if ($mimeType) {
            // WorkerHttp::header('Content-Type: ' . $mimeType);
            $Response->header('Content-Type', $mimeType);
        } else {
            // WorkerHttp::header('Content-Type: application/octet-stream');
            $Response->header('Content-Type', 'application/octet-stream');
            $fileinfo = pathinfo($file);
            $filename = $fileinfo['filename'] ?? '';
            // WorkerHttp::header('Content-Disposition: attachment; filename="' . $filename . '"');
            $Response->header('Content-Disposition', 'attachment; filename="' . $filename . '" ');
        }

        if ($modifiyTime) {
            // WorkerHttp::header('Last-Modified: ' . $modifiyTime);
            $Response->header('Last-Modified', $modifiyTime);
        }
        // WorkerHttp::header('Content-Length: ' . filesize($file));
        $Response->header('Content-Length', filesize($file));

        ob_start();
        readfile($file);
        $content = ob_get_clean();

        return $connection->send($content);
    }

    /**
     * 获取文件类型信息
     * @access protected
     * @param  string $filename 文件名
     * @return string
     */
    protected function getMimeType(string $filename)
    {
        $file_info = pathinfo($filename);
        $extension = $file_info['extension'] ?? '';

        if (isset(self::$mimeTypeMap[$extension])) {
            $mime = self::$mimeTypeMap[$extension];
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $filename);
        }
        return $mime;
    }
    /**
     * 启动
     * @access public
     * @return void
     */
    public function start()
    {
        Worker::runAll();
    }

    /**
     * 停止
     * @access public
     * @return void
     */
    public function stop()
    {
        Worker::stopAll();
    }
}
