<?php
declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Query extends Param
{
    public ?string $in = 'query';
}
