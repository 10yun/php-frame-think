<?php

declare(strict_types=1);

namespace shiyun\middleware;

/**
 * https://www.cnblogs.com/luojie-/p/12963872.html
 */
class CheckFormMiddle
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        if ($request->isAjax()) {
            dd('--');
            //获取当前参数
            $params = $request->param();
            if (!empty($params)) {
                //获取url并转为数组
                $arr = $this->url_to_array($params);
                if (!$arr) {
                    return $next($request);
                }
                //检查url是否满足验证条件
                $newcontroller = explode('_', $arr[2]);
                if (count($newcontroller) <= 1) {
                    return $next($request);
                }
                //检查验证文件是否存在
                $validate = $this->exists_validate($newcontroller, $arr[1]);
                if (empty($validate)) {
                    return $next($request);
                }
                $results = $this->validate_data($arr[3], $validate, $params);
                if ($results) {
                    // return $this->error($results);
                }
            }
        }
        return $next($request);
    }

    /**
     * 验证数据
     * @param $action
     * @param $validate
     * @param $params
     * @return bool
     */
    public function validate_data($action, $validate, $params)
    {
        $scene = '';
        if (strstr($action, 'add')) {
            $scene = 'add';
        }
        if (strstr($action, 'edit')) {
            $scene = 'edit';
        }
        if (empty($scene)) {
            return false;
        }
        $v = new $validate;
        if ($v->hasScene($scene)) {
            //设置当前验证场景
            $v->scene($scene);
            if (!$v->check($params)) {
                //校验不通过则直接返回错误信息
                return $v->getError();
            } else {
                return false;
            }
        }
    }

    /**
     * 检查文件是否存在
     * @param null $validate
     * @return bool|string
     */
    public function exists_validate($validate, $model)
    {
        $mokuai = $model . "\\";
        $file = "app\\" . $mokuai . "validate\\" . $validate[0] . "\\" . ucfirst($validate[1]);
        if (class_exists($file)) {
            return $file;
        }
        return false;
    }

    /**
     * url 转数组
     * @param $data
     * @return array|null
     */
    public function url_to_array($data)
    {
        $arr = null;
        foreach ($data as $k => $v) {
            $arr = explode('/', $k);
            break;
        }
        return $arr;
    }
}
