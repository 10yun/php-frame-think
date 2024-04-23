<?php

declare(strict_types=1);

namespace shiyun\annotation;

abstract class AnnotationAbstract implements IntfAnnotationItem
{
    /**
     * 必传属性
     */
    protected array $attrMust = [];
    /**
     * 解析需要的属性
     */
    protected array $attrNeed = ['methods'];
    /**
     * methods类型
     */
    protected string|array $methods = [];
    /**
     * 注解传入的参数
     */
    protected array $_arguments = [];

    /**
     * 参数名
     */
    protected array $_parameters = [];
    /**
     * 参数默认值
     */
    protected array $_defaultValues = [];
    /**
     * 解析参数
     * @access public
     * @param array $args
     * @param string $firstParameter 构造方法的第一个参数名
     * @return array
     */
    protected function paresArgs(array $args, string $firstParameter): array
    {
        // 解析参数
        $this->paresParameters();

        // 非注释解析传参
        if (isset($args[1])) {
            return $this->_arguments;
        }
        // 注释解析 不指定参数传参
        if (isset($args[0]['value'][1]) && is_array($args[0]['value']) && isset($this->_parameters[1])) {
            $data = [];
            foreach ($args[0]['value'] as $key => $value) {
                $data[$this->_parameters[$key]] = $value;
            }
            return $this->_arguments = $data;
        }

        // 注释解析 指定参数传参
        $args = $args[0] ?? [];
        if (isset($args['value'])) {
            $args[$firstParameter] = $args['value'];
            unset($args['value']);
        }
        if (is_array($args)) {
            $this->_arguments = $args;
        }

        return $this->_arguments;
    }

    /**
     * 解析参数
     * @access public
     * @return void
     */
    protected function paresParameters()
    {
        // 使用反射获取构造方法参数
        $parameters = (new \ReflectionObject($this))->getConstructor()->getParameters();
        // 获取参数名
        $this->_parameters = array_map(function ($param) {
            // 参数默认值
            if ($param->isDefaultValueAvailable()) {
                $this->_defaultValues[$param->getName()] = $param->getDefaultValue();
            }
            return $param->getName();
        }, $parameters);
    }

    protected function parseNeedParam(array $paramLast = []): array
    {
        // 增加需要的类属性，追加
        if (is_array($this->attrNeed) && count($this->attrNeed)) {
            foreach ($this->attrNeed as $key => $val) {
                if (empty($paramLast[$val])) {
                    $methods_name = strtoupper($val);
                    if (method_exists($this, "get{$methods_name}")) {
                        $paramLast[$val] = call_user_func([$this, "get{$methods_name}"]);
                    }
                }
            }
        }
        return $paramLast;
    }
    /**
     * 获取传入的参数
     * @return array
     */
    public function getArguments(): array
    {
        return $this->parseNeedParam($this->_arguments);
    }

    /**
     * 动态设置参数
     * @param array $args
     * @return static
     */
    public function setArguments(array $args): static
    {
        $this->_arguments = [];

        // 按序传参，不指定参数名
        if (isset($args[0])) {
            foreach ($args as $index => $value) {
                if (is_string($index)) {
                    $index = array_search($index, $this->_parameters);
                }
                $this->_arguments[$this->_parameters[$index]] = $value;
            }
        } else {
            $this->_arguments = $args;
        }

        return $this;
    }

    /**
     * 获取所有的参数
     * @access public
     * @return array
     */
    public function getParameters(): array
    {
        $params = [];
        foreach ($this->_parameters as $value) {
            $params[$value] = $this->_arguments[$value] ?? $this->_defaultValues[$value] ?? null;
        }
        return $this->parseNeedParam($params);
    }

    /**
     * 动态设置所有的参数
     * @param array $args
     * @return static
     */
    public function setParameters(array $args = []): static
    {
        foreach ($args as $key => $value) {
            $this->_parameters[$key] = $value;
        }
        return $this;
    }
    /**
     * @return array
     */
    public function getMethods(): string|array
    {
        $methods = [];
        if (is_string($this->methods)) {
            $methods[] = $this->methods;
        } else if (is_array($this->methods)) {
            $methods = $this->methods;
        }
        foreach ($methods as $key => $val) {
            $methods[$key] = strtoupper($val);
        }
        return $methods;
    }
}
