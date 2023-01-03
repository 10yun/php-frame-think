<?php

declare(strict_types=1);

namespace shiyun\route;

use shiyun\annotation\IntfAnnotationLoad;

use shiyun\route\annotation\{
    RouteFlag,
    RouteGroup,
    RouteRestful,
    RouteGet,
    RoutePost,
    RoutePut,
    RoutePatch,
    RouteDelete,
    RouteRule,
    RouteMiddleware,
};
use shiyun\route\RouteAnnotationHandle;
use shiyun\annotation\AnnotationParse;
use shiyun\validate\annotation\Validate;
use shiyun\validate\ValidateAnnotationHandle;

/**
 * 路由注解加载
 */
class RouteAttriLoad implements IntfAnnotationLoad
{
    public static function loader(): void
    {
        // 接口标识注解
        AnnotationParse::addHandle(RouteFlag::class, RouteAnnotationHandle::class);
        // 路由分组
        AnnotationParse::addHandle(RouteGroup::class, RouteAnnotationHandle::class);
        // rest路由
        AnnotationParse::addHandle(RouteRestful::class, RouteAnnotationHandle::class);
        // 路由-GET
        AnnotationParse::addHandle(RouteGet::class, RouteAnnotationHandle::class);
        // 路由-POST
        AnnotationParse::addHandle(RoutePost::class, RouteAnnotationHandle::class);
        // 路由-PUT
        AnnotationParse::addHandle(RoutePut::class, RouteAnnotationHandle::class);
        // 路由-PATCH
        AnnotationParse::addHandle(RoutePatch::class, RouteAnnotationHandle::class);
        // 路由-DELETE
        AnnotationParse::addHandle(RouteDelete::class, RouteAnnotationHandle::class);
        // 路由-所有Methods
        AnnotationParse::addHandle(RouteRule::class, RouteAnnotationHandle::class);
        // 中间件注解
        AnnotationParse::addHandle(RouteMiddleware::class, RouteAnnotationHandle::class);
        // 验证器注解
        AnnotationParse::addHandle(Validate::class, RouteAnnotationHandle::class);
    }

    public static function register($routeObj): void
    {
        RouteAnnotationHandle::createRoute($routeObj);
    }
    public static function debug($routeObj): void
    {
        $routeList = $routeObj->getRuleList();
        $htmlTableBody = '';
        foreach ($routeList as $item) {
            $item['route'] = $item['route'] instanceof \Closure ? '<Closure>' : htmlentities($item['route']);
            $item['rule'] = empty($item['rule']) ? '-' : htmlentities($item['rule']);
            // $str_option = "-";
            // if (!empty($item['option'])) {
            //     $str_option = "";
            //     if (is_array($item['option'])) {
            //         foreach ($item['option'] as $opt_key => $opt_val) {
            //             $str_option .= '"' . $opt_key . '":"' . $opt_val . '"';
            //         }
            //     }
            // }
            $str_option =  empty($item['option']) ? '' : json_encode($item['option']);
            $json_pattern = empty($item['pattern']) ? '-' : json_encode($item['pattern']);
            $htmlTableBody .= <<<EOF
<tr>
    <td rowspan="2">{$item['rule']}</td>
    <td>{$item['route']}</td>
    <td rowspan="2">{$item['method']}</td>
    <td rowspan="2">{$item['domain']}</td>
    <td rowspan="2">{$str_option}</td>
    <td rowspan="2">{$json_pattern}</td>
</tr>
<tr>
    <td>{$item['name']}</td>
</tr>
EOF;

            // EOF 独立一行
        }

        $htmlTableAll = <<<EOF
<style>
body {
   
}
.debug-box{
    background: #000;
    color: #56DB3A;  
    display: flex;
    flex-direction: column-reverse;
    justify-content: flex-end;
    margin: auto;
    padding: 15px;
    word-wrap: break-word;
    font-family: Helvetica, Arial, sans-serif;
    font-size: 14px;
    line-height: 1.4;
}
table.debug-table{
  border: 1px solid #629755;
  border-collapse: collapse;
  color: #56DB3A;
},
table.debug-table td,
table.debug-table th {
  border: 1px solid #629755;
  border-collapse: collapse;
  color: #56DB3A;
}

table.debug-table td {
  padding: 2px 5px;
  border: 1px solid #629755;
  color: #56DB3A;
}
table.debug-table tbody td {
  padding: 5px 5px;
}
</style>
<pre class="debug-box">
<table class="debug-table">
    <thead>
        <tr>
            <td rowspan="2" align="left">Rule</td>
            <td>Route</td>
            <td rowspan="3">Method</td>
            <td rowspan="3">Domain</td>
            <td rowspan="3">Option</td>
            <td rowspan="3">Pattern</td>
        </tr>
        <tr>
            <td>Name</td>
        </tr>
    </thead>
    <tbody>{$htmlTableBody}</tbody>
</table>
</pre>
EOF;

        // EOF 独立一行
        echo $htmlTableAll;
    }
}
