<?php
declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ApiResponse extends AbstractAnnotation
{

    public function __construct(
        public int     $code = 500,
        public array   $schema = [],
        public ?string $description = null,
    )
    {
    }

}
