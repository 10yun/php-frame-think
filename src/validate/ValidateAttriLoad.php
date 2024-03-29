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
}
