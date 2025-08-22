<?php

namespace shiyun\docs\annotation;

use Attribute;

/**
 * 路由文档
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD
    | Attribute::TARGET_PARAMETER
    | Attribute::IS_REPEATABLE)]
class DocsTitle
{
    public function __construct(
        string $str = '', // 标题 
    ) {}

    // protected array $attrMust = ['title'];
    // /**
    //  * @param string $title 标题
    //  * @param string $param 参数
    //  */
    // public function __construct(
    //     public string $title = '',
    //     public string $param = '',
    // ) {
    //     // 解析参数
    //     // $this->paresArgs(func_get_args(), 'title');
    // }
}
