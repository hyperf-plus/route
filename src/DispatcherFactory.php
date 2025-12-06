<?php
declare(strict_types=1);

namespace HPlus\Route;

use HPlus\Route\Annotation\AdminController;
use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\DeleteApi;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PatchApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Helper\StringHelper;
use HPlus\Route\Helper\RestfulMapping;
use Hyperf\Collection\Arr;
use Hyperf\Di\Exception\ConflictAnnotationException;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Mapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PatchMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\PriorityMiddleware;
use Hyperf\HttpServer\Router\DispatcherFactory as Dispatcher;
use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\Stringable\Str;
use function Hyperf\Config\config;

class DispatcherFactory extends Dispatcher
{
    /**
     * 扩展的路由注解类型
     */
    protected array $extendedMappingAnnotations = [
        GetApi::class,
        PostApi::class,
        PutApi::class,
        DeleteApi::class,
        PatchApi::class,
    ];

    /**
     * 根据注解注册路由（支持 ApiController 和 AdminController）
     * 
     * @param string $className
     * @param Controller $annotation
     * @param array $methodMetadata
     * @param PriorityMiddleware[] $middlewares
     * @throws ConflictAnnotationException
     */
    protected function handleController(string $className, Controller $annotation, array $methodMetadata, array $middlewares = []): void
    {
        if (!$methodMetadata) {
            return;
        }

        // 检查是否是扩展的控制器类型
        $isExtendedController = $annotation instanceof ApiController || $annotation instanceof AdminController;
        
        if ($isExtendedController) {
            $this->handleExtendedController($className, $annotation, $methodMetadata, $middlewares);
        } else {
            // 使用父类处理标准 Controller
            parent::handleController($className, $annotation, $methodMetadata, $middlewares);
        }
    }

    /**
     * 处理扩展的控制器（ApiController/AdminController）
     * 
     * @param string $className
     * @param Controller $annotation
     * @param array $methodMetadata
     * @param PriorityMiddleware[] $middlewares
     */
    protected function handleExtendedController(string $className, Controller $annotation, array $methodMetadata, array $middlewares = []): void
    {
        $service = property_exists($annotation, 'service') ? ($annotation->service ?? '') : '';
        $prefix = $this->getPrefix($className, $annotation->prefix, $service);
        $router = $this->getRouter($annotation->server);
        
        // 合并标准注解和扩展注解
        $mappingAnnotations = [
            RequestMapping::class,
            GetMapping::class,
            PostMapping::class,
            PutMapping::class,
            PatchMapping::class,
            DeleteMapping::class,
            ...$this->extendedMappingAnnotations,
        ];

        foreach ($methodMetadata as $methodName => $values) {
            $options = $annotation->options;
            $methodMiddlewares = $middlewares;
            
            // Handle method level middlewares.
            if (isset($values)) {
                $methodMiddlewares = array_merge($methodMiddlewares, $this->handleMiddleware($values));
            }
            
            // Rewrite by annotation @Middleware for Controller.
            $options['middleware'] = $methodMiddlewares;
            
            foreach ($mappingAnnotations as $mappingAnnotation) {
                /** @var Mapping $mapping */
                if ($mapping = $values[$mappingAnnotation] ?? null) {
                    if (!isset($mapping->methods) || !isset($mapping->options)) {
                        continue;
                    }
                    
                    $methodOptions = Arr::merge($options, $mapping->options);
                    // Rewrite by annotation @Middleware for method.
                    $methodOptions['middleware'] = $options['middleware'];
                    
                    if (!isset($mapping->path)) {
                        // 使用 RESTful 规则生成路径
                        $path = $this->getRestfulPath($methodName, $mapping->methods[0] ?? 'GET', $prefix);
                    } elseif ($mapping->path === '') {
                        $path = $prefix;
                    } elseif ($mapping->path[0] !== '/') {
                        $path = rtrim($prefix, '/') . '/' . $mapping->path;
                    } else {
                        $path = $mapping->path;
                    }

                    $path = str_replace('/_self_path', '', $path);
                    if (!str_starts_with($path, '/')) {
                        $path = '/' . $path;
                    }
                    
                    $router->addRoute($mapping->methods, $path, [$className, $methodName], $methodOptions);
                }
            }
        }
    }

    /**
     * 获取路由前缀
     */
    protected function getPrefix(string $className, string $prefix, string $service = ""): string
    {
        if (!$prefix) {
            $handledNamespace = Str::replaceFirst('Controller', '', Str::after($className, '\Controller\\'));
            $handledNamespace = str_replace('\\', '/', $service . "\\" . $handledNamespace);
            // 使用 kebab-case（现代 RESTful API 标准）
            $prefix = StringHelper::camelToKebab($handledNamespace);
            $prefix = str_replace('/-', '/', $prefix);
            
            // RESTful 风格：转换为复数形式
            $parts = explode('/', $prefix);
            $lastPart = array_pop($parts);
            if ($lastPart) {
                $lastPart = StringHelper::pluralize($lastPart);
                $parts[] = $lastPart;
            }
            $prefix = implode('/', $parts);
        }

        if ($prefix[0] !== '/') {
            $prefix = '/' . $prefix;
        }
        return $prefix;
    }

    /**
     * 初始化注解路由
     */
    protected function initAnnotationRoute(array $collector): void
    {
        foreach ($collector as $className => $metadata) {
            if (isset($metadata['_c'][ApiController::class])) {
                $middlewares = $this->handleMiddleware($metadata['_c']);
                $this->handleController($className, $metadata['_c'][ApiController::class], $metadata['_m'] ?? [], $middlewares);
            }
            if (isset($metadata['_c'][AdminController::class])) {
                $middlewares = $this->handleMiddleware($metadata['_c']);
                
                // 添加 AdminController 配置的中间件
                $adminMiddlewares = config('admin.route.middleware', []);
                foreach ($adminMiddlewares as $middleware) {
                    if (!class_exists($middleware)) {
                        continue;
                    }
                    $middlewares[] = new PriorityMiddleware($middleware);
                }
                
                $this->handleController($className, $metadata['_c'][AdminController::class], $metadata['_m'] ?? [], $middlewares);
            }
            if (isset($metadata['_c'][AutoController::class])) {
                if ($this->hasControllerAnnotation($metadata['_c'])) {
                    $message = sprintf('AutoController annotation can\'t use with Controller annotation at the same time in %s.', $className);
                    throw new ConflictAnnotationException($message);
                }
                $middlewares = $this->handleMiddleware($metadata['_c']);
                $this->handleAutoController($className, $metadata['_c'][AutoController::class], $middlewares, $metadata['_m'] ?? []);
            }
            if (isset($metadata['_c'][Controller::class])) {
                $middlewares = $this->handleMiddleware($metadata['_c']);
                parent::handleController($className, $metadata['_c'][Controller::class], $metadata['_m'] ?? [], $middlewares);
            }
        }
    }

    /**
     * 检查路由是否已存在
     */
    private function hasRoute(RouteCollector $router, Mapping $mapping, string $path): bool
    {
        foreach ($router->getData() as $datum) {
            foreach ($mapping->methods as $method) {
                if (isset($datum[$method][$path])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 根据 RESTful 规则生成路径
     */
    private function getRestfulPath(string $methodName, string $httpMethod, string $prefix): string
    {
        // 检查是否符合 RESTful 映射
        $mapping = RestfulMapping::getMapping($methodName);
        if ($mapping !== null) {
            [$expectedMethod, $pathTemplate] = $mapping;
            
            // 如果 HTTP 方法匹配，使用 RESTful 路径模板
            if (strtoupper($httpMethod) === $expectedMethod) {
                return $prefix . $pathTemplate;
            }
        }

        // 默认：方法名转 kebab-case 路径
        return $prefix . '/' . StringHelper::camelToKebab($methodName);
    }
}
