<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 路由映射基类注解
 * 
 * 扩展自 Hyperf\Di\Annotation\AbstractAnnotation，提供统一的路由映射配置
 */
abstract class Mapping extends AbstractAnnotation
{
    /**
     * @param string|null $path 路由路径
     * @param string|null $summary OpenAPI 摘要
     * @param string|null $description OpenAPI 描述
     * @param bool $deprecated 是否已废弃
     * @param bool $security 是否验证用户权限
     * @param bool $userOpen 是否对登录用户开放
     * @param array $methods HTTP 方法列表
     * @param array $options 路由选项
     * @param string|null $name 路由名称
     * @param array $middleware 中间件列表
     */
    public function __construct(
        public ?string $path = null,
        public ?string $summary = null,
        public ?string $description = null,
        public bool $deprecated = false,
        public bool $security = true,
        public bool $userOpen = true,
        public array $methods = [],
        public array $options = [],
        public ?string $name = null,
        public array $middleware = []
    ) {
    }
}
