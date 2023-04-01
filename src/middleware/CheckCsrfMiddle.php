<?php

namespace shiyun\middleware;

use think\helper\Str;
use shiyun\support\Db;
use shiyun\support\Cache;
use shiyun\support\Request;

/**
 * ========== 中间件 ==========
 * 表单 csrf 验证
 * ========== #### ==========
 */
class CheckCsrfMiddle
{
    /**
     * @param \think\Request $request
     * @param \Closure $next
     *            return void
     */
    public function handle($request, \Closure $next)
    {
        // 过滤掉不需要csrf验证的请求
        if (in_array($request->method(), [
            'GET', 'HEAD', 'OPTIONS'
        ], true)) {
            return $next($request);
        }
        // Header验证
        $name = '_csrf_token';
        $token = $request->has($name) ? $request->param($name) : $request->header('X-CSRF-TOKEN');
        if (!empty($token)) {
            $key = substr($token, 0, 13);

            // $csrf_token = session($name);
            $value = frameCacheGet('default', $key);
            $origToken = $key . $value;
            if ($origToken === $token) {
                //验证通过

                // 防止重复提交
                // Cache::delete($key);
            } else {
                // 验证不通过
                return sendRespCode200('100030');
                // 
                return json([
                    'code' => 422,
                    'msg' => '表单token错误',
                    'data' => "",
                ]);
            }
        }
        return $next($request);
    }
}
