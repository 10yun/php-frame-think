<?php

declare(strict_types=1);

namespace shiyun\annotation;

use Closure;
use Throwable;
use Generator;
use SplFileInfo;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionAttribute;
use ReflectionException;
use ReflectionParameter;
use shiyun\support\Route as frameRoute;

abstract class AnnotationParse
{
    /**
     * 注解处理类
     * @var array
     */
    protected static array $handle = [];

    /**
     * 注解类结果集
     * @var array
     */
    protected static array $annotations = [];

    /**
     * 扫描注解类
     * @access public
     * @param array $include 扫描的路径
     * @param array $exclude 排除的路径
     * @return Generator
     */
    public static function scanAnnotations(array $include, array $exclude = [])
    {
        // 排除路径转正则表达式
        $regular = AnnotationUtil::excludeToRegular($exclude);
        $excludeRegular = $regular ? '/^(' . $regular . ')/' : '';

        $include_all = [];
        foreach ($include as $path) {
            // 2022-1128 增加支持*
            if (str_contains($path, "*")) {
                if (!str_starts_with($path, "/")) {
                    $path = AnnotationUtil::basePath(AnnotationUtil::replaceSeparator($path));
                }
                $path_arr = glob($path);
                foreach ($path_arr as $path_item) {
                    $include_all[] = $path_item;
                }
            } else {
                $include_all[] = $path;
            }
        }

        try {
            //code...
            foreach ($include_all as $path) {
                // 扫描绝对路径
                if (!str_starts_with($path, "/")) {
                    $path = AnnotationUtil::basePath(AnnotationUtil::replaceSeparator($path));
                }

                // 遍历获取文件 
                yield from AnnotationUtil::findDirectory($path, function (SplFileInfo $item) use ($excludeRegular) {
                    return $item->getExtension() === 'php' && !($excludeRegular && preg_match($excludeRegular, $item->getPathname()));
                });
            }
        } catch (\Throwable $th) {
            var_dump($th->getMessage());
            //throw $th;
        }
    }

