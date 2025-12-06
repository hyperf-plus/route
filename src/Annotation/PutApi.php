<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * PUT 请求路由注解
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PutApi extends Mapping
{
    public function __construct(
        ?string $path = null,
        ?string $summary = null,
        ?string $description = null,
        bool $deprecated = false,
        bool $security = true,
        bool $userOpen = true,
        array $options = [],
        ?string $name = null,
        array $middleware = []
    ) {
        parent::__construct($path, $summary, $description, $deprecated, $security, $userOpen, ['PUT'], $options, $name, $middleware);
    }
}
