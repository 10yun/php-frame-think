<?php

declare(strict_types=1);

namespace shiyun\middleware;

/**
 * 路由-自动验证表单
 */
class CheckFormMiddle
{
    public function handle($request, \Closure $next)
    {
        //获取当前参数
        $params = $request->param();
        // 获取访问控制器和方法
        $method = $request->rule()->getName();
        //通过字符串分割，获取到具体的类文件和操作的方法名称

        if (empty($method)) {
            return $next($request);
        }

        if (strpos($method, '@') !== false) {
            $conName = substr($method, 0, strpos($method, '@'));
            $actionName = substr($method, strpos($method, '@') + 1);
        } else {
            $conName = substr($method, 0, strpos($method, '/'));
            $actionName = substr($method, strpos($method, '/') + 1);
        }
        /**
         * 获取应用名字
         */
        $modNameStr = app('http')->getName();
        $modNamespace = $modNameStr;
        /**
         * 获取类名字
         */
        $conNameStr = request()->controller();
        /**
         * 意图名字
         */
        $actNameStr = request()->action();

        //拼接验证类名，注意路径不要出错

        if (str_contains($conName, '.')) {
            $conName = str_replace(".", "\\", $conName);
        }
        if (str_contains($method, 'app\\')  || str_contains($method, 'addons\\')) {
            // 如果是全命名空间
            $conName = str_replace("controller", "validate", $conName);
            $checkClassName = "{$conName}Validate";
        } else {
            $checkClassName = "app\\{$modNamespace}\\validate\\{$conName}Validate";
        }

        // $pathInfo = $request->pathinfo();
        // dd($request);

        // dd($appName, $params, $method, $conName, $checkClassName, $actionName);
        // dd($actionName, $conName, $checkClassName);

        // $params = array_filter($params);
        //判断当前验证类是否存在
        if (class_exists($checkClassName)) {
            $checkClassObj = new $checkClassName;
            //仅当存在验证场景才校验
            if ($checkClassObj->hasScene($actionName)) {
                //设置当前验证场景
                $checkClassObj->scene($actionName);
                if (!$checkClassObj->check($params)) {
                    //校验不通过则直接返回错误信息
                    return sendRespError($checkClassObj->getError());
                }
            }
        }

        return $next($request);
    }
}
