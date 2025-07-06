<?php

namespace HPlus\Route\Annotation;


use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PutApi extends Mapping
{
    public array $methods = ['PUT'];
    
    public function __construct(
        ?string $path = null,
        ?string $summary = null,
        ?string $description = null,
        ?string $deprecated = null,
        bool $security = true,
        bool $userOpen = true,
        array $methods = ['PUT'],
        array $options = [],
        ?string $name = null,
        array $middleware = []
    ) {
        parent::__construct(
            $path,
            $summary,
            $description,
            $deprecated,
            $security,
            $userOpen,
            $methods,
            $options,
            $name,
            $middleware
        );
    }
}
