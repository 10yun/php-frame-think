<?php

declare(strict_types=1);

namespace shiyun\route;

use shiyun\bootstrap\AnnotationBootstrap;
use shiyun\annotation\IntfAnnotationHandle;

use shiyun\route\annotation\{
    RouteFlag,
    RouteGroup,
    RouteGet,
    RoutePost,
    RoutePut,
    RoutePatch,
    RouteDelete,
    RouteRule,
    RouteMiddleware,
};
use shiyun\validate\annotation\Validate;
use shiyun\validate\annotation\ValidateMiddleware;
use shiyun\validate\ValidateAnnotationHandle;

use shiyun\support\Route as frameRoute;
use think\route\Rule as RouteObject;

abstract class RouteAnnotationHandle implements IntfAnnotationHandle
{
    /**
     * api标识
     */
    protected static array $flags = [];
    /**
     * 控制器注解
     * @var array
     */
    protected static array $controllers = [];

    /**
     * 控制器中间件
     * @var array
     */
    protected static array $middlewares = [];

    /**
     * 保存的路由
     * @var array
     */
    protected static array $routes = [];
    protected static array $routesLast = [];

    /**
     * 处理路由注解
     * @access public
     * @param array $item
     * @return void
     */
    public static function handle(array $item = []): void
    {
        if ($item['type'] === 'class') {
            self::handleClassAnnotation($item, $item['class']);
        } else if ($item['type'] === 'method') {
            self::handleMethodAnnotation($item, $item['class']);
        }
    }

    /**
     * 处理类注解
     * @access public
     * @param array $item
     * @param string $className
     * @return void
     */
    public static function handleClassAnnotation(array $item, string $className)
    {
        $annotation = $item['annotation'];
        $parameters = $item['parameters'];

        switch ($annotation) {
                // 标识注解
            case RouteFlag::class:
                static::$flags[$className] ??= [];
                static::$flags[$className][] = $parameters;
                break;
                // 控制器注解
            case RouteGroup::class:
                static::$controllers[$className] ??= [];
                static::$controllers[$className][] = $parameters;
                break;
                // 控制器中间件注解
            case RouteMiddleware::class:
                static::$middlewares[$className] ??= [];
                static::$middlewares[$className][] = [
                    'middlewares' => (array)$parameters['middlewares'],
                    'only' => $parameters['only'],
                    'except' => $parameters['except'],
                ];
                break;
        }
    }

    /**
     * 处理方法注解
     * @access public
     * @param array $item
     * @param string $className
     * @return void
     */
    public static function handleMethodAnnotation(array $item, string $className)
    {
        $method = $item['method'];
        $annotation = $item['annotation'];
        $parameters = $item['parameters'];

        switch ($annotation) {
                // 路由注解
            case RouteRule::class:
                static::$routes[] = $item;
                break;
            case RouteGet::class:
                static::$routes[] = $item;
                break;
            case RoutePost::class:
                static::$routes[] = $item;
                break;
            case RoutePut::class:
                static::$routes[] = $item;
                break;
            case RoutePatch::class:
                static::$routes[] = $item;
                break;
            case RouteDelete::class:
                static::$routes[] = $item;
                break;
                // 方法中间件注解
            case RouteMiddleware::class:
                $middlewares = static::$middlewares[$className . ':' . $method] ??= [];
                static::$middlewares[$className . ':' . $method] = array_merge($middlewares, (array)$parameters['middlewares']);
                break;
        }
    }

    /**
     * 创建路由
     * @access public
     * @param bool $isClear 是否清除路由
     * @return void
     */
    public static function createRoute(bool $isClear = true)
    {
        $useDefaultMethod = AnnotationBootstrap::$config['route']['use_default_method'] ?? true;

        foreach (self::$routes as $item) {
            $parameters = $item['parameters'];

            // 未指定path参数且开启默认方法路由, 则使用方法名作为路由
            if (!isset($item['arguments']['path']) && $useDefaultMethod) {
                $parameters['path'] = $item['method'];
            }

            // 忽略控制器注解的path参数
            if (str_starts_with($parameters['path'], '/')) {
                // 添加路由
                self::addRoute($parameters['path'], $item);
                continue;
            }

            foreach (self::$controllers[$item['class']] ?? [['prefix' => '']] as $controller) {
                // 控制器注解的path参数
                $controllerPath = trim($controller['prefix'] ?? '', '/');
                // 路由注解的path参数
                $routePath = $parameters['path'];

                // 控制器注解的path参数不为空时，拼接 "/" 路径分隔符，如果path参数以 "[/" (可变参数) 开头，则不拼接
                $controllerPath = $controllerPath ? (str_starts_with($controllerPath, '[/') ? '' : '/') . $controllerPath : '';
                // 路由注解的path参数不为空时，拼接 "/" 路径分隔符，如果path参数以 "[/" (可变参数) 开头，则不拼接
                $routePath = $routePath ? (str_starts_with($routePath, '[/') ? '' : '/') . $routePath : '';

                // 添加路由
                self::addRoute($controllerPath . $routePath, $item);
            }
        }
        self::registerRoute();
        // $all_route = frameRoute::getRoutes();
        // var_dump($all_route);

        if ($isClear) {
            // 资源回收
            self::recovery();
        }
    }
    /**
     * 最终注册路由
     * @access public
     * @return void
     */
    protected static function registerRoute()
    {
        foreach (self::$routesLast as $item) {
            $parameters = $item['parameters'];
            // 添加路由
            $route = frameRoute::add($item['methods'], ($item['path'] ?: '/'), [$item['class'], $item['method']]);
            // 路由参数
            $parameters['params'] && $route->setParams($parameters['params']);
            // 路由名称
            $parameters['name'] && $route->name($parameters['name']);
            // 路由中间件
            self::addMiddleware($route, $item['class'], $item['method']);
        }
    }
    /**
     * 添加路由
     * @access public
     * @param string $path
     * @param array $item
     * @return void
     */
    protected static function addRoute(string $path, array $item)
    {
        $parameters = $item['parameters'];

        if (!empty($parameters['methods'])) {
            try {
                // 
                /**
                 * 新的，防止重复注册
                 * 如果是数组的话，解析单条
                 */
                if (is_array($parameters['methods'])) {
                    foreach ($parameters['methods'] as $val) {
                        $route_flag = $path . '_' . $val;
                        $route_flag_md5 = md5($route_flag);
                        if (empty(self::$routesLast[$route_flag_md5])) {
                            self::$routesLast[$route_flag_md5] = array_merge([
                                'route_flag' => $route_flag,
                                'route_flag_md5' => $route_flag_md5,
                                'methods' => $val,
                                'path' => $path
                            ], $item);
                        }
                    }
                } else if (is_string($parameters['methods'])) {
                    $route_flag = $path . '_' . $parameters['methods'];
                    $route_flag_md5 = md5($route_flag);
                    if (empty(self::$routesLast[$route_flag_md5])) {
                        self::$routesLast[$route_flag_md5] = array_merge([
                            'route_flag' => $route_flag,
                            'route_flag_md5' => $route_flag_md5,
                            'methods' => $parameters['methods'],
                            'path' => $path
                        ], $item);
                    }
                }

                /**
                 * 旧的
                 */
                // // 添加路由
                // $route = frameRoute::add($parameters['methods'], ($path ?: '/'), [$item['class'], $item['method']]);
                // // 路由参数
                // $parameters['params'] && $route->setParams($parameters['params']);
                // // 路由名称
                // $parameters['name'] && $route->name($parameters['name']);
                // // 路由中间件
                // self::addMiddleware($route, $item['class'], $item['method']);
            } catch (\Throwable $th) {
                echo "error: RouteAnnotationHandle->addRoute \r\n";
                echo "{$th->getMessage()}  \r\n";
                throw $th;
                return;
            }
        }
    }
    /**
     * 添加中间件
     * @param RouteObject $route
     * @param string $class
     * @param string $method
     * @return void
     */
    protected static function addMiddleware(RouteObject $route, string $class, string $method)
    {
        // 类中间件
        $classMiddlewares = self::$middlewares[$class] ?? [];
        // 方法中间件
        $methodMiddlewares = self::$middlewares[$class . ':' . $method] ?? [];

        // 添加类中间件
        foreach ($classMiddlewares as $item) {
            // 填写了only参数且不在only参数中则跳过
            if ($item['only'] && !in_array($method, self::toLowerArray($item['only']))) {
                continue;
            } // 填写了except参数且在except参数中则跳过
            else if ($item['except'] && in_array($method, self::toLowerArray($item['except']))) {
                continue;
            }
            $route->middleware($item['middlewares']);
        }

        // 添加方法中间件
        $route->middleware($methodMiddlewares);

        // 如果有验证器注解则添加验证器中间件
        if (ValidateAnnotationHandle::isExistValidate($class, $method)) {
            $route->middleware(ValidateMiddleware::class);
        }
    }

    protected static function toLowerArray(array $data)
    {
        return array_map(fn ($item) => strtolower($item), $data);
    }

    /**
     * 资源回收
     * @access public
     * @return void
     */
    protected static function recovery()
    {
        // 清空控制器注解
        self::$controllers = [];
        // 清空控制器中间件注解
        self::$middlewares = [];
    }
}
