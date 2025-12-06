<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * 请求头参数注解（Header Parameter）
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Header extends Param
{
    public ?string $in = 'header';
}
