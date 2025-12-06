<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * 表单数据参数注解（Form Data）
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class FormData extends Param
{
    public ?string $in = 'formData';
    public ?string $scene = '';
}
