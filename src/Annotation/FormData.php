<?php
declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class FormData extends Param
{
    public ?string $in = 'formData';
    public ?string $scene = '';
}
