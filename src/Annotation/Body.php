<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * 请求体参数注解（Request Body）
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Body extends Param
{
    public ?string $in = 'body';
    public ?string $name = 'body';
    public ?string $description = '请求体';
    public bool $security = true;
}
