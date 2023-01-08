<?php

/** @var array $traces */
if (!function_exists('parse_padding')) {
    function parse_padding($source)
    {
        $length  = strlen(strval(count($source['source']) + $source['first']));
        return 40 + ($length - 1) * 8;
    }
}
if (!function_exists('parse_exc_class')) {
    function parse_exc_class($name)
    {
        $names = explode('\\', $name);
        $nameCount = count($names) - 1;
        $htmlArr = [];
        foreach ($names as $key => $val) {
            if ($key == $nameCount) {
                $htmlArr[] = '<span class="debug-error-exc-title-primary">' . $val . '</span>';
            } else {
                $htmlArr[] = $val;
            }
        }
        return implode("\\", $htmlArr);
    }
}
if (!function_exists('parse_code_class')) {
    function parse_code_class($name)
    {
        $names = explode('/', $name);
        $htmlArr = [];
        foreach ($names as $key => $val) {
            $htmlArr[] = '<span class="delimiter">' . $val . '</span>';
        }
        return implode("/", $htmlArr);
    }
}
if (!function_exists('parse_class')) {
    function parse_class($name)
    {
        $names = explode('\\', $name);
        return '<abbr title="' . $name . '">' . end($names) . '</abbr>';
    }
}

if (!function_exists('parse_file')) {
    function parse_file($file, $line)
    {
        return '<a class="toggle" title="' . "{$file} line {$line}" . '">' . basename($file) . " line {$line}" . '</a>';
    }
}

