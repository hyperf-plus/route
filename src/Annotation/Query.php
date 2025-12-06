<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * 查询参数注解（Query String）
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Query extends Param
{
    public ?string $in = 'query';
}
