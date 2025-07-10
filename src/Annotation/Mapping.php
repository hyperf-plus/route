<?php

namespace HPlus\Route\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

abstract class Mapping extends AbstractAnnotation
{
    /**
     * @param string|null $path 路径
     * @param string|null $summary 说明
     * @param string|null $description 介绍
     * @param string|null $deprecated 是否废弃
     * @param bool $security 是否验证用户权限
     * @param bool $userOpen 是否对登录用户开放
     * @param array $methods HTTP方法
     * @param array $options 选项
     * @param string|null $name 路由名称
     * @param array $middleware 中间件
     */
    public function __construct(
        public ?string $path = null,
        public ?string $summary = null,
        public ?string $description = null,
        public ?string $deprecated = null,
        public bool $security = true,
        public bool $userOpen = true,
        public array $methods = [],
        public array $options = [],
        public ?string $name = null,
        public array $middleware = []
    ) {
        // PHP 8.0+ 的属性提升功能会自动处理参数赋值
        // 这里不需要额外的逻辑
    }
}
