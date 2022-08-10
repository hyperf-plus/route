<?php

namespace HPlus\Route\Annotation;

use Attribute;
use Hyperf\HttpServer\Annotation\Controller as AnnotationController;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller extends AnnotationController
{
    /**
     * @param string $prefix
     * @param string|null $service 所属服务 预留，后面做微服务时用
     * @param array $options
     * @param string $server
     * @param array $ignore
     * @param array $generate
     * @param string|null $tag
     * @param string|null $description
     * @param bool $userOpen 控制器内是否全部只需登录就可以访问？
     * @param bool $security 如果选择否，则控制器内所有方法都不进行权限验证，直接完全对外开放
     */
    public function __construct(
        public string  $prefix = '',
        public string  $server = 'http',
        public array   $options = [],
        public array   $ignore = [],
        public array   $generate = [],
        public ?string $service = null,
        public ?string $tag = null,
        public ?string $description = null,
        public bool    $userOpen = false,
        public bool    $security = true
    )
    {
    }
}