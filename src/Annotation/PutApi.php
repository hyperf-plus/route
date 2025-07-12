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
    public function __construct(
        ?string $path = null,
        ?string $summary = null,
        ?string $description = null,
        ?string $deprecated = null,
        bool $security = true,
        bool $userOpen = true,
        array $options = [],
        ?string $name = null,
        array $middleware = []
    ) {
        parent::__construct(
            path: $path,
            summary: $summary,
            description: $description,
            deprecated: $deprecated,
            security: $security,
            userOpen: $userOpen,
            methods: ['PUT'], // 显式设置HTTP方法
            options: $options,
            name: $name,
            middleware: $middleware
        );
    }
}
