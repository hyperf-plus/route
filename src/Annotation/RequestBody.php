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
class RequestBody extends AbstractAnnotation
{
    public function __construct(
        public string $description = '',
        public bool $required = true,
        public array $content = [],
        public array $examples = [],
        public ?string $ref = null,
    ) {}
} 