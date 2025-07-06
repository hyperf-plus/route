<?php

declare(strict_types=1);

namespace App\Controller;

use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\RouteCollector;
use Psr\Container\ContainerInterface;

#[ApiController(prefix: '/debug', description: '路由调试控制器')]
class RouteDebugController
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    #[GetApi(path: '/routes', summary: '获取所有HPlus Route注解路由')]
    public function routes(): array
    {
        try {
            $collector = $this->container->get(RouteCollector::class);
            $routes = $collector->collectRoutes();
            
            return [
                'code' => 200,
                'message' => 'HPlus Route注解路由信息获取成功',
                'data' => [
                    'total_routes' => count($routes),
                    'routes' => $routes,
                    'collector_class' => get_class($collector),
                    'environment' => [
                        'php_version' => PHP_VERSION,
                        'php_version_id' => PHP_VERSION_ID,
                        'attributes_supported' => PHP_VERSION_ID >= 80000,
                        'swoole_version' => defined('SWOOLE_VERSION') ? SWOOLE_VERSION : 'not installed'
                    ]
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => '获取路由信息失败：' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    #[GetApi(path: '/route/{controller}', summary: '获取指定控制器的路由')]
    public function controllerRoutes(string $controller): array
    {
        try {
            $collector = $this->container->get(RouteCollector::class);
            $controllerClass = "App\\Controller\\{$controller}Controller";
            
            if (!class_exists($controllerClass)) {
                return [
                    'code' => 404,
                    'message' => "控制器 {$controllerClass} 不存在"
                ];
            }
            
            $routes = $collector->getControllerRoutes($controllerClass);
            
            return [
                'code' => 200,
                'message' => "控制器 {$controllerClass} 的路由信息",
                'data' => [
                    'controller' => $controllerClass,
                    'route_count' => count($routes),
                    'routes' => $routes
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => '获取控制器路由信息失败：' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    #[GetApi(path: '/annotations', summary: '测试属性注解扫描')]
    public function testAnnotations(): array
    {
        $testResults = [];
        
        // 测试 ApiController 注解
        $reflectionClass = new \ReflectionClass(ApiTestController::class);
        $classAttributes = $reflectionClass->getAttributes();
        
        foreach ($classAttributes as $attribute) {
            $testResults['class_annotations'][] = [
                'name' => $attribute->getName(),
                'arguments' => $attribute->getArguments()
            ];
        }
        
        // 测试方法注解
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $methodAttributes = $method->getAttributes();
            if (!empty($methodAttributes)) {
                foreach ($methodAttributes as $attribute) {
                    $testResults['method_annotations'][$method->getName()][] = [
                        'name' => $attribute->getName(),
                        'arguments' => $attribute->getArguments()
                    ];
                }
            }
        }
        
        return [
            'code' => 200,
            'message' => 'PHP 8+ 属性注解扫描测试结果',
            'data' => [
                'test_class' => ApiTestController::class,
                'annotation_scan_results' => $testResults,
                'php_version' => PHP_VERSION,
                'reflection_api_available' => method_exists(\ReflectionClass::class, 'getAttributes')
            ]
        ];
    }
}