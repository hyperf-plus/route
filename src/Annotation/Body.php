<?php
declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Body extends Param
{
    public ?string $in = 'body';
    public ?string $name = 'body';
    public ?string $description = 'body';
    public bool $security = true;

}
