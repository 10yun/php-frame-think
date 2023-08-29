<?php


class DomainCross
{
    /**
     * 防止跨域,解决处理跨域问题
     *
     * @param array $MethodSett
     *            允许访问的方式，默认get，post
     * @param array $HeadersSett
     *            允许传递的 header 参数
     */
    protected function doCrossDomain($AllowHeaders = array(), $RequestHeaders = array(), $Method = array(), $CacheOpt = array())
    {
        $this->doAllowHeaders($AllowHeaders);
        $this->doAllowMethods($Method);
        $this->doRequestHeaders($RequestHeaders);
        /* ========== 清空缓存 ========== */
        header("Cache-Control:no-cache");
        // header ( 'Cache-Control: max-age=0' );
        header('X-Accel-Buffering:no'); // 关闭输出缓存
        header('Pragma:no-cache');
    }

    /**
     * 准许跨域请求来源访问
     *
     * @param array $diyOpt
     */
    protected function doAllowOrigin($diyOpt = array())
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : ''; // 跨域访问的时候才会存在此字段
        if (in_array($origin, $diyOpt)) {
            header('Access-Control-Allow-Origin:' . $origin);
        } else {
            header('Access-Control-Allow-Origin: * ');
        }
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 1800');
    }

    /**
     * 允许传递的 请求 参数
     *
     * @param array $diyOpt
     */
    protected function doRequestHeaders($diyOpt = array())
    {
        $allOpt = array_merge(array(
            'Origin',
            'X-Requested-With',
            'Content-Type',
            'Accept'
        ), $diyOpt);
        header('Access-Control-Request-Headers:' . implode(',', $allOpt));
    }

    /**
     * 允许传递的 header 参数
     *
     * @param array $diyOpt
     */
    protected function doAllowHeaders($diyOpt = array())
    {
        $allOpt = array_merge(array(
            'Origin',
            'X-Requested-With',
            'Content-Type',
            'Accept',
            'Authorization'
        ), $diyOpt);
        header('Access-Control-Allow-Headers:' . implode(',', $allOpt));
    }

    /**
     * 允许访问的方式
     *
     * @param array $diyOpt
     */
    protected function doAllowMethods($diyOpt = array())
    {
        $allOpt = array_merge(array(
            'GET',
            'POST'
            // 'PUT',
            // 'DELETE',
            // 'OPTIONS'
        ), $diyOpt);
        header('Access-Control-Allow-Methods:' . implode(',', $allOpt));
    }
}
