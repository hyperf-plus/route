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
use HPlus\Route\Helper\StringHelper;
use HPlus\Route\Helper\RestfulMapping;
use ReflectionClass;
use ReflectionMethod;

/**
 * 路由收集器
 * 
 * 职责：收集 API 路由的基础信息，供 swagger 等插件使用
 * 
 * 设计原则：
 * - 只收集路由元数据，不处理验证逻辑
 * - 不依赖 validate 插件（验证相关的处理由 swagger 插件负责）
 * - 使用缓存优化性能
 */
class RouteCollector
{
    private static ?self $instance = null;

    private array $routeCache = [];
    private array $controllerCache = [];
    private array $reflectionCache = [];
    private array $routeIndex = [];


    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 收集所有 API 路由
     */
    public function collectRoutes(): array
    {
        if (!empty($this->routeCache)) {
            return $this->routeCache;
        }

        $routes = [];
        $controllers = AnnotationCollector::getClassesByAnnotation(ApiController::class);
        
        foreach ($controllers as $className => $controllerAnnotation) {
            $controllerRoutes = $this->getControllerRoutesWithCache($className, $controllerAnnotation);
            $routes = array_merge($routes, $controllerRoutes);
        }

        $this->buildRouteIndex($routes);
        $this->routeCache = $routes;
        
        return $routes;
    }

    private function getControllerRoutesWithCache(string $className, ApiController $controllerAnnotation): array
    {
        if (isset($this->controllerCache[$className])) {
            return $this->controllerCache[$className];
        }

        $reflectionClass = $this->getReflectionClassWithCache($className);
        $routes = $this->collectControllerRoutes($reflectionClass, $controllerAnnotation);
        
        $this->controllerCache[$className] = $routes;
        return $routes;
    }

    private function getReflectionClassWithCache(string $className): ReflectionClass
    {
        if (!isset($this->reflectionCache[$className])) {
            $this->reflectionCache[$className] = new ReflectionClass($className);
        }
        return $this->reflectionCache[$className];
    }

    private function collectControllerRoutes(ReflectionClass $controller, ApiController $controllerAnnotation): array
    {
        $routes = [];
        $className = $controller->getName();
        $controllerPrefix = $this->getControllerPrefix($className, $controllerAnnotation);
        $methods = $controller->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            if ($method->isConstructor() || str_starts_with($method->getName(), '__')) {
                continue;
            }

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

    private function getControllerPrefix(string $className, ApiController $controllerAnnotation): string
    {
        if (!empty($controllerAnnotation->prefix)) {
            return $controllerAnnotation->prefix;
        }
        return $this->generateRestfulPrefix($className);
    }

    private function generateRestfulPrefix(string $className): string
    {
        $classPath = str_replace('\\', '/', $className);
        
        if (str_contains($classPath, '/Controller/')) {
            $controllerPart = substr($classPath, strpos($classPath, '/Controller/') + 12);
        } else {
            $controllerPart = substr($className, strrpos($className, '\\') + 1);
        }
        
        if (str_ends_with($controllerPart, 'Controller')) {
            $controllerPart = substr($controllerPart, 0, -10);
        }
        
        $parts = explode('/', $controllerPart);
        $resourceName = array_pop($parts);
        
        $resourceName = StringHelper::camelToKebab($resourceName);
        $resourceName = StringHelper::pluralize($resourceName);

        // 按目录结构生成前缀，不再默认加 /api
        $prefix = '';
        if (!empty($parts)) {
            $prefix .= '/' . implode('/', array_map('strtolower', $parts));
        }
        $prefix .= '/' . $resourceName;

        return $this->normalizePath($prefix);
    }

    private function buildRouteInfo(
        string $className,
        ReflectionMethod $method,
        Mapping $routeAnnotation,
        ApiController $controllerAnnotation,
        string $controllerPrefix
    ): array {
        $methodName = $method->getName();
        $httpMethod = $routeAnnotation->methods[0] ?? 'GET';
        
        $routePath = $this->getRoutePath($methodName, $httpMethod, $routeAnnotation, $method);
        
        if ($routePath && !str_starts_with($routePath, '/')) {
            $routePath = '/' . $routePath;
        }
        
        $fullPath = $this->normalizePath($controllerPrefix . $routePath);
        
        return [
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
            'restful' => $this->isRestfulMethod($methodName),
            // 保留方法的反射信息，供其他插件（如 swagger）使用
            '_method' => $method,
        ];
    }

    private function getRoutePath(string $methodName, string $httpMethod, Mapping $routeAnnotation, ReflectionMethod $method): string
    {
        // 用户指定的 path 优先
        if (isset($routeAnnotation->path)) {
            return $routeAnnotation->path;
        }

        // 检查是否符合 RESTful 标准映射
        $mapping = RestfulMapping::getMapping($methodName);
        if ($mapping !== null) {
            [$expectedMethod, $pathTemplate] = $mapping;
            if (strtoupper($httpMethod) === $expectedMethod) {
                return $pathTemplate;
            }
        }

        // 默认：方法名转为 kebab-case 路径
        return '/' . StringHelper::camelToKebab($methodName);
    }

    private function isRestfulMethod(string $methodName): bool
    {
        return RestfulMapping::isRestfulMethod($methodName);
    }

    private function getTags(ApiController $controllerAnnotation, string $className): array
    {
        if ($controllerAnnotation->tag) {
            return [$controllerAnnotation->tag];
        }
        return [$this->generateTag($className)];
    }

    private function generateSummary(string $methodName, string $httpMethod): string
    {
        $summaries = [
            'GET' => [
                'index' => '获取列表', 'list' => '获取列表', 'getList' => '获取列表',
                'show' => '获取详情', 'detail' => '获取详情', 'getDetail' => '获取详情', 'get' => '获取信息',
                'search' => '搜索', 'query' => '查询', 'filter' => '筛选',
                'export' => '导出数据', 'download' => '下载文件',
            ],
            'POST' => [
                'create' => '创建', 'store' => '保存', 'add' => '添加', 'post' => '提交',
                'batch' => '批量操作', 'import' => '导入数据', 'upload' => '上传文件',
            ],
            'PUT' => [
                'update' => '更新', 'edit' => '编辑', 'modify' => '修改', 'put' => '更新',
                'batchUpdate' => '批量更新',
            ],
            'PATCH' => ['patch' => '部分更新', 'update' => '部分更新'],
            'DELETE' => ['delete' => '删除', 'destroy' => '销毁', 'remove' => '移除', 'batchDelete' => '批量删除'],
        ];

        $method = strtoupper($httpMethod);
        return $summaries[$method][$methodName] ?? ucfirst($methodName);
    }

    private function generateTag(string $className): string
    {
        $shortName = substr($className, strrpos($className, '\\') + 1);
        if (str_ends_with($shortName, 'Controller')) {
            $shortName = substr($shortName, 0, -10);
        }
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', $shortName);
    }

    private function getRouteAnnotation(ReflectionMethod $method): ?Mapping
    {
        static $routeAnnotations = [
            GetApi::class, PostApi::class, PutApi::class, 
            DeleteApi::class, PatchApi::class
        ];

        $className = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();

        // 只调用一次 getClassMethodAnnotation
        $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($className, $methodName);
        if (!$methodAnnotations) {
            return null;
        }

        foreach ($routeAnnotations as $annotationClass) {
            if (isset($methodAnnotations[$annotationClass])) {
                return $methodAnnotations[$annotationClass];
            }
        }

        return null;
    }

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
            $this->routeIndex['by_path'][$route['path']][] = $route;
            $this->routeIndex['by_controller'][$route['controller']][] = $route;
            
            foreach ($route['tags'] as $tag) {
                $this->routeIndex['by_tag'][$tag][] = $route;
            }
            
            foreach ($route['methods'] as $method) {
                $this->routeIndex['by_method'][$method][] = $route;
            }
            
            if ($route['restful'] ?? false) {
                $this->routeIndex['restful'][] = $route;
            }
        }
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    // === 公共查询方法 ===

