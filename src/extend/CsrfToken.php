<?php

namespace shiyun\extend;

class CsrfToken
{
    /*
     *得到form token
     *
     *@return \think\Response
     * */
    public function getCsrfToken()
    {
        $type = "sha1";
        $type  = is_callable($type) ? $type : 'md5';
        $token = call_user_func($type, request()->server('REQUEST_TIME_FLOAT'));
        $key = uniqid();
        frameCacheSet('file', $key, $token, 300);
        // return '123';
        return json([
            'code' => 0,
            'msg' => "success",
            'data' => $key . $token,
        ]);
    }
}
