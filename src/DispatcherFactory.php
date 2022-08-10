<?php
declare(strict_types=1);

namespace HPlus\Route;

use HPlus\Route\Annotation\AdminController;
use HPlus\Route\Annotation\ApiController;
use Hyperf\Di\Exception\ConflictAnnotationException;
use Hyperf\Di\ReflectionManager;
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
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;

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

        if (! $methodMetadata) {
            return;
        }
        $prefix = $this->getPrefix($className, $annotation->prefix);
        $router = $this->getRouter($annotation->server);

        $mappingAnnotations = [
            RequestMapping::class,
            GetMapping::class,
            PostMapping::class,
            PutMapping::class,
            PatchMapping::class,
            DeleteMapping::class,

        ];

        foreach ($methodMetadata as $methodName => $values) {
            $options = $annotation->options;
            $methodMiddlewares = $middlewares;
            // Handle method level middlewares.
            if (isset($values)) {
                $methodMiddlewares = array_merge($methodMiddlewares, $this->handleMiddleware($values));
            }
            // Rewrite by annotation @Middleware for Controller.
            $options['middleware'] = array_unique($methodMiddlewares);
            foreach ($mappingAnnotations as $mappingAnnotation) {
                /** @var Mapping $mapping */
                if ($mapping = $values[$mappingAnnotation] ?? null) {
                    if (! isset($mapping->methods) || ! isset($mapping->options)) {
                        continue;
                    }
                    $methodOptions = Arr::merge($options, $mapping->options);
                    // Rewrite by annotation @Middleware for method.
                    $methodOptions['middleware'] = $options['middleware'];
                    if (! isset($mapping->path)) {
                        $path = $prefix . '/' . Str::snake($methodName);
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

}