    public function findRouteByPath(string $path): ?array
    {
        return $this->routeIndex['by_path'][$path][0] ?? null;
    }

    public function findRoutesByController(string $controllerClass): array
    {
        return $this->routeIndex['by_controller'][$controllerClass] ?? [];
    }

    public function findRoutesByTag(string $tag): array
    {
        return $this->routeIndex['by_tag'][$tag] ?? [];
    }

    public function findRoutesByMethod(string $method): array
    {
        return $this->routeIndex['by_method'][strtoupper($method)] ?? [];
    }

    public function getRestfulRoutes(): array
    {
        return $this->routeIndex['restful'] ?? [];
    }

    public function getControllerRoutes(string $controllerClass): array
    {
        $controllerAnnotation = AnnotationCollector::getClassAnnotation($controllerClass, ApiController::class);
        
        if (!$controllerAnnotation) {
            return [];
        }

        return $this->getControllerRoutesWithCache($controllerClass, $controllerAnnotation);
    }

    public function getAllPaths(): array
    {
        $routes = $this->collectRoutes();
        return array_unique(array_column($routes, 'path'));
    }

    /**
     * 获取路由统计信息
     */
    public function getRouteStats(): array
    {
        $routes = $this->collectRoutes();
        
        $methodsDistribution = [];
        $controllers = [];
        $pathPatterns = [
            'static' => 0,
            'dynamic' => 0,
        ];
        
        foreach ($routes as $route) {
            // 统计 HTTP 方法分布
            foreach ($route['methods'] as $method) {
                $methodsDistribution[$method] = ($methodsDistribution[$method] ?? 0) + 1;
            }
            
            // 统计控制器
            $controllers[$route['controller']] = true;
            
            // 统计路径模式
            if (str_contains($route['path'], '{')) {
                $pathPatterns['dynamic']++;
            } else {
                $pathPatterns['static']++;
            }
        }
        
        return [
            'total_routes' => count($routes),
            'total_controllers' => count($controllers),
            'methods_distribution' => $methodsDistribution,
            'path_patterns' => $pathPatterns,
        ];
    }

    /**
     * 获取缓存统计信息
     */
    public function getCacheStats(): array
    {
        return [
            'routes_cached' => count($this->routeCache),
            'controllers_cached' => count($this->controllerCache),
            'reflections_cached' => count($this->reflectionCache),
        ];
    }

    public function clearCache(): self
    {
        $this->routeCache = [];
        $this->controllerCache = [];
        $this->reflectionCache = [];
        $this->routeIndex = [];
        return $this;
    }
}
