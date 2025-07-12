<?php

declare(strict_types=1);

namespace HPlus\Route;

use Hyperf\Di\Annotation\AnnotationCollector;
use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;
use HPlus\Route\Annotation\PatchApi;
use HPlus\Route\Annotation\Mapping;
use HPlus\Route\Utils\RouteHelper;
use HPlus\Validate\Annotations\RequestValidation;
use HPlus\Validate\RuleParser;
use ReflectionClass;
use ReflectionMethod;

/**
 * 高性能路由收集器 - RESTful增强版
 * 
 * 核心原则：
 * - 注解驱动：只有明确注解的方法才能访问（安全第一）
 * - RESTful规则：根据方法名和注解类型智能生成路径
 * - 用户优先：用户设置的prefix/path优先，没设置就自动生成
 * - 零配置：开箱即用，但可自定义
 * 
 * RESTful映射规则：
 * - GET + index/list → GET /resource
 * - GET + show/detail → GET /resource/{id}
 * - POST + create/store → POST /resource
 * - PUT/PATCH + update/edit → PUT /resource/{id}
 * - DELETE + delete/destroy → DELETE /resource/{id}
 * 
 * 性能优化：
 * - 单例模式：全局唯一实例
 * - 多层缓存：避免重复计算
 * - 懒加载：按需加载
 * - 索引优化：O(1)查找
 */
class RouteCollector
{
    /**
     * 单例实例
     */
    private static ?self $instance = null;

    /**
     * 路由缓存
     */
    private array $routeCache = [];

    /**
     * 控制器缓存
     */
    private array $controllerCache = [];

    /**
     * 反射类缓存
     */
    private array $reflectionCache = [];

    /**
     * 路由索引（用于快速查找）
     */
    private array $routeIndex = [];

    /**
     * 路由缓存开关
     */
    private bool $enableCache;





    /**
     * 私有构造函数（单例模式）
     */
    private function __construct() {}

    /**
     * 获取单例实例
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 收集所有API路由
     * 
     * @return array<string, array> 路由信息数组
     */
    public function collectRoutes(): array
    {
        // 缓存命中检查
        if (!empty($this->routeCache)) {
            return $this->routeCache;
        }

        $routes = [];
        
        // 批量获取所有ApiController注解的类
        $controllers = AnnotationCollector::getClassesByAnnotation(ApiController::class);
        
        // 批量处理控制器
        foreach ($controllers as $className => $controllerAnnotation) {
            $controllerRoutes = $this->getControllerRoutesWithCache($className, $controllerAnnotation);
            $routes = array_merge($routes, $controllerRoutes);
        }

        // 构建路由索引（提升查找性能）
        $this->buildRouteIndex($routes);
        
        // 缓存结果
        $this->routeCache = $routes;
        
        return $routes;
    }

    /**
     * 使用缓存获取控制器路由
     */
    private function getControllerRoutesWithCache(string $className, ApiController $controllerAnnotation): array
    {
        // 检查控制器缓存
        if (isset($this->controllerCache[$className])) {
            return $this->controllerCache[$className];
        }

        $reflectionClass = $this->getReflectionClassWithCache($className);
        $routes = $this->collectControllerRoutes($reflectionClass, $controllerAnnotation);
        
        // 缓存控制器路由
        $this->controllerCache[$className] = $routes;
        
        return $routes;
    }

    /**
     * 使用缓存获取反射类
     */
    private function getReflectionClassWithCache(string $className): ReflectionClass
    {
        if (isset($this->reflectionCache[$className])) {
            return $this->reflectionCache[$className];
        }
        
        $this->reflectionCache[$className] = new ReflectionClass($className);

        return $this->reflectionCache[$className];
    }

    /**
     * 收集单个控制器的路由
     */
    private function collectControllerRoutes(ReflectionClass $controller, ApiController $controllerAnnotation): array
    {
        $routes = [];
        $className = $controller->getName();
        
        // 获取控制器前缀（用户设置优先，否则自动生成）
        $controllerPrefix = $this->getControllerPrefix($className, $controllerAnnotation);
        
        // 批量获取所有公共方法
        $methods = $controller->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            // 跳过构造函数和魔术方法
            if ($method->isConstructor() || str_starts_with($method->getName(), '__')) {
                continue;
            }

            // 只处理有路由注解的方法（必须注解才能访问）
            $routeAnnotation = $this->getRouteAnnotation($method);
            if ($routeAnnotation) {
                $routeInfo = $this->buildRouteInfo(
                    $className,
                    $method,
                    $routeAnnotation,
                    $controllerAnnotation,
                    $controllerPrefix
                );
                
                if ($routeInfo) {
                    $routes[] = $routeInfo;
                }
            }
        }
        
        return $routes;
    }

    /**
     * 获取控制器前缀（用户设置优先）
     */
    private function getControllerPrefix(string $className, ApiController $controllerAnnotation): string
    {
        // 用户设置优先
        if (!empty($controllerAnnotation->prefix)) {
            return $controllerAnnotation->prefix;
        }

        // 自动生成RESTful风格前缀
        return RouteHelper::generateRestfulPrefix($className);
    }



    /**
     * 构建路由信息
     */
    private function buildRouteInfo(
        string $className,
        ReflectionMethod $method,
        Mapping $routeAnnotation,
        ApiController $controllerAnnotation,
        string $controllerPrefix
    ): array {
        $methodName = $method->getName();
        $httpMethod = $routeAnnotation->methods[0] ?? 'GET';
        
        // 获取路径（增强版：支持智能参数识别）
        $routePath = $this->getRoutePathEnhanced($methodName, $httpMethod, $routeAnnotation, $method);
        
        // 确保路径正确拼接（与 DispatcherFactory 保持一致）
        if ($routePath && !str_starts_with($routePath, '/')) {
            $routePath = '/' . $routePath;
        }
        
        $fullPath = RouteHelper::normalizePath($controllerPrefix . $routePath);
        
        $routeInfo = [
            'path' => $fullPath,
            'methods' => $routeAnnotation->methods,
            'controller' => $className,
            'action' => $methodName,
            'name' => "{$className}::{$methodName}",
            'middleware' => $routeAnnotation->options['middleware'] ?? [],
            'summary' => $routeAnnotation->summary ?? $this->generateSummary($methodName, $httpMethod),
            'description' => $routeAnnotation->description ?? '',
            'deprecated' => $routeAnnotation->deprecated ?? false,
            'tags' => $this->getTags($controllerAnnotation, $className),
            'security' => ($routeAnnotation->security ?? true) && ($controllerAnnotation->security ?? true),
            'userOpen' => ($routeAnnotation->userOpen ?? false) || ($controllerAnnotation->userOpen ?? false),
            'restful' => $this->isRestfulMethod($methodName), // 标记是否符合RESTful约定
            'smart_path' => !isset($routeAnnotation->path) && !$this->isRestfulMethod($methodName), // 标记是否为智能生成
        ];

        // 懒加载：只在需要时提取参数和请求体信息
        $routeInfo = $this->lazyLoadRouteDetails($routeInfo, $method);

        return $routeInfo;
    }

    /**
     * 获取路由路径（用户设置优先，否则RESTful规则）
     */
    private function getRoutePath(string $methodName, string $httpMethod, Mapping $routeAnnotation): string
    {
        // 用户设置优先
        if (isset($routeAnnotation->path)) {
            return $routeAnnotation->path;
        }

        // 使用工具类获取RESTful路径
        $pathTemplate = RouteHelper::getRestfulPath($methodName, $httpMethod);
        
        if ($pathTemplate !== null) {
            return $pathTemplate;
        }

        // 默认：方法名转路径（驼峰转中划线）
        return '/' . RouteHelper::camelToKebab($methodName);
    }

    /**
     * 获取路由路径（增强版：支持动态参数识别）
     */
    private function getRoutePathEnhanced(
        string $methodName, 
        string $httpMethod, 
        Mapping $routeAnnotation,
        ReflectionMethod $method
    ): string {
        // 用户设置优先
        if (isset($routeAnnotation->path)) {
            return $routeAnnotation->path;
        }

        // 使用工具类获取RESTful路径
        $pathTemplate = RouteHelper::getRestfulPath($methodName, $httpMethod);
        
        if ($pathTemplate !== null) {
            return $pathTemplate;
        }

        // 智能路径生成：基于方法参数
        return $this->generateSmartPath($methodName, $method);
    }

    /**
     * 智能生成路径（基于方法参数）
     * 
     * 规则：
     * 1. 识别路径参数（int/string类型的简单参数）
     * 2. 根据参数位置和方法名智能组合
     * 3. 支持多个参数的复杂路径
     * 
     * 示例：
     * - customAction($id) → /{id}/custom-action
     * - userPost($userId, $postId) → /{userId}/user-post/{postId}
     * - getByCode($code) → /by-code/{code}
     * - compareVersions($id, $v1, $v2) → /{id}/compare-versions/{v1}/{v2}
     */
    private function generateSmartPath(string $methodName, ReflectionMethod $method): string
    {
        $parameters = $method->getParameters();
        $pathParams = [];
        
        // 提取路径参数
        foreach ($parameters as $param) {
            if ($this->isPathParameter($param)) {
                $pathParams[] = $param->getName();
            }
        }
        
        // 转换方法名
        $pathSegment = RouteHelper::camelToKebab($methodName);
        
        // 没有参数：简单路径
        if (empty($pathParams)) {
            return '/' . $pathSegment;
        }
        
        // 智能组合路径
        return $this->combineSmartPath($pathSegment, $pathParams, $methodName);
    }

    /**
     * 判断是否为路径参数
     */
    private function isPathParameter(\ReflectionParameter $param): bool
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
     * 智能组合路径
     */
    private function combineSmartPath(string $pathSegment, array $pathParams, string $methodName): string
    {
        $paramCount = count($pathParams);
        
        // 单参数情况
        if ($paramCount === 1) {
            $param = $pathParams[0];
            
            // 特殊模式识别
            if ($this->isResourceIdParam($param)) {
                // 资源ID模式：/{id}/action
                return '/{' . $param . '}/' . $pathSegment;
            } elseif ($this->isFilterParam($methodName, $param)) {
                // 过滤器模式：/by-xxx/{param}
                return '/' . $pathSegment . '/{' . $param . '}';
            } else {
                // 默认：/{param}/action
                return '/{' . $param . '}/' . $pathSegment;
            }
        }
        
        // 多参数情况
        if ($paramCount === 2) {
            // 常见模式：/{param1}/action/{param2}
            $param1 = $pathParams[0];
            $param2 = $pathParams[1];
            
            if ($this->isResourceIdParam($param1)) {
                return '/{' . $param1 . '}/' . $pathSegment . '/{' . $param2 . '}';
            } else {
                // 并列参数：/action/{param1}/{param2}
                return '/' . $pathSegment . '/{' . $param1 . '}/{' . $param2 . '}';
            }
        }
        
        // 更多参数：/action/{param1}/{param2}/{param3}...
        $paramPath = array_map(fn($p) => '{' . $p . '}', $pathParams);
        return '/' . $pathSegment . '/' . implode('/', $paramPath);
    }

    /**
     * 判断是否为资源ID参数
     */
    private function isResourceIdParam(string $paramName): bool
    {
        // 常见的资源ID参数名
        $idPatterns = ['id', 'Id', 'ID', 'uuid', 'code', 'key'];
        
        // 精确匹配
        if (in_array($paramName, $idPatterns)) {
            return true;
        }
        
        // 后缀匹配：userId, postId, orderId 等
        foreach ($idPatterns as $pattern) {
            if (str_ends_with($paramName, $pattern)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 判断是否为过滤器模式
     */
    private function isFilterParam(string $methodName, string $paramName): bool
    {
        // 方法名包含 by/By/find/Find/get/Get + 参数相关
        $filterPrefixes = ['by', 'findBy', 'getBy', 'searchBy', 'filterBy'];
        
        $lowerMethod = strtolower($methodName);
        foreach ($filterPrefixes as $prefix) {
            if (str_starts_with($lowerMethod, strtolower($prefix))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检查是否为RESTful标准方法
     */
    private function isRestfulMethod(string $methodName): bool
    {
        return RouteHelper::isRestfulMethod($methodName);
    }

    /**
     * 获取标签（用户设置优先）
     */
    private function getTags(ApiController $controllerAnnotation, string $className): array
    {
        if ($controllerAnnotation->tag) {
            return [$controllerAnnotation->tag];
        }
        
        // 自动生成标签
        return [$this->generateTag($className)];
    }



    /**
     * 自动生成摘要（根据方法名和HTTP方法）
     */
    private function generateSummary(string $methodName, string $httpMethod): string
    {
        // RESTful标准摘要
        $summaries = [
            'GET' => [
                'index' => '获取列表',
                'list' => '获取列表',
                'getList' => '获取列表',
                'show' => '获取详情',
                'detail' => '获取详情',
                'getDetail' => '获取详情',
                'get' => '获取信息',
                'search' => '搜索',
                'query' => '查询',
                'filter' => '筛选',
                'export' => '导出数据',
                'download' => '下载文件',
                // 资源子操作
                'state' => '获取状态',
                'status' => '获取状态',
                'relationships' => '获取关系',
                'relations' => '获取关系',
                'children' => '获取子项',
                'parent' => '获取父项',
                'review' => '获取审核信息',
                'audit' => '获取审计信息',
                'stats' => '获取统计',
                'statistics' => '获取统计',
                'metrics' => '获取指标',
                'analytics' => '获取分析',
                'history' => '获取历史',
                'logs' => '获取日志',
                'versions' => '获取版本',
                'revisions' => '获取修订',
                'permissions' => '获取权限',
                'roles' => '获取角色',
            ],
            'POST' => [
                'create' => '创建',
                'store' => '保存',
                'add' => '添加',
                'post' => '提交',
                'batch' => '批量操作',
                'import' => '导入数据',
                'upload' => '上传文件',
                // 资源子操作
                'enable' => '启用',
                'disable' => '禁用',
                'activate' => '激活',
                'deactivate' => '停用',
                'lock' => '锁定',
                'unlock' => '解锁',
                'publish' => '发布',
                'unpublish' => '取消发布',
                'archive' => '归档',
                'restore' => '恢复',
                'clone' => '克隆',
                'duplicate' => '复制',
                'approve' => '批准',
                'reject' => '拒绝',
                'share' => '分享',
                'unshare' => '取消分享',
            ],
            'PUT' => [
                'update' => '更新',
                'edit' => '编辑',
                'modify' => '修改',
                'put' => '更新',
                'batchUpdate' => '批量更新',
            ],
            'PATCH' => [
                'patch' => '部分更新',
                'update' => '部分更新',
            ],
            'DELETE' => [
                'delete' => '删除',
                'destroy' => '销毁',
                'remove' => '移除',
                'batchDelete' => '批量删除',
            ],
        ];

        $method = strtoupper($httpMethod);
        if (isset($summaries[$method][$methodName])) {
            return $summaries[$method][$methodName];
        }

        // 默认：方法名转中文
        return ucfirst($methodName);
    }

    /**
     * 自动生成标签
     */
    private function generateTag(string $className): string
    {
        // 从类名提取标签：UserController -> User
        $shortName = substr($className, strrpos($className, '\\') + 1);
        if (str_ends_with($shortName, 'Controller')) {
            $shortName = substr($shortName, 0, -10);
        }
        
        // 空格分隔驼峰单词：UserDetail -> User Detail
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', $shortName);
    }

    /**
     * 懒加载路由详细信息
     */
    private function lazyLoadRouteDetails(array $routeInfo, ReflectionMethod $method): array
    {
        // 使用闭包实现懒加载
        $routeInfo['_lazy_parameters'] = fn() => $this->extractParameters($method);
        $routeInfo['_lazy_request_body'] = fn() => $this->extractRequestBody($method);
        
        return $routeInfo;
    }

    /**
     * 获取路由参数（懒加载）
     */
    public function getRouteParameters(array $route): array
    {
        if (isset($route['parameters'])) {
            return $route['parameters'];
        }
        
        if (isset($route['_lazy_parameters'])) {
            $parameters = $route['_lazy_parameters']();
            // 缓存结果
            $route['parameters'] = $parameters;
            return $parameters;
        }
        
        return [];
    }

    /**
     * 获取路由请求体（懒加载）
     */
    public function getRouteRequestBody(array $route): ?array
    {
        if (isset($route['requestBody'])) {
            return $route['requestBody'];
        }
        
        if (isset($route['_lazy_request_body'])) {
            $requestBody = $route['_lazy_request_body']();
            // 缓存结果
            $route['requestBody'] = $requestBody;
            return $requestBody;
        }
        
        return null;
    }

    /**
     * 提取参数信息
     */
    private function extractParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        
        // 路径参数（从方法参数推断）
        $methodParams = $method->getParameters();
        foreach ($methodParams as $param) {
            if ($param->getType() && !$param->getType()->isBuiltin()) {
                continue; // 跳过对象参数
            }
            
            // 常见参数名映射
            $paramName = $param->getName();
            if (in_array($paramName, ['id', 'uuid', 'code', 'key'])) {
                $parameters[] = [
                    'name' => $paramName,
                    'type' => 'path',
                    'required' => !$param->isOptional(),
                    'dataType' => $this->getParameterType($param),
                    'description' => $this->getParameterDescription($paramName)
                ];
            }
        }

        // 查询参数（从验证注解）
        if (class_exists(RequestValidation::class)) {
            $className = $method->getDeclaringClass()->getName();
            $methodName = $method->getName();
            
            $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($className, $methodName);
            $validation = null;
            if ($methodAnnotations && isset($methodAnnotations[RequestValidation::class])) {
                $validation = $methodAnnotations[RequestValidation::class];
            }

            if ($validation && !empty($validation->rules)) {
                $parameters = array_merge($parameters, $this->extractValidationParameters($validation));
            }
        }

        return $parameters;
    }

    /**
     * 获取参数描述
     */
    private function getParameterDescription(string $paramName): string
    {
        $descriptions = [
            'id' => 'ID标识',
            'uuid' => 'UUID标识',
            'code' => '编码',
            'key' => '键值',
            'page' => '页码',
            'size' => '每页数量',
            'limit' => '限制数量',
            'offset' => '偏移量',
            'sort' => '排序字段',
            'order' => '排序方向',
        ];
        
        return $descriptions[$paramName] ?? ucfirst($paramName);
    }

    /**
     * 从验证注解提取参数
     */
    private function extractValidationParameters(RequestValidation $validation): array
    {
        $parameters = [];
        
        foreach ($validation->rules as $field => $rule) {
            // 使用RuleParser（如果可用）
            if (class_exists(RuleParser::class)) {
                [$fieldName, $description] = RuleParser::parseFieldName($field);
                $required = RuleParser::isRequired($rule);
                $nullable = RuleParser::isNullable($rule);
            } else {
                [$fieldName, $description] = $this->parseFieldName($field);
                $required = str_contains($rule, 'required');
                $nullable = str_contains($rule, 'nullable');
            }
            
            // 根据验证类型确定参数位置
            $paramType = ($validation->dateType === 'json') ? 'body' : 'query';
            
            if ($paramType === 'query') {
                $parameters[] = [
                    'name' => $fieldName,
                    'type' => 'query',
                    'required' => $required,
                    'nullable' => $nullable,
                    'dataType' => $this->extractDataType($rule),
                    'description' => $description ?: $this->getParameterDescription($fieldName),
                    'rule' => $rule
                ];
            }
        }
        
        return $parameters;
    }

    /**
     * 构建路由索引（提升查找性能）
     */
    private function buildRouteIndex(array $routes): void
    {
        $this->routeIndex = [
            'by_path' => [],
            'by_controller' => [],
            'by_tag' => [],
            'by_method' => [],
            'restful' => [],
        ];
        
        foreach ($routes as $route) {
            // 按路径索引
            $this->routeIndex['by_path'][$route['path']][] = $route;
            
            // 按控制器索引
            $this->routeIndex['by_controller'][$route['controller']][] = $route;
            
            // 按标签索引
            foreach ($route['tags'] as $tag) {
                $this->routeIndex['by_tag'][$tag][] = $route;
            }
            
            // 按HTTP方法索引
            foreach ($route['methods'] as $method) {
                $this->routeIndex['by_method'][$method][] = $route;
            }
            
            // RESTful路由索引
            if ($route['restful'] ?? false) {
                $this->routeIndex['restful'][] = $route;
            }
        }
    }

    /**
     * 根据路径快速查找路由
     */
    public function findRouteByPath(string $path): ?array
    {
        if (isset($this->routeIndex['by_path'][$path])) {
            return $this->routeIndex['by_path'][$path][0];
        }
        
        return null;
    }

    /**
     * 根据控制器快速查找路由
     */
    public function findRoutesByController(string $controllerClass): array
    {
        return $this->routeIndex['by_controller'][$controllerClass] ?? [];
    }

    /**
     * 根据标签快速查找路由
     */
    public function findRoutesByTag(string $tag): array
    {
        return $this->routeIndex['by_tag'][$tag] ?? [];
    }

    /**
     * 根据HTTP方法快速查找路由
     */
    public function findRoutesByMethod(string $method): array
    {
        return $this->routeIndex['by_method'][strtoupper($method)] ?? [];
    }

    /**
     * 获取所有RESTful路由
     */
    public function getRestfulRoutes(): array
    {
        return $this->routeIndex['restful'] ?? [];
    }

    /**
     * 清除所有缓存
     */
    public function clearCache(): self
    {
        $this->routeCache = [];
        $this->controllerCache = [];
        $this->reflectionCache = [];
        $this->routeIndex = [];
        return $this;
    }

    /**
     * 优化内存使用
     */
    public function optimizeMemory(): void
    {
        // 清理超过阈值的缓存
        $maxCacheSize = 1000;
        
        if (count($this->routeCache) > $maxCacheSize) {
            $this->routeCache = array_slice($this->routeCache, -$maxCacheSize, null, true);
        }
        
        if (count($this->controllerCache) > $maxCacheSize) {
            $this->controllerCache = array_slice($this->controllerCache, -$maxCacheSize, null, true);
        }
        
        if (count($this->reflectionCache) > $maxCacheSize) {
            $this->reflectionCache = array_slice($this->reflectionCache, -$maxCacheSize, null, true);
        }
        
        // 触发垃圾回收
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * 提取请求体信息
     */
    private function extractRequestBody(ReflectionMethod $method): ?array
    {
        if (!class_exists(RequestValidation::class)) {
            return null;
        }

        $className = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();
        
        $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($className, $methodName);
        $validation = null;
        if ($methodAnnotations && isset($methodAnnotations[RequestValidation::class])) {
            $validation = $methodAnnotations[RequestValidation::class];
        }

        if (!$validation || empty($validation->rules) || $validation->dateType !== 'json') {
            return null;
        }

        $properties = [];
        $required = [];

        foreach ($validation->rules as $field => $rule) {
            if (class_exists(RuleParser::class)) {
                [$fieldName, $description] = RuleParser::parseFieldName($field);
                $isRequired = RuleParser::isRequired($rule);
                $isNullable = RuleParser::isNullable($rule);
            } else {
                [$fieldName, $description] = $this->parseFieldName($field);
                $isRequired = str_contains($rule, 'required');
                $isNullable = str_contains($rule, 'nullable');
            }

            $properties[$fieldName] = [
                'type' => $this->extractDataType($rule),
                'description' => $description ?: $fieldName,
                'required' => $isRequired,
                'nullable' => $isNullable,
                'rule' => $rule
            ];

            if ($isRequired) {
                $required[] = $fieldName;
            }
        }

        return [
            'description' => '请求数据',
            'required' => !empty($required),
            'properties' => $properties,
            'requiredFields' => $required
        ];
    }

    /**
     * 获取路由注解
     */
    private function getRouteAnnotation(ReflectionMethod $method): ?Mapping
    {
        $routeAnnotations = [
            GetApi::class, PostApi::class, PutApi::class, 
            DeleteApi::class, PatchApi::class
        ];

        $className = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();

        foreach ($routeAnnotations as $annotationClass) {
            $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($className, $methodName);
            if ($methodAnnotations && isset($methodAnnotations[$annotationClass])) {
                return $methodAnnotations[$annotationClass];
            }
        }

        return null;
    }

    /**
     * 获取参数类型
     */
    private function getParameterType(\ReflectionParameter $param): string
    {
        $type = $param->getType();
        
        if (!$type) {
            return 'string';
        }

        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'string';
        
        return match ($typeName) {
            'int' => 'integer',
            'float' => 'number',
            'bool' => 'boolean',
            'array' => 'array',
            default => 'string'
        };
    }

    /**
     * 从验证规则提取数据类型
     */
    private function extractDataType(string $rule): string
    {
        if (str_contains($rule, 'integer') || str_contains($rule, 'int')) {
            return 'integer';
        } elseif (str_contains($rule, 'numeric') || str_contains($rule, 'decimal')) {
            return 'number';
        } elseif (str_contains($rule, 'boolean') || str_contains($rule, 'bool')) {
            return 'boolean';
        } elseif (str_contains($rule, 'array')) {
            return 'array';
        } elseif (str_contains($rule, 'json')) {
            return 'object';
        }
        
        return 'string';
    }

    /**
     * 解析字段名和描述
     */
    private function parseFieldName(string $field): array
    {
        if (str_contains($field, '|')) {
            [$fieldName, $description] = explode('|', $field, 2);
            return [trim($fieldName), trim($description)];
        }
        
        return [$field, ''];
    }



    /**
     * 获取指定控制器的路由
     */
    public function getControllerRoutes(string $controllerClass): array
    {
        $controllerAnnotation = AnnotationCollector::getClassAnnotation($controllerClass, ApiController::class);
        
        if (!$controllerAnnotation) {
            return [];
        }

        return $this->getControllerRoutesWithCache($controllerClass, $controllerAnnotation);
    }

    /**
     * 获取所有路由路径
     */
    public function getAllPaths(): array
    {
        $routes = $this->collectRoutes();
        return array_unique(array_column($routes, 'path'));
    }
} 