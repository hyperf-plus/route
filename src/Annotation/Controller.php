<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;
use Hyperf\HttpServer\Annotation\Controller as AnnotationController;

/**
 * 扩展控制器注解
 * 
 * 继承自 Hyperf\HttpServer\Annotation\Controller，扩展了以下功能：
 * - service: 所属服务（微服务预留）
 * - tag: OpenAPI 标签
 * - description: OpenAPI 描述
 * - userOpen: 是否只需登录即可访问
 * - security: 是否进行权限验证
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller extends AnnotationController
{
    /**
     * @param string $prefix 路由前缀
     * @param string $server 服务名称
     * @param array $options 路由选项
     * @param string|null $service 所属服务（微服务预留）
     * @param string|null $tag OpenAPI 标签
     * @param string|null $description OpenAPI 描述
     * @param bool $userOpen 控制器内是否全部只需登录就可以访问
     * @param bool $security 是否进行权限验证（false 则完全对外开放）
     * @param array $ignore 忽略的方法列表
     * @param array $generate 生成配置
     */
    public function __construct(
        public string  $prefix = '',
        public string  $server = 'http',
        public array   $options = [],
        public ?string $service = null,
        public ?string $tag = null,
        public ?string $description = null,
        public bool    $userOpen = false,
        public bool    $security = true,
        public array   $ignore = [],
        public array   $generate = []
    ) {
        // 调用父类构造函数
        parent::__construct($prefix, $server, $options);
    }
}