<?php

declare(strict_types=1);

namespace shiyun\validate\annotation;

use shiyun\annotation\AnnotationAbstract;
use Attribute;

/**
 * 表单验证
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class ValidateForm extends AnnotationAbstract
{
    protected array $attrMust = ['validate', 'scene'];
    /**
     * @param string|object $validate 验证器
     * @param string $scene 验证场景
     * @param bool $batch 统一验证：true=是，flase=不是
     */
    public function __construct(
        public string|object $validate,
        public string $scene = '',
        public bool $batch = true,
    ) {
        $this->paresArgs(func_get_args(), 'validate');
    }
}
