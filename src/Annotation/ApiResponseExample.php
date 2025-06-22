<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 增强的API响应示例注解 - 支持OpenAPI 3.1.1完整规范
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ApiResponseExample extends AbstractAnnotation
{
    // 基本信息
    public ?int $code = null;
    public ?string $description = null;
    public ?string $mediaType = 'application/json';
    
    // 示例相关 (OpenAPI 3.1+ 增强)
    public mixed $example = null;
    public ?array $examples = null;
    
    // Schema 定义 (支持 JSON Schema 2020-12)
    public ?array $schema = null;
    public ?string $schemaRef = null; // $ref
    
    // 响应头
    public ?array $headers = null;
    
    // 链接 (OpenAPI 3.1+ 新增)
    public ?array $links = null;
    
    // 扩展字段
    public ?array $extensions = null;

    public function __construct(
        int $code = 200,
        string $description = 'Success',
        string $mediaType = 'application/json',
        mixed $example = null,
        ?array $examples = null,
        ?array $schema = null,
        ?string $schemaRef = null,
        ?array $headers = null,
        ?array $links = null,
        ?array $extensions = null
    ) {
        $this->code = $code;
        $this->description = $description;
        $this->mediaType = $mediaType;
        $this->example = $example;
        $this->examples = $examples;
        $this->schema = $schema;
        $this->schemaRef = $schemaRef;
        $this->headers = $headers;
        $this->links = $links;
        $this->extensions = $extensions;
    }
} 