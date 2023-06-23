<?php

function get_class_from_file($path_to_file)
{
    //Grab the contents of the file
    $contents = file_get_contents($path_to_file);

    //Start with a blank namespace and class
    $namespace = $class = "";

    //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
    $getting_namespace = $getting_class = false;

    //Go through each token and evaluate it as necessary
    foreach (token_get_all($contents) as $token) {

        //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
        if (is_array($token) && $token[0] == T_NAMESPACE) {
            $getting_namespace = true;
        }

        //If this token is the class declaring, then flag that the next tokens will be the class name
        if (is_array($token) && $token[0] == T_CLASS) {
            $getting_class = true;
        }

        //While we're grabbing the namespace name...
        if ($getting_namespace === true) {

            //If the token is a string or the namespace separator...
            if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {

                //Append the token's value to the name of the namespace
                $namespace .= $token[1];
            } else if ($token === ';') {

                //If the token is the semicolon, then we're done with the namespace declaration
                $getting_namespace = false;
            }
        }

        //While we're grabbing the class name...
        if ($getting_class === true) {

            //If the token is a string, it's the name of the class
            if (is_array($token) && $token[0] == T_STRING) {

                //Store the token's value as the class name
                $class = $token[1];

                //Got what we need, stope here
                break;
            }
        }
    }
    //Build the fully-qualified class name and return it
    return $namespace ? $namespace . '\\' . $class : $class;
}

function get_namespace_class_form_file($classFilePath)
{
    // 替换为实际的类文件路径

    // 读取类文件内容
    $classContent = file_get_contents($classFilePath);

    // 使用正则表达式从文件内容中提取类名和命名空间
    $classNamePattern = '/class\s+(\w+)/';
    $namespacePattern = '/namespace\s+(.*?);/';

    $className = null;
    $namespace = null;

    if (preg_match($classNamePattern, $classContent, $matches)) {
        $className = $matches[1];
    }

    if (preg_match($namespacePattern, $classContent, $matches)) {
        $namespace = $matches[1];
    }
    return $namespace ? $namespace . '\\' . $className : $className;
}


function getMethodAnnotations($method)
{
    $reflection = new \ReflectionMethod($method);
    // 获取注释
    $annotations = [];
    foreach ($reflection->getDocComment() as $comment) {
        if (preg_match('/@[a-zA-Z]+/', $comment)) {
            $annotations[] = preg_replace('/^@([a-zA-Z]+)(.*)$/', '$1', $comment);
        }
    }

    // 将注释转换为数组格式
    $annotations = array_map(function ($annotation) {
        return json_decode($annotation, true);
    }, $annotations);

    // 返回方法名、参数和注释信息
    return [$reflection->name, $reflection->getParameters(), $annotations];
}
/**
 * 获取一个函数的依赖
 * @param  string|callable $func
 * @param  array  $param 调用方法时所需参数 形参名就是key值
 * @return array  返回方法调用所需依赖
 */
function getFucntionParameter($func, $param = [])
{
    if (!is_array($param)) {
        $param = [$param];
    }
    $ReflectionFunc = new \ReflectionFunction($func);
    $depend = array();
    foreach ($ReflectionFunc->getParameters() as $value) {
        if (isset($param[$value->name])) {
            $depend[] = $param[$value->name];
        } elseif ($value->isDefaultValueAvailable()) {
            $depend[] = $value->getDefaultValue();
        } else {
            $tmp = $value->getClass();
            if (is_null($tmp)) {
                throw new \Exception("Function parameters can not be getClass {$class}");
            }
            $depend[] = $this->get($tmp->getName());
        }
    }
    return $depend;
}
