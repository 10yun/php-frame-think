<?php

namespace shiyun\middleware;

use think\Response;

class BaseCrossMiddle
{
    /**
     * 处理跨域请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     *            return void
     */
    public function handle($request, \Closure $next)
    {
        // frameLogsFile('AllowCrossDomain   ----- ');
        $maxAge = 1800;
        $headers = [
            'Access-Control-Allow-Headers'  => 'x-token, Cache-Control, Content-Disposition, x-requested-with, Host, Sign, Auth-Token, Auth-Identity, Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With',
        ];

        $headerDefault = [
            'Authorization', 'authorization',
            'x-requested-with', 'X-Requested-With',
            'content-type', 'Content-Type',
            'Origin', 'Accept'
        ];
        $Headers_default = implode(",", $headerDefault);
        // 自定义请求方式，解决 无PUT、POST、DELETE 问题
        $Headers_default .= ",x-http-method-override";
        $Headers_default .= ",syOpenAppProject";
        $Headers_default .= ",syOpenAppId";
        $Headers_default .= ",syOpenAppKey";
        $Headers_default .= ",syOpenAppToken";
        $Headers_default .= ",syOpenAppRole";
        $Headers_default .= ",syOpenAppClientPlatform";
        // $Headers_default .= ",If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since";
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: {$Headers_default} ");
        header("Access-Control-Max-Age: {$maxAge}");
        $header = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'OPTIONS,GET,POST,PUT,PATCH,DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => $Headers_default,
            'Access-Control-Max-Age' => "1800",
            'Access-Control-Request-Headers' => $Headers_default
        ];



        // $all_origin = array(
        // 	'http://console.' . env('ctocode.url_domain_base')
        // );
        // $request->header ( 'Origin',$all_origin );
        // OPTIONS请求返回204请求
        if ($request->method(true) === 'OPTIONS') {
            /**
             * 浏览器第一次在处理复杂请求的时候会先发起OPTIONS请求。路由在处理请求的时候会导致PUT请求失败。
             * 在检测到option请求的时候就停止继续执行
             */
            return Response::create()->contentType('application/json')
                ->code(204)
                ->header($header);
        }
        return $next($request);
    }
}
