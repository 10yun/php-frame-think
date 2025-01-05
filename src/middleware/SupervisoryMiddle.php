<?php

declare(strict_types=1);

namespace shiyun\middleware;

use Closure;
use think\Response;

/**
 * 监控
 * @package shiyun\middleware
 */
class SupervisoryMiddle
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $startTime = microtime(true);  // 开始时间
        $project   = 'tp6';            // 应用名
        $ip        = '127.0.0.1';      // 请求IP
        $transfer  = 'test';           // 调用入口

        $response = $next($request);

        $finishTime = microtime(true);           // 结束时间
        $costTime   = $finishTime - $startTime;  // 运行时长

        $code    = mt_rand(2, 5) * 100;  // 状态码
        $success = $code < 400;          // 是否成功
        // 详细信息，自定义设置
        $details = [
            'time'     => date('Y-m-d H:i:s.', (int)$startTime) . substr((string)$startTime, 11),   // 请求时间（包含毫秒时间）
            'run_time' => $costTime,                                                                // 运行时长
            // .....
        ];

        // 执行上报
        try {
            // 数据打包 多条“\n”隔开
            $data = json_encode([
                'time'     => date('Y-m-d H:i:s.', (int)$startTime) . substr((string)$startTime, 11),
                'project'  => $project,
                'ip'       => $ip,
                'transfer' => $transfer,
                'costTime' => $costTime,
                'success'  => $success ? 1 : 0,
                'code'     => $code,
                'details'  => json_encode($details, 320),
            ], 320) . "\n";

            $client = new \GuzzleHttp\Client(['verify' => false]);
            $client->post(
                // 上报地址
                'http://127.0.0.1:8788/report/statistic/transfer',
                [
                    'headers' => [
                        // 上报认证，不设置默认为当前年份的md5值
                        'authorization' => md5(date('Y'))
                    ],
                    'form_params' => [
                        // 上报数据
                        'transfer' => $data
                    ],
                ]
            );
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $response;
    }
}
