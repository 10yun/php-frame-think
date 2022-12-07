<?php

namespace shiyun\middleware;

/**
 * 处理 JWT
 * 后台页面权限还是得通过 session 来处理
 * 单纯得 jwt 无法处理
 * 这里使用 jwt + token 方式 
 */
/**
 * jwt鉴权
 */
class SyAuthJwtMiddle
{
    public function handle($request, \Closure $next)
    {
        $tokenType = $request->header('syOpenAppKey');
        $tokenJwt = $request->header('Authorization');
        $method = 'bearer';
        // 去除token中可能存在的bearer标识
        $tokenJwt = trim(str_ireplace($method, '', $tokenJwt));

        if (!empty($tokenType)) {
            if (!empty($tokenJwt)) {
                $jwtauth = \app\common\lib\JwtAuth::getInstance();
                $jwtauth->setToken($tokenJwt);
                if ($jwtauth->validate() && $jwtauth->verify()) {
                    return $next($request);
                } else {
                    return sendRespError('jwt登录过期', '10002');
                }
            } else {
                //sendRespError ( 'jwt参数错误', '10003' );
            }
        }
        return $next($request);
    }
}
