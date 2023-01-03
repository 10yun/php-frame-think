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
    protected function doParseStr1($oldStr)
    {
        $last_str = "";
        if (is_array($oldStr)) {
            foreach ($oldStr as $key => $val) {
                if (is_bool($val)) {
                    $bool_val =  $val === false ? 'false' : 'true';
                    $last_str .= sprintf('<p>"%s" : "%s"</p>', $key, $bool_val);
                } else {
                    $last_str .= sprintf('<p>"%s" : "%s"</p>', $key, $val);
                }
            }
        }
        return $last_str;
    }
    protected function doParseStr2($oldStr)
    {
        $last_str = "";
        if (is_array($oldStr)) {
            foreach ($oldStr as $key => $val) {
                $last_str .=  sprintf('<p>%s</p>', $val);
            }
        }
        return $last_str;
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
            //    
            // }
            /** 处理中间件 */
            $middlewareStr =  '-';
            if (!empty($item['option']['middleware']) || array_key_exists('middleware', $item['option'])) {
                $middlewareStr = (new self())->doParseStr2($item['option']['middleware']);
                unset($item['option']['middleware']);
            }
            /** 处理追加 */
            $appendStr = '-';
            if (!empty($item['option']['append'])) {
                $appendStr = (new self())->doParseStr1($item['option']['append']);
                unset($item['option']['append']);
            }
            $optionStr = '-';
            $optionStr = (new self())->doParseStr1($item['option']);
            $patternStr = '-';
            $patternStr = (new self())->doParseStr1($item['pattern']);

            $htmlTableBody .= <<<EOF
<tr>
    <td>{$item['rule']}</td>
    <td>
        <div class="debug-text">
            <p>{$item['name']}</p>
            <p>{$item['route']}</p>
        </div>
    </td>
    <td>{$item['method']}</td>
    <td>{$item['domain']}</td>
    <td><div class="debug-text">{$optionStr}</div></td>
    <td><div class="debug-text">{$appendStr}</div></td>
    <td><div class="debug-text">{$middlewareStr}</div></td>
    <td><div class="debug-text" >{$patternStr}</div></td>
</tr>
EOF;

            // EOF 独立一行
        }

        $htmlTableAll = <<<EOF
<style>
* {
    box-sizing: border-box;
    font-size: 13px;
    margin: 0;
    padding: 0;
}
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 13px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
}
.debug-box{
    background: #000;
    color: #56DB3A;  
    margin: auto;
    padding: 15px;
    word-wrap: break-word;
}
.debug-text{
    word-wrap: break-word;
    padding: 2px;
}
.debug-text p{
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
<div class="debug-box">
<table class="debug-table">
    <thead>
        <tr>
            <td align="left">Rule</td>
            <td align="left">
                <p>Route</p>
                <p>Name</p>
            </td>
            <td>Method</td>
            <td>Domain</td>
            <td>Option</td>
            <td>Append</td>
            <td>Middleware</td>
            <td>Pattern</td>
        </tr>
    </thead>
    <tbody>{$htmlTableBody}</tbody>
</table>
</div>
EOF;

        // EOF 独立一行
        echo $htmlTableAll;
    }
}