    /**
     * 解析注解
     * @access public
     * @param Generator $generator
     * @return void
     * @throws ReflectionException
     */
    public static function parseAnnotations(Generator $generator): void
    {
        /** @var SplFileInfo $item */
        foreach ($generator as $item) {
            // 获取路径中的类名地址
            $pathname = $item->getPathname();

            $className = substr($pathname, strlen(AnnotationUtil::basePath()) + 0, -4);
            $className = str_replace('/', '\\', $className);

            try {
                if (!class_exists($className)) {
                    continue;
                }
                // 反射类
                $reflectionClass = new ReflectionClass($className);
            } catch (Throwable) {
                // throw new \Exception($className . ' no class_exists', 1);
                continue;
            }

            // 解析类的注解
            foreach (self::yieldParseClassAnnotations($reflectionClass) as $annotations) {
                // 遍历注解结果集
                foreach ($annotations as $item) {
                    // 注解类
                    $annotationClass = $item['annotation'];
                    // 调用注解处理类
                    if (isset(self::$handle[$annotationClass])) {
                        /** @var IntfAnnotationHandle $handle */
                        foreach (self::$handle[$annotationClass] as $handle) {
                            [$handle, 'handle']($item, $className);
                        }
                    }
                }
            }
        }
    }
    /**
     * 解析类注解 包括：类注解、属性注解、方法注解、方法参数注解，利用Generator提高性能
     * @access public
     * @param string|ReflectionClass $className
     * @return Generator
     * @throws ReflectionException
     */
    public static function yieldParseClassAnnotations(
        string|ReflectionClass $className
    ): Generator {
        $reflectionClass = is_string($className) ? new ReflectionClass($className) : $className;
        /**
         * 获取类的注解
         */
        yield from self::getClassAnnotations($reflectionClass);
        /**
         * TODO: 获取所有方法的注解
         * 等待删除
         */
        // foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
        //     $middlewares = '';
        //     $path        = "";
        //     $methods     = "";
        //     foreach ($reflectionMethod->getAttributes() as $kk => $attribute) {
        //         if ($attribute->getName() === Middleware::class) {
        //             $middlewares = $attribute->getArguments();
        //         }
        //         if ($attribute->getName() === Middlewares::class) {
        //             $middlewares = $attribute->getArguments();
        //         }
        //         if ($attribute->getName() === RequestMapping::class) {
        //             $path = $attribute->getArguments()["path"] ?? "";
        //             $methods = $attribute->newInstance()->setMethods();
        //         }
        //     }

        //     if (!empty($methods) and !empty($path)) {
        //         if (!empty($middlewares)) {
        //             frameRoute::add($methods, $path, [$class_name, $reflectionMethod->name])->middleware($middlewares);
        //         } else {
        //             frameRoute::add($methods, $path, [$class_name, $reflectionMethod->name]);
        //         }
        //     }
        // }
        /**
         * 获取方法注解
         */
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $method = self::getMethodAnnotations($reflectionMethod);
            $method && (yield from $method);
            // 获取方法参数的注解
            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameter = self::getMethodParameterAnnotations($reflectionMethod, $reflectionParameter);
                $parameter && (yield from $parameter);
            }
        }
        /**
         * 获取所有属性的注解
         */
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $property = self::getPropertyAnnotations($reflectionClass, $reflectionProperty);
            $property && (yield from $property);
        }
    }

    /**
     * 解析类注解 包括：类注解、属性注解、方法注解、方法参数注解
     * @access public
     * @param string|ReflectionClass $className
     * @return array
     * @throws ReflectionException
     */
    public static function parseClassAnnotations(string|ReflectionClass $className): array
    {
        $reflectionClass = is_string($className) ? new ReflectionClass($className) : $className;

        $methods = $properties = [];

        // 获取类的注解
        $class = self::getClassAnnotations($reflectionClass);
        // 获取所有方法的注解
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            // 获取方法注解
            $method = self::getMethodAnnotations($reflectionMethod);
            // 获取方法参数注解
            $parameters = [];
            // 获取方法参数的注解
            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameter = self::getMethodParameterAnnotations($reflectionMethod, $reflectionParameter);
                $parameter && ($parameters[$reflectionParameter->name] = $parameter);
            }
            // 跳过空数据
            if (empty($method) && empty($parameters)) {
                continue;
            }

            $methods[$reflectionMethod->name] = [
                // 方法注解
                'methods' => $method,
                // 方法参数注解
                'parameters' => $parameters,
            ];
        }
        // 获取所有属性的注解
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $property = self::getPropertyAnnotations($reflectionClass, $reflectionProperty);
            $property && ($properties[$reflectionProperty->name] = $property);
        }

        return ['class' => $class, 'method' => $methods, 'property' => $properties];
    }


    /**
     * 获取类注解
     * @access public
     * @param string|ReflectionClass $className
     * @param array|string $scanAnnotations
     * @return array
     * @throws ReflectionException
     */
    public static function getClassAnnotations(
        string|ReflectionClass $className,
        array|string $scanAnnotations = []
    ): array {
        $scanAnnotations = (array)$scanAnnotations;

        $reflection = is_string($className) ? new ReflectionClass($className) : $className;

        $annotations = self::cacheAnnotation($reflection->getName(), 'class', function () use ($reflection) {
            // 扫描PHP8原生注解
            $attributes = $reflection->getAttributes();

            //返回类中所有方法
            $function_arr  = [];
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                $function_arr[] = $reflectionMethod->getName();
            }

            return self::buildScanAnnotationItems([
                ...$attributes,
                // ...self::getPhp7('class', $reflection)
            ], [
                'type' => 'class',
                // 类名
                'class' => $reflection->name,
                'functions' => $function_arr,
            ]);
        });

        return self::filterScanAnnotations($annotations, $scanAnnotations);
    }

    /**
     * 获取类方法注解
     * @access public
     * @param string|ReflectionMethod $methodName
     * @param array|string $scanAnnotations
     * @return array
     * @throws ReflectionException
     */
    public static function getMethodAnnotations(
        string|ReflectionMethod $methodName,
        array|string $scanAnnotations = []
    ): array {
        $scanAnnotations = (array)$scanAnnotations;

        $reflectionMethod = is_string($methodName) ? new ReflectionMethod($methodName) : $methodName;
        // 类.方法名 标签
        $tag = 'method.' . $reflectionMethod->name;

        $annotations = self::cacheAnnotation($reflectionMethod->class, $tag, function () use ($reflectionMethod) {
            // 扫描PHP8原生注解
            $attributes = $reflectionMethod->getAttributes();

            return self::buildScanAnnotationItems([
                ...$attributes,
                // ...self::getPhp7('method', $reflectionMethod)
            ], [
                'type' => 'method',
                // 类名
                'class' => $reflectionMethod->class,
                // 方法名
                'method' => $reflectionMethod->name,
            ]);
        });

        return self::filterScanAnnotations($annotations, $scanAnnotations);
    }

    /**
     * 获取类方法注解
     * @access public
     * @param string|ReflectionClass $className
     * @param string|ReflectionProperty $propertyName
     * @param array|string $scanAnnotations
     * @return array
     * @throws ReflectionException
     */
    public static function getPropertyAnnotations(
        string|ReflectionClass $className,
        string|ReflectionProperty $propertyName,
        array|string $scanAnnotations = []
    ): array {
        $scanAnnotations = (array)$scanAnnotations;

        $reflectionClass = is_string($className) ? new ReflectionClass($className) : $className;
        $reflectionProperty = is_string($propertyName) ? new ReflectionProperty($reflectionClass, $propertyName) : $propertyName;
        // 类.属性名 标签
        $tag = 'property.' . $reflectionProperty->name;

        $annotations = self::cacheAnnotation($reflectionClass->name, $tag, function () use ($reflectionProperty) {
            // 扫描PHP8原生注解
            $attributes = $reflectionProperty->getAttributes();

            return self::buildScanAnnotationItems([
                ...$attributes,
                // ...self::getPhp7('property', $reflectionProperty)
            ], [
                'type' => 'property',
                // 类名
                'class' => $reflectionProperty->class,
                // 属性名
                'property' => $reflectionProperty->name,
            ]);
        });

        return self::filterScanAnnotations($annotations, $scanAnnotations);
    }

    /**
     * 获取方法参数注解
     * @access public
     * @param string|ReflectionMethod $methodName
     * @param string|ReflectionParameter $parameterName
     * @param array|string $scanAnnotations
     * @return array
     * @throws ReflectionException
     */
    public static function getMethodParameterAnnotations(
        string|ReflectionMethod $methodName,
        string|ReflectionParameter $parameterName,
        array|string $scanAnnotations = []
    ) {
        $scanAnnotations = (array)$scanAnnotations;
        $reflectionMethod = is_string($methodName) ? new ReflectionMethod($methodName) : $methodName;

        // 解析反射的参数
        $reflectionParameter = is_string($parameterName) ? new ReflectionParameter([
            // 类名
            $reflectionMethod->class,
            // 方法名
            $reflectionMethod->name,

        ], $parameterName) : $parameterName;

        $tag = 'parameter.' . $reflectionMethod->name . '.' . $reflectionParameter->name;

        $annotations = self::cacheAnnotation($reflectionMethod->class, $tag, function () use ($reflectionMethod, $reflectionParameter) {
            // 扫描PHP8原生注解
            $attributes = $reflectionParameter->getAttributes();

            return self::buildScanAnnotationItems($attributes, [
                'type' => 'parameter',
                // 类名
                'class' => $reflectionMethod->class,
                // 方法名
                'method' => $reflectionMethod->name,
                // 参数名
                'parameter_name' => $reflectionParameter->name,
            ]);
        });

        return self::filterScanAnnotations($annotations, $scanAnnotations);
    }

    /**
     * Build ScanAnnotationItems
     * @access public
     * @param array $attributes
     * @param array $parameters
     * @return array
     */
    protected static function buildScanAnnotationItems(array $attributes, array $parameters = [])
    {
        $annotations = [];

        foreach ($attributes as $attribute) {

            if ($attribute instanceof ReflectionAttribute) {
                // 获取注解类实例
                /** @var IntfAnnotationItem $annotation */
                $annotation = self::reflectionAttributeToAnnotation($attribute);
            } else {
                $annotation = $attribute;
            }

            if (!$annotation instanceof IntfAnnotationItem) {
                continue;
            }

            $annotations[$annotation::class][] = array_merge([
                // 注解参数类
                'annotation' => $annotation::class,
                // 注解传入的参数
                'arguments' => $annotation->getArguments(),
                // 注解所有的参数
                'parameters' => $annotation->getParameters(),
            ], $parameters);

            unset($annotation);
        }

        return $annotations;
    }

    /**
     * 注解解析缓存
     * @access public
     * @param string $className
     * @param string $tag
     * @param array|Closure|null $data
     * @return array|Closure|false|mixed
     */
    public static function cacheAnnotation(string $className, string $tag, array|Closure $data = null)
    {
        if (is_null($data)) {
            return self::$annotations[$className][$tag] ?? false;
        }

        if ($data instanceof Closure) {
            return self::$annotations[$className][$tag] ??= $data();
        }

        self::$annotations[$className][$tag] ??= [];
        return self::$annotations[$className][$tag] = $data;
    }

    /**
     * 获取指定的ScanAnnotations
     * @access public
     * @param array $annotations
     * @param array $scanAnnotations
     * @return array
     */
    protected static function filterScanAnnotations(array $annotations, array $scanAnnotations): array
    {
        return $scanAnnotations ? array_filter($annotations, fn ($key, $class) => in_array($class, $scanAnnotations)) : $annotations;
    }

    /**
     * 通过反射注解类获取注解类实例
     * @access public
     * @param ReflectionAttribute $attribute
     * @return mixed
     */
    protected static function reflectionAttributeToAnnotation(ReflectionAttribute $attribute)
    {
        $instance = $attribute->newInstance();
        return $instance->setArguments($attribute->getArguments());
    }

    /**
     * 获取注解处理类
     * @param string|null $annotation
     * @return array|string|null
     */
    public static function getHandle(string $annotation = null): array|string|null
    {
        return $annotation ? self::$handle[$annotation] ?? null : self::$handle;
    }

    /**
     * 添加注解处理类
     * @param string $annotationClass
     * @param string $handleClass
     * @return array
     */
    public static function addHandle(string $annotationClass, string $handleClass): array
    {
        self::$handle[$annotationClass] ??= [];
        self::$handle[$annotationClass][] = $handleClass;
        return self::$handle;
    }

    /**
     * 移除注解处理类
     * @param string $annotationClass
     * @param string|null $handleClass
     * @return array
     */
    public static function removeHandle(string $annotationClass, string $handleClass = null): array
    {
        if ($handleClass) {
            $key = array_search($handleClass, self::$handle[$annotationClass] ?? []);
            if ($key !== false) {
                unset(self::$handle[$annotationClass][$key]);
            }
        } else {
            unset(self::$handle[$annotationClass]);
        }

        return self::$handle;
    }

    /**
     * 
     * 
     *   是否需要支持php7
     * 
     *  如果需要支持，需要引入 "doctrine/annotations": "^1.13"
     * 
     * 
     */
    protected static bool $is_php7 = false;
    /**
     * 注释解析器
     */
    protected static $annotationReader = null;
    public static function getPhp7($type = '', $reflection = null)
    {
        if (self::$is_php7 !== true) {
            return [];
        }
        if ($type == 'class') {
            // 通过注释解析为注解
            $readerAttributes = self::getAnnotationReader()->getClassAnnotations($reflection);
        }
        if ($type == 'method') {
            // 通过注释解析为注解
            $readerAttributes = self::getAnnotationReader()->getMethodAnnotations($reflection);
        }
        if ($type == 'property') {
            // 通过注释解析为注解
            $readerAttributes = self::getAnnotationReader()->getPropertyAnnotations($reflection);
        }
        return [
            ...$readerAttributes
        ];
    }
    /**
     * 获取注释解析器
     * @access public
     * @return AnnotationReader|null
     */
    public static function getAnnotationReader()
    {
        if (is_null(self::$annotationReader)) {
            // self::$annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
        }
        return clone self::$annotationReader;
    }
}
