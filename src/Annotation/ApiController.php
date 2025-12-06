<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * API 控制器注解
 * 
 * 用于标记 API 控制器，支持自动路由注册和 OpenAPI 文档生成
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ApiController extends Controller
{
}
