<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * 路径参数注解（Path Parameter）
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Path extends Param
{
    public ?string $in = 'path';
    public bool $userOpen = false;
}
