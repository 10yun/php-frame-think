<?php

declare(strict_types=1);

namespace shiyun\route\annotation;

use shiyun\annotation\AnnotationAbstract;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD
    | Attribute::TARGET_PARAMETER
    | Attribute::IS_REPEATABLE)]
class RouteInfo extends AnnotationAbstract
{
    protected array $attrMust = ['title'];
    /**
     * @param string $title 标题
     * @param string $desc  描述
     * @param string $tag   标签
     * @param string $version 版本
     * @param string $author 作者
     */
    public function __construct(
        public string $title = '',
        public string $desc = '',
        public string $tag = '',
        public string $version = '',
        public string $author = ''
    ) {
        // 解析参数
        $this->paresArgs(func_get_args(), 'title');
    }
}
