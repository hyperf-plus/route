<?php

declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * 后台管理控制器注解
 * 
 * 用于标记后台管理控制器，支持自动添加后台路由前缀和中间件
 * 配置项在 config('admin.route') 中定义
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AdminController extends Controller
{
}
