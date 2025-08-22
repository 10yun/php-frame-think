<?php

declare(strict_types=1);

namespace shiyun\middleware;

use Closure;
use think\Config;
use think\Request;
use think\Response;

/**
 * 【跨域】
 * @author 福州十云科技有限公司
 * @version 2108
 * @package shiyun\middleware
 */
class BaseCrossMiddle
{
    /**
     * 自定义跨域
     */
    protected array $customerCross = [];
    public function __construct()
    {
        $this->customerCross = syGetConfig('shiyun.app.cross_domain', []);
    }
    /**
     * 处理跨域请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     *            return void
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxAge = 1800;
        // $headerArr .= 'x-token, Cache-Control, Content-Disposition, Host, Sign, Auth-Token, Auth-Identity';
        $headerArr = [
            'Authorization',
            'Content-Type',
            'If-Match',
            'If-Modified-Since',
            'If-None-Match',
            'If-Unmodified-Since',
            'X-CSRF-TOKEN',
            'X-Requested-With',
            'Host',
            'Origin',
            'Accept',
            'Sec-Fetch-Site',
            'Sec-Fetch-Mode',
            'Sec-Fetch-Dest'
        ];
        // 自定义请求方式，解决 无PUT、POST、DELETE 问题
        $headerArr[] = 'x-http-method-override';
        /**
         * 自定义
         */
        if (!empty($this->customerCross) && is_array($this->customerCross)) {
            $headerArr = array_merge($headerArr, $this->customerCross);
        }
        /**
         * 兼容旧版本
         */
        $headerStr = implode(",", $headerArr);
        // var_dump('---', $headerStr);
        header("Access-Control-Allow-Origin: * ");
        header("Access-Control-Allow-Headers: {$headerStr} ");
        header("Access-Control-Max-Age: {$maxAge}");
        $header = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'OPTIONS,GET,POST,PUT,PATCH,DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => $headerStr,
            'Access-Control-Max-Age' => "1800",
            'Access-Control-Request-Headers' => $headerStr
        ];

        // $all_origin = array(
        // 	'http://console.' . env('URL_DOMAIN_BASE')
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
        return $next($request)->header($header);
    }
}
