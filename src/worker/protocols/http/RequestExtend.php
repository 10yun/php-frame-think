<?php

namespace shiyunWorker\protocols\http;

class RequestExtend
{
    public static function server($connection, $request)
    {
        //$request->get();
        //$request->post();
        //$request->header();
        //$request->cookie();
        //$request->session();
        //$request->uri();
        //$request->path();
        //$request->method();

        $HTTP_HOST = $request->header('host');
        $hostArr = explode(":", $HTTP_HOST);
        $serverData = [
            'HTTP_HOST' => $request->header('host'),
            'HTTP_CONNECTION' => $request->header('connection'),
            'QUERY_STRING' => $request->queryString(),
            'REMOTE_ADDR' => $connection->getRemoteIp(),
            'REMOTE_PORT' => $connection->getRemotePort(),
            'REQUEST_METHOD' => $request->method(),
            'REQUEST_URI' => $request->uri(),
            'SERVER_NAME' => $hostArr[0] ?? '',
            'SERVER_PORT' => $hostArr[1] ?? ''
        ];
        return $serverData;
    }
}
