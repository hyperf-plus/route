<?php

declare(strict_types=1);

namespace HPlus\Route\Utils;

/**
 * 路由工具类
 * 提供驼峰转换、复数化、RESTful路由映射等功能
 */
class RouteHelper
{
    /**
     * 核心RESTful映射规则（只保留标准的5个操作）
     */
    public static array $restfulMapping = [
        'index' => ['GET', ''],      // 列表
        'show' => ['GET', '/{id}'],  // 详情
        'store' => ['POST', ''],     // 创建
        'update' => ['PUT', '/{id}'], // 更新
        'destroy' => ['DELETE', '/{id}'], // 删除
    ];

    /**
     * 驼峰转中划线
     * currentUser -> current-user
     * getUserInfo -> get-user-info
     */
    public static function camelToKebab(string $str): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $str));
    }

    /**
     * 单词复数化（简单规则）
     * user -> users
     * category -> categories
     */
    public static function pluralize(string $word): string
    {
        // 特殊情况
        $irregular = [
            'child' => 'children',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'tooth' => 'teeth',
            'foot' => 'feet',
            'mouse' => 'mice',
            'goose' => 'geese',
        ];
        
        $lowerWord = strtolower($word);
        
        if (isset($irregular[$lowerWord])) {
            return $irregular[$lowerWord];
        }
        
        // 已经是复数形式
        if (str_ends_with($word, 's') || str_ends_with($word, 'es')) {
            return $word;
        }
        
        // 以y结尾（前面是辅音）
        if (str_ends_with($word, 'y') && !in_array(substr($word, -2, 1), ['a', 'e', 'i', 'o', 'u'])) {
            return substr($word, 0, -1) . 'ies';
        }
        
        // 以s, x, z, ch, sh结尾
        if (str_ends_with($word, 's') || str_ends_with($word, 'x') || 
            str_ends_with($word, 'z') || str_ends_with($word, 'ch') || 
            str_ends_with($word, 'sh')) {
            return $word . 'es';
        }
        
        // 默认加s
        return $word . 's';
    }

    /**
     * 获取RESTful路径模板（基于方法参数智能生成）
     */
    public static function getRestfulPath(string $methodName, string $httpMethod, ?\ReflectionMethod $method = null): ?string
    {
        // 1. 检查标准RESTful映射
        if (isset(self::$restfulMapping[$methodName])) {
            [$expectedMethod, $pathTemplate] = self::$restfulMapping[$methodName];
            
            if (strtoupper($httpMethod) === $expectedMethod) {
                return $pathTemplate;
            }
        }
        
        // 2. 智能生成基于参数的路径
        if ($method !== null) {
            $smartPath = self::generateSmartPathFromParams($methodName, $method);
            if ($smartPath !== null) {
                return $smartPath;
            }
        }
        
        // 3. 自动转换（默认规则）
        return '/' . self::camelToKebab($methodName);
    }

    /**
     * 基于方法参数智能生成路径
     * 
     * 规则：
     * - function method($id) → /{id:\d+}/method
     * - function method($name) → /{name}/method  
     * - function method($id, $xxxx) → /{id:\d+}/{xxxx}/method
     * - function method($userId, $postId) → /{userId:\d+}/{postId:\d+}/method
     */
    public static function generateSmartPathFromParams(string $methodName, \ReflectionMethod $method): ?string
    {
        $parameters = $method->getParameters();
        $pathParams = [];
        
        // 提取所有简单类型参数作为路径参数
        foreach ($parameters as $param) {
            if (self::isPathParameter($param)) {
                $paramName = $param->getName();
                $paramType = $param->getType();
                
                // 根据参数类型添加正则约束
                if ($paramType instanceof \ReflectionNamedType) {
                    $typeName = $paramType->getName();
                    if ($typeName === 'int') {
                        $pathParams[] = $paramName . ':\d+';
                    } else {
                        $pathParams[] = $paramName;
                    }
                } else {
                    $pathParams[] = $paramName;
                }
            }
        }
        
        // 没有路径参数，使用普通路径
        if (empty($pathParams)) {
            return null;
        }
        
        // 有路径参数，生成 /{param1}/{param2}/method 格式
        $pathSegments = array_map(fn($param) => '{' . $param . '}', $pathParams);
        $pathSegments[] = self::camelToKebab($methodName);
        
        return '/' . implode('/', $pathSegments);
    }

    /**
     * 判断是否为路径参数
     */
    private static function isPathParameter(\ReflectionParameter $param): bool
    {
        $type = $param->getType();
        
        // 无类型或非内置类型，不作为路径参数
        if (!$type || !$type instanceof \ReflectionNamedType || !$type->isBuiltin()) {
            return false;
        }
        
        $typeName = $type->getName();
        
        // 只有 int 和 string 作为路径参数
        return in_array($typeName, ['int', 'string']);
    }

    /**
     * 规范化路径
     */
    public static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * 检查是否为RESTful标准方法
     */
    public static function isRestfulMethod(string $methodName): bool
    {
        return isset(self::$restfulMapping[$methodName]);
    }

    /**
     * 生成RESTful风格的控制器前缀
     * UserController -> /api/users
     * UserDetailController -> /api/user-details
     */
    public static function generateRestfulPrefix(string $className): string
    {
        $classPath = str_replace('\\', '/', $className);
        
        // 提取控制器部分
        if (str_contains($classPath, '/Controller/')) {
            $controllerPart = substr($classPath, strpos($classPath, '/Controller/') + 12);
        } else {
            $controllerPart = substr($className, strrpos($className, '\\') + 1);
        }
        
        // 移除Controller后缀
        if (str_ends_with($controllerPart, 'Controller')) {
            $controllerPart = substr($controllerPart, 0, -10);
        }
        
        // 处理路径部分（保留版本号等）
        $parts = explode('/', $controllerPart);
        $resourceName = array_pop($parts); // 最后一部分是资源名
        
        // 转换资源名为RESTful风格（驼峰转中划线+复数）
        $resourceName = self::camelToKebab($resourceName);
        $resourceName = self::pluralize($resourceName);
        
        // 组装完整路径
        $prefix = '/';
        if (!empty($parts)) {
            $prefix .= '/' . implode('/', array_map('strtolower', $parts));
        }
        $prefix .= '/' . $resourceName;
        
        return self::normalizePath($prefix);
    }

    /**
     * 计算路由优先级（用于排序）
     * 静态路由优先级高于动态路由
     */
    public static function getRoutePriority(string $path): int
    {
        // 静态路由（无参数）优先级最高
        if (!str_contains($path, '{')) {
            return 1000;
        }
        
        // 动态路由按参数数量排序，参数越少优先级越高
        $paramCount = substr_count($path, '{');
        return 1000 - $paramCount * 100;
    }

    /**
     * 检查路由是否为静态路由
     */
    public static function isStaticRoute(string $path): bool
    {
        return !str_contains($path, '{');
    }

    /**
     * 检查路由是否为动态路由
     */
    public static function isDynamicRoute(string $path): bool
    {
        return str_contains($path, '{');
    }
} 