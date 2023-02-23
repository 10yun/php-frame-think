<?php

namespace shiyun\libs;

class Container
{
    protected $binds;

    protected $instances;

    /**
     * @param $abstract
     * @param $concrete
     * 把代词 绑定到容器里,为后续make
     */
    public function bind($abstract, $concrete)
    {
        if ($concrete instanceof \Closure) {
            $this->binds[$abstract] = $concrete;
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    /**
     * @param $abstract
     * @param array $parameters
     * @return mixed
     * 创建对象
     */
    public function make($abstract, $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        // 把容器对象$this,放到参数数组第一个元素。为call_user_func_array使用
        array_unshift($parameters, $this);

        // 这里$this->binds[$abstract] 绑定的闭包函数, 执行函数参数是$parameters
        return call_user_func_array($this->binds[$abstract], $parameters);
    }
}
