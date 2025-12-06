<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 参数注解基类
 * 
 * 用于定义 API 参数的各种属性，支持 OpenAPI 规范
 */
abstract class Param extends AbstractAnnotation
{
    /**
     * @param string|null $key 字段key，格式："name" 或 "name|description"
     * @param string|null $in 参数位置：query, path, header, body, formData
     * @param string|null $name 字段名
     * @param string|null $description 字段描述
     * @param bool $required 是否必需
     * @param bool $security 是否进行安全验证
     * @param string|null $default 默认值
     * @param string|null $type 数据类型
     * @param bool $userOpen 是否对登录用户开放
     * @param array $enum 枚举值列表
     * @param string|null $rule 验证规则字符串
     * @param array|null $rules 验证规则数组
     * @param string|null $scene 验证场景
     * @param string|null $validate 验证器类名
     */
    public function __construct(
        public ?string $key = null,
        public ?string $in = null,
        public ?string $name = null,
        public ?string $description = null,
        public bool $required = false,
        public bool $security = false,
        public ?string $default = null,
        public ?string $type = null,
        public bool $userOpen = false,
        public array $enum = [],
        public ?string $rule = '',
        public ?array $rules = [],
        public ?string $scene = null,
        public ?string $validate = null
    ) {
        // PHP 8.0+ 构造函数属性提升会自动处理参数赋值
    }
}
