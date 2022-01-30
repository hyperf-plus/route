<?php
declare(strict_types=1);

namespace HPlus\Route;

use HPlus\Route\Annotation\AdminController;
use HPlus\Route\Annotation\ApiController;
use Hyperf\Di\Exception\ConflictAnnotationException;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Mapping;
use Hyperf\HttpServer\Router\DispatcherFactory as Dispatcher;
use Hyperf\HttpServer\Router\RouteCollector;

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
        $class = ReflectionManager::reflectClass($className);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        if ($annotation->prefix != '' && $annotation->prefix[0] !== '/') {
            $annotation->prefix = '/' . $annotation->prefix;
        }
        $service = $annotation->service ?? '';
        $prefix = !empty($service) ? ('/' . $service) . $prefix : $prefix;
        $prefix = $prefix . $this->getPrefix($className, $annotation->prefix);
        $router = $this->getRouter($annotation->server);
        foreach ($methods as $methodName => $method) {
            $methodMiddlewares = $middlewares;
            // Handle method level middlewares.
            $methodName = $method->getName();
            if (isset($methodMetadata[$methodName])) {
                $methodMiddlewares = array_merge($methodMiddlewares, $this->handleMiddleware($methodMetadata[$methodName]));
                $methodMiddlewares = array_unique($methodMiddlewares);
            }
            if (substr($methodName, 0, 2) === '__') {
                continue;
            }
            $methodAnnotations = ApiAnnotation::methodMetadata($method->class, $method->name);
            foreach ($methodAnnotations as $mapping) {
                if (!$mapping instanceof Mapping) {
                    continue;
                }
                if (!isset($mapping->methods)) {
                    continue;
                }

                $path = $prefix . '/' . $methodName;
                if ($mapping->path) {
                    $path = $prefix . '/' . $mapping->path;
                }
                if ($this->hasRoute($router, $mapping, $path)) {
                    continue;
                }
                $path = str_replace('/_self_path', '', $path);
                if (substr($path, 0, 1) !== '/') {
                    $path = '/' . $path;
                }
                $router->addRoute($mapping->methods, $path, [$className, $methodName], [
                    'middleware' => $methodMiddlewares,
                ]);
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