if (!function_exists('parse_args')) {
    function parse_args($args)
    {
        $result = [];
        foreach ($args as $key => $item) {
            switch (true) {
                case is_object($item):
                    $value = sprintf('<em>object</em>(%s)', parse_class(get_class($item)));
                    break;
                case is_array($item):
                    if (count($item) > 3) {
                        $value = sprintf('[%s, ...]', parse_args(array_slice($item, 0, 3)));
                    } else {
                        $value = sprintf('[%s]', parse_args($item));
                    }
                    break;
                case is_string($item):
                    if (strlen($item) > 20) {
                        $value = sprintf(
                            '\'<a class="toggle" title="%s">%s...</a>\'',
                            htmlentities($item),
                            htmlentities(substr($item, 0, 20))
                        );
                    } else {
                        $value = sprintf("'%s'", htmlentities($item));
                    }
                    break;
                case is_int($item):
                case is_float($item):
                    $value = $item;
                    break;
                case is_null($item):
                    $value = '<em>null</em>';
                    break;
                case is_bool($item):
                    $value = '<em>' . ($item ? 'true' : 'false') . '</em>';
                    break;
                case is_resource($item):
                    $value = '<em>resource</em>';
                    break;
                default:
                    $value = htmlentities(str_replace("\n", '', var_export(strval($item), true)));
                    break;
            }

            $result[] = is_int($key) ? $value : "'{$key}' => {$value}";
        }

        return implode(', ', $result);
    }
}
if (!function_exists('echo_value')) {
    function echo_value($val)
    {
        if (is_array($val) || is_object($val)) {
            echo htmlentities(json_encode($val, JSON_PRETTY_PRINT));
        } elseif (is_bool($val)) {
            echo $val ? 'true' : 'false';
        } elseif (is_scalar($val)) {
            echo htmlentities($val);
        } else {
            echo 'Resource';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>系统发生错误</title>
    <meta name="robots" content="noindex,nofollow" />
    <style>
        /* Base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            color: #333;
            margin: 0;
            padding: 0 20px 20px;
            font: 16px Verdana, "Helvetica Neue", helvetica, Arial, 'Microsoft YaHei', sans-serif;
            font: 12px "Helvetica Neue", helvetica, arial, sans-serif;
        }

        h1 {
            margin: 10px 0 0;
            font-size: 28px;
            font-weight: 500;
            line-height: 32px;
        }

        h2 {
            color: #4288ce;
            font-weight: 400;
            padding: 6px 0;
            margin: 6px 0 0;
            font-size: 18px;
            border-bottom: 1px solid #eee;
        }

        h3 {
            margin: 12px;
            font-size: 16px;
            font-weight: bold;
        }

        abbr {
            cursor: help;
            text-decoration: underline;
            text-decoration-style: dotted;
        }

        a {
            color: #868686;
            cursor: pointer;
        }

        a:hover {
            text-decoration: underline;
        }

        .line-error {
            background: #f8cbcb;
            background: #5b5b5b;
        }

        .echo table {
            width: 100%;
        }

        .echo pre {
            padding: 16px;
            overflow: auto;
            font-size: 85%;
            line-height: 1.45;
            background-color: #f7f7f7;
            border: 0;
            border-radius: 3px;
            font-family: Consolas, "Liberation Mono", Menlo, Courier, monospace;
            background: #2d2d2d;
        }

        .echo pre>pre {
            padding: 0;
            margin: 0;
        }

        /* Exception Info */
        .exception {
            margin-top: 20px;
        }

        .exception .message {
            padding: 12px;
            line-height: 18px;
            font-size: 16px;
            font-family: Consolas, "Liberation Mono", Courier, Verdana, "微软雅黑", serif;
            /**diy */
            color: white;
            box-sizing: border-box;
            background-color: #2a2a2a;
            padding: 35px 40px;
            max-height: 180px;
            overflow: hidden;
            transition: 0.5s;
        }

        .exception .code {
            float: left;
            text-align: center;
            color: #fff;
            margin-right: 12px;
            padding: 16px;
            border-radius: 4px;
            background: #999;
        }

        .exception .source-code {
            overflow-x: auto;
        }

        .exception .source-code pre {
            margin: 0;
        }

        .exception .source-code pre ol {
            margin: 0;
            color: #4288ce;
            display: inline-block;
            min-width: 100%;
            box-sizing: border-box;
            font-size: 14px;
            font-family: "Century Gothic", Consolas, "Liberation Mono", Courier, Verdana, serif;
            padding-left: <?php echo (isset($source) && !empty($source)) ? parse_padding($source) : 40;  ?>px;
            /** diy */
            color: #a29d9d;
        }

        .exception .source-code pre li {
            border-left: 1px solid #ddd;
            height: 18px;
            line-height: 18px;
        }

        .exception .source-code pre code {
            color: #333;
            height: 100%;
            display: inline-block;
            border-left: 1px solid #fff;
            font-size: 14px;
            font-family: Consolas, "Liberation Mono", Courier, Verdana, "微软雅黑", serif;
            padding-left: 5px;
        }

        .exception .trace {
            /* padding: 6px; */
            line-height: 16px;
            font-size: 14px;
            font-family: Consolas, "Liberation Mono", Courier, Verdana, "微软雅黑", serif;
        }

        .exception .trace h2 {
            margin: 0;
            padding: 10px 15px;
            color: #333;
            font-size: 12px;
            font-weight: 400;
            border-bottom: 1px solid #eee;
        }

        .exception .trace h2:hover {
            text-decoration: underline;
            cursor: pointer;
        }


        /* Exception Variables */
        .exception-var table {
            width: 100%;
            margin: 12px 0;
            box-sizing: border-box;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .exception-var table caption {
            text-align: left;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 0;
        }

        .exception-var table caption small {
            font-weight: 300;
            display: inline-block;
            margin-left: 10px;
            color: #ccc;
        }

        .exception-var table tbody {
            font-size: 13px;
            font-family: Consolas, "Liberation Mono", Courier, "微软雅黑", serif;
        }

        .exception-var table td {
            padding: 0 6px;
            vertical-align: top;
            word-break: break-all;
        }

        .exception-var table td:first-child {
            width: 28%;
            font-weight: bold;
            white-space: nowrap;
        }

        .exception-var table td pre {
            margin: 0;
        }

        /* Copyright Info */
        .copyright {
            margin-top: 24px;
            padding: 12px 20px;
            border-top: 1px solid #eee;
            font-size: 16px;
        }

        /* SPAN elements with the classes below are added by prettyprint. */
        pre.prettyprint .pln {
            color: #000;
            color: #f08d49;
        }

        /* plain text */
        pre.prettyprint .str {
            color: #080;
            color: #7ec699;
        }

        /* string content */
        pre.prettyprint .kwd {
            color: #008;
            color: #cc99cd;
        }

        /* a keyword */
        pre.prettyprint .com {
            color: #800;
            color: #999;
        }

        /* a comment */
        pre.prettyprint .typ {
            color: #606;
            color: #f8c555;
        }

        /* a type name */
        pre.prettyprint .lit {
            color: #066
        }

        /* a literal value */
        /* punctuation, lisp open bracket, lisp close bracket */
        pre.prettyprint .pun,
        pre.prettyprint .opn,
        pre.prettyprint .clo {
            color: #660;
            color: #ccc;
        }

        pre.prettyprint .tag {
            color: #008
        }

        /* a markup tag name */
        pre.prettyprint .atn {
            color: #606
        }

        /* a markup attribute name */
        pre.prettyprint .atv {
            color: #080
        }

        /* a markup attribute value */
        pre.prettyprint .dec,
        pre.prettyprint .var {
            color: #606
        }

        /* a declaration; a variable name */
        pre.prettyprint .fun {
            color: red
        }

        /* a function name */

        .debug-error-left {
            overflow-y: scroll;
            height: 100%;
            position: fixed;
            margin: 0;
            left: 0;
            top: 0;
            width: 30%;
            background: #ded8d8;
        }

        .debug-error-right {
            overflow-y: scroll;
            height: 100%;
            position: fixed;
            margin: 0;
            left: 0;
            top: 0;
            left: 30%;
            width: 70%;
            background: #fafafa;
        }

        /**
         * 异常类型
         */
        .debug-error-exc-box {
            padding: 12px;
            line-height: 18px;
            font-size: 16px;
            /**diy */
            color: white;
            box-sizing: border-box;
            background-color: #2a2a2a;
            padding: 35px 40px;
            min-height: 180px;
            overflow: hidden;
            transition: 0.5s;
        }

        .debug-error-exc-title {
            font-family: Consolas, "Liberation Mono", Courier, Verdana, "微软雅黑", serif;
            color: #bebebe;
            font-size: 14px;
        }

        .debug-error-exc-title-primary {
            color: #e95353;
        }

        .debug-error-exc-message {
            font-size: 20px;
            word-wrap: break-word;
            margin: 10px 0 0 0;
            color: white;
        }

        .debug-error-exc-help {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            margin-top: 20px;
        }

        .debug-error-exc-help-item {
            color: #fff;
            margin-right: 12px;
        }

        .debug-error-exc-help-item svg {
            fill: #fff;
        }


        /**
         *
         */
        .debug-error-stack-box {
            /* padding: 6px; */
            line-height: 16px;
            font-size: 14px;
            font-family: Consolas, "Liberation Mono", Courier, Verdana, "微软雅黑", serif;
        }

        .debug-error-stack-top {
            margin: 0;
            padding: 10px 15px;
            color: #333;
            font-size: 12px;
            font-weight: 400;
            border-bottom: 1px solid #eee;
        }

        .debug-error-stack-top:hover {
            text-decoration: underline;
            cursor: pointer;
        }

        .debug-error-stack-list {
            margin: 5px;
        }

        .debug-error-stack-item {
            padding: 14px;
            cursor: pointer;
            transition: all 0.1s ease;
            background: #eeeeee;
        }

        .debug-error-stack-item.active {
            box-shadow: inset -5px 0 0 0 #4288ce;
            color: #4288CE;
            background: #ede2e2;
            border: 1px solid #4288CE !important;
        }

        .debug-error-stack-item:not(:last-child) {
            border-bottom: 1px solid rgba(0, 0, 0, .05);
        }

        .stack-item-index {
            font-size: 11px;
            color: #a29d9d;
            background-color: rgba(0, 0, 0, .05);
            height: 18px;
            width: 18px;
            line-height: 18px;
            border-radius: 5px;
            padding: 0 1px 0 1px;
            text-align: center;
            display: inline-block;
        }

        .stack-item-top-box {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            margin-bottom: 10px;
            display: inline-flex;
        }

        .stack-item-top-class {
            font-size: 14px;
        }

        .stack-item-top-func {
            font-size: 14px;
        }

        .stack-item-file {
            font-family: "Inconsolata", "Fira Mono", "Source Code Pro", Monaco, Consolas, "Lucida Console", monospace;
            color: #a29d9d;
            font-size: 12px;
        }

        /**
          * 代码
          */
        .debug-error-code-box {
            padding: 5px;
            background: #303030;
            display: none;
            display: block;
        }

        .debug-error-code__file {
            color: #a29d9d;
            padding: 12px 6px;
            border-bottom: none;
            font-family: "Inconsolata", "Fira Mono", "Source Code Pro", Monaco, Consolas, "Lucida Console", monospace;
        }

        .debug-error-code__file .delimiter {
            display: inline-block;
        }

        .debug-error-code__line {
            color: #7ec699;
        }

        @media (min-width: 1000px) {
            .debug-error-left {
                width: 32%;
            }

            .debug-error-right {
                left: 32%;
                width: 68%;
            }

            .debug-error-code-box {
                padding: 20px 40px;
            }

            .debug-error-vars-box {
                padding: 20px 40px;
            }
    </style>
</head>

<body>
    <?php if (\think\facade\App::isDebug()) { ?>
        <?php foreach ($traces as $index => $trace) { ?>
            <div class="exception">

                <div class="debug-error-left">
                    <?php include __DIR__ . '/exception_left_info.php'; ?>
                    <?php include __DIR__ . '/exception_left_stack.php'; ?>
                </div>
                <div class="debug-error-right">
                    <?php include __DIR__ . '/exception_right_code.php'; ?>
                    <?php include __DIR__ . '/exception_right_var.php'; ?>
                    <div class="copyright">
                        <a title="官方网站-开放平台" target="_blank" href="https://open.10yun.com/">十云-开放平台</a>
                        <span><?php echo syGetVersion(); ?></span>
                        <span>{ 科技改变未来，技术驱动世界 }</span>
                        <span>- <a title="官方手册" target="_blank" href="https://docs.10yun.com/php/">官方手册</a></span>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="exception">
            <div class="info">
                <h1><?php echo htmlentities($message); ?></h1>
            </div>
            <div class="copyright">
                <a title="官方网站-开放平台" target="_blank" href="https://open.10yun.com/">十云-开放平台</a>
                <span><?php echo syGetVersion(); ?></span>
                <span>{ 科技改变未来，技术驱动世界 }</span>
                <span>- <a title="官方手册" target="_blank" href="https://docs.10yun.com/php/">官方手册</a></span>
            </div>
        </div>
    <?php } ?>



    <?php if (\think\facade\App::isDebug()) { ?>
        <script>
            function $(selector, node) {
                var elements;

                node = node || document;
                if (document.querySelectorAll) {
                    elements = node.querySelectorAll(selector);
                } else {
                    switch (selector.substr(0, 1)) {
                        case '#':
                            elements = [node.getElementById(selector.substr(1))];
                            break;
                        case '.':
                            if (document.getElementsByClassName) {
                                elements = node.getElementsByClassName(selector.substr(1));
                            } else {
                                elements = get_elements_by_class(selector.substr(1), node);
                            }
                            break;
                        default:
                            elements = node.getElementsByTagName();
                    }
                }
                return elements;

                function get_elements_by_class(search_class, node, tag) {
                    var elements = [],
                        eles,
                        pattern = new RegExp('(^|\\s)' + search_class + '(\\s|$)');

                    node = node || document;
                    tag = tag || '*';

                    eles = node.getElementsByTagName(tag);
                    for (var i = 0; i < eles.length; i++) {
                        if (pattern.test(eles[i].className)) {
                            elements.push(eles[i])
                        }
                    }

                    return elements;
                }
            }

            $.getScript = function(src, func) {
                var script = document.createElement('script');

                script.async = 'async';
                script.src = src;
                script.onload = func || function() {};

                $('head')[0].appendChild(script);
            }

            ;
            (function() {
                var files = $('.toggle');
                var ol = $('ol', $('.prettyprint')[0]);
                var li = $('li', ol[0]);

                // 短路径和长路径变换
                for (var i = 0; i < files.length; i++) {
                    files[i].ondblclick = function() {
                        var title = this.title;

                        this.title = this.innerHTML;
                        this.innerHTML = title;
                    }
                }

                /* (function() {
                    var expand = function(dom, expand) {
                        var ol = $('ol', dom.parentNode)[0];
                        expand = undefined === expand ? dom.attributes['data-expand'].value === '0' : undefined;
                        if (expand) {
                            dom.attributes['data-expand'].value = '1';
                            ol.style.display = 'none';
                            dom.innerText = 'Call Stack (展开)';
                        } else {
                            dom.attributes['data-expand'].value = '0';
                            ol.style.display = 'block';
                            dom.innerText = 'Call Stack (折叠)';
                        }
                    };
                    var traces = $('.trace');
                    for (var i = 0; i < traces.length; i++) {
                        var h2 = $('h2', traces[i])[0];
                        expand(h2);
                        h2.onclick = function() {
                            expand(this);
                        };
                    }
                })(); */
                (function() {
                    var expand = function(dom, expand) {
                        var ol = $('div.debug-error-stack-list', dom.parentNode)[0];
                        expand = undefined === expand ? dom.attributes['data-expand'].value === '0' : undefined;
                        if (expand) {
                            dom.attributes['data-expand'].value = '1';
                            ol.style.display = 'none';
                            dom.innerText = 'Call Stack (展开)';
                        } else {
                            dom.attributes['data-expand'].value = '0';
                            ol.style.display = 'block';
                            dom.innerText = 'Call Stack (折叠)';
                        }
                    };
                    var traces = $('div.debug-error-stack-box');
                    for (var i = 0; i < traces.length; i++) {
                        var h2 = $('div.debug-error-stack-top', traces[i])[0];
                        expand(h2);
                        h2.onclick = function() {
                            expand(this);
                        };
                    }
                })();

                $.getScript('//cdn.bootcdn.net/ajax/libs/prettify/r298/prettify.min.js', function() {
                    prettyPrint();
                });
            })();
        </script>
    <?php } ?>
</body>

</html>