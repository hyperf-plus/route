<?php

declare(strict_types=1);

namespace App\Controller;

use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\RouteCollector;

#[ApiController(prefix: '/debug', description: '路由调试控制器')]
class RouteDebugController
{
    #[GetApi(path: '/routes', summary: '获取所有路由信息')]
    public function routes(): array
    {
        $collector = RouteCollector::getInstance();
        $routes = $collector->collectRoutes();
        
        return [
            'code' => 200,
            'message' => '路由信息获取成功',
            'data' => [
                'total_routes' => count($routes),
                'routes' => $routes,
                'php_version' => PHP_VERSION,
                'php_version_id' => PHP_VERSION_ID,
                'attributes_supported' => PHP_VERSION_ID >= 80000,
            ]
        ];
    }

    #[GetApi(path: '/routes/stats', summary: '获取路由统计信息')]
    public function stats(): array
    {
        $collector = RouteCollector::getInstance();
        $routes = $collector->collectRoutes();
        
        $stats = [
            'total_routes' => count($routes),
            'controllers' => [],
            'methods' => [],
            'paths' => [],
        ];

        foreach ($routes as $route) {
            // 统计控制器
            if (!isset($stats['controllers'][$route['controller']])) {
                $stats['controllers'][$route['controller']] = 0;
            }
            $stats['controllers'][$route['controller']]++;

            // 统计HTTP方法
            foreach ($route['methods'] as $method) {
                if (!isset($stats['methods'][$method])) {
                    $stats['methods'][$method] = 0;
                }
                $stats['methods'][$method]++;
            }

            // 统计路径
            $stats['paths'][] = $route['path'];
        }

        return [
            'code' => 200,
            'message' => '路由统计信息获取成功',
            'data' => $stats
        ];
    }

    #[GetApi(path: '/routes/test', summary: '测试路由收集功能')]
    public function test(): array
    {
        $collector = RouteCollector::getInstance();
        
        // 清除缓存
        $collector->clearCache();
        
        // 重新收集路由
        $routes = $collector->collectRoutes();
        
        $testResults = [
            'cache_cleared' => true,
            'routes_collected' => count($routes),
            'test_controller_routes' => 0,
            'user_controller_routes' => 0,
            'annotations_working' => false,
        ];

        foreach ($routes as $route) {
            if ($route['controller'] === 'App\\Controller\\TestController') {
                $testResults['test_controller_routes']++;
            }
            if ($route['controller'] === 'App\\Controller\\UserController') {
                $testResults['user_controller_routes']++;
            }
        }

        $testResults['annotations_working'] = $testResults['test_controller_routes'] > 0 || $testResults['user_controller_routes'] > 0;

        return [
            'code' => 200,
            'message' => '路由收集测试完成',
            'data' => $testResults
        ];
    }
}