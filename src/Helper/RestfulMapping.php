<?php

declare(strict_types=1);

namespace HPlus\Route\Helper;

/**
 * RESTful 方法映射规则
 * 
 * 定义标准的 RESTful API 方法名到 HTTP 方法和路径的映射
 * RouteCollector 和 DispatcherFactory 共用此配置
 */
final class RestfulMapping
{
    /**
     * 标准 RESTful 映射规则
     * 
     * 格式: 方法名 => [HTTP方法, 路径模板]
     */
    public const MAPPING = [
        // 列表操作
        'index' => ['GET', ''],
        'list' => ['GET', ''],
        
        // 详情操作
        'show' => ['GET', '/{id}'],
        'detail' => ['GET', '/{id}'],
        'get' => ['GET', '/{id}'],
        
        // 创建操作
        'create' => ['POST', ''],
        'store' => ['POST', ''],
        'add' => ['POST', ''],
        
        // 更新操作
        'update' => ['PUT', '/{id}'],
        'edit' => ['PUT', '/{id}'],
        'modify' => ['PUT', '/{id}'],
        'patch' => ['PATCH', '/{id}'],
        
        // 删除操作
        'delete' => ['DELETE', '/{id}'],
        'destroy' => ['DELETE', '/{id}'],
        'remove' => ['DELETE', '/{id}'],
    ];

    /**
     * 检查方法名是否为标准 RESTful 方法
     */
    public static function isRestfulMethod(string $methodName): bool
    {
        return isset(self::MAPPING[$methodName]);
    }

    /**
     * 获取 RESTful 路径模板
     * 
     * @return array{0: string, 1: string}|null [HTTP方法, 路径模板] 或 null
     */
    public static function getMapping(string $methodName): ?array
    {
        return self::MAPPING[$methodName] ?? null;
    }
}
