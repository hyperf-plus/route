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
use HPlus\Route\Utils\RouteHelper;
use Hyperf\Collection\Arr;
use Hyperf\Di\Exception\ConflictAnnotationException;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Mapping;
use Hyperf\HttpServer\Annotation\PatchMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Router\DispatcherFactory as Dispatcher;
use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\Stringable\Str;
use function Hyperf\Config\config;

class DispatcherFactory extends Dispatcher
{
    /**
     * 根据注解注册路由
     * @param string $className
     * @param Controller $annotation
     * @param array $methodMetadata
     * @param array $middlewares
     * @param string $prefix
     * @throws ConflictAnnotationException
     */
    protected function handleController(string $className, Controller $annotation, array $methodMetadata, array $middlewares = [], $prefix = ''): void
    {
        if (!$methodMetadata) {
            return;
        }
        $prefix = $this->getPrefix($className, $annotation->prefix, $annotation->service ?? "");
        $router = $this->getRouter($annotation->server);
        $mappingAnnotations = [
            RequestMapping::class,
            GetMapping::class,
            PostMapping::class,
            PutMapping::class,
            PatchMapping::class,
            DeleteMapping::class,
            GetApi::class,
            PostApi::class,
            PutApi::class,
            DeleteApi::class,
            PatchApi::class,
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
                        $path = $this->getRestfulPath($methodName, $mapping->methods[0] ?? 'GET', $prefix, $className);
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

    protected function getPrefix(string $className, string $prefix, string $service = ""): string
    {
        if (!$prefix) {
            $handledNamespace = Str::replaceFirst('Controller', '', Str::after($className, '\Controller\\'));
            $handledNamespace = str_replace('\\', '/', $service . "\\" . $handledNamespace);
            $prefix = RouteHelper::camelToKebab($handledNamespace);
            $prefix = str_replace('/-', '/', $prefix);
            
            // RESTful 风格：转换为复数形式
            $parts = explode('/', $prefix);
            $lastPart = array_pop($parts);
            if ($lastPart) {
                $lastPart = RouteHelper::pluralize($lastPart);
                $parts[] = $lastPart;
            }
            $prefix = implode('/', $parts);
        }

        if ($prefix[0] !== '/') {
            $prefix = '/' . $prefix;
        }
        return $prefix;
    }

    protected function initAnnotationRoute(array $collector): void
    {
        foreach ($collector as $className => $metadata) {
            if (isset($metadata['_c'][ApiController::class])) {
                $middlewares = $this->handleMiddleware($metadata['_c']);
                $this->handleController($className, $metadata['_c'][ApiController::class], $metadata['_m'] ?? [], $middlewares);
            }
            if (isset($metadata['_c'][AdminController::class])) {
                $middlewares = $this->handleMiddleware($metadata['_c']);
                $prefix = config('admin.route.prefix', '');
                foreach (config('admin.route.middleware', []) as $middleware) {
                    if (!class_exists($middleware)) continue;
                    $middlewares = array_merge($middlewares, [$middleware]);
                }
                $this->handleController($className, $metadata['_c'][AdminController::class], $metadata['_m'] ?? [], $middlewares, $prefix);
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

    private function hasRoute(RouteCollector $router, Mapping $mapping, $path)
    {
        foreach ($router->getData() as $datum) {
            foreach ($mapping->methods as $method) {
                if (isset($datum[$method][$path])) return true;
            }
        }
        return false;
    }

    /**
     * 根据 RESTful 规则生成路径（支持基于参数的智能生成）
     */
    private function getRestfulPath(string $methodName, string $httpMethod, string $prefix, string $className): string
    {
        // 创建反射方法以获取参数信息
        $reflectionMethod = null;
        try {
            $reflectionMethod = new \ReflectionMethod($className, $methodName);
        } catch (\ReflectionException $e) {
            // 如果获取反射失败，继续使用基本逻辑
        }
        
        // 使用工具类获取RESTful路径（传递反射方法）
        $pathTemplate = RouteHelper::getRestfulPath($methodName, $httpMethod, $reflectionMethod);
        
        if ($pathTemplate !== null) {
            return $prefix . $pathTemplate;
        }

        // 默认：方法名转路径（驼峰转中划线）
        return $prefix . '/' . RouteHelper::camelToKebab($methodName);
    }



}
