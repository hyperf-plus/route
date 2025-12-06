<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * API 响应示例注解 - 支持 OpenAPI 3.1.1 完整规范
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ApiResponseExample extends AbstractAnnotation
{
    /**
     * @param int $code HTTP 状态码
     * @param string $description 响应描述
     * @param string $mediaType 媒体类型
     * @param mixed $example 示例值
     * @param array|null $examples 多个示例（OpenAPI 3.1+）
     * @param array|null $schema Schema 定义
     * @param string|null $schemaRef Schema 引用（$ref）
     * @param array|null $headers 响应头
     * @param array|null $links 链接（OpenAPI 3.1+）
     * @param array|null $extensions 扩展字段
     */
    public function __construct(
        public int $code = 200,
        public string $description = 'Success',
        public string $mediaType = 'application/json',
        public mixed $example = null,
        public ?array $examples = null,
        public ?array $schema = null,
        public ?string $schemaRef = null,
        public ?array $headers = null,
        public ?array $links = null,
        public ?array $extensions = null
    ) {
    }
} 