<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit;

use HPlus\Route\RouteCollector;
use HPlus\Route\Tests\Fixtures\TestApiController;
use HPlus\Route\Tests\Fixtures\RestfulController;
use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * RouteCollector 测试类
 * 
 * @group route-collector
 */
final class RouteCollectorTest extends AbstractTestCase
{
    private RouteCollector $routeCollector;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 清理之前的注解收集器状态
        $this->clearAnnotationCollector();
        
        // 注册测试控制器注解
        $this->registerTestControllerAnnotations();
        
        // 获取路由收集器实例
        $this->routeCollector = RouteCollector::getInstance();
        
        // 清理缓存
        $this->routeCollector->clearCache();
    }

    protected function tearDown(): void
    {
        // 清理缓存
        $this->routeCollector->clearCache();
        parent::tearDown();
    }

    /**
     * @test
     * @group singleton
     */
    public function it_should_be_singleton(): void
    {
        $instance1 = RouteCollector::getInstance();
        $instance2 = RouteCollector::getInstance();
        
        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(RouteCollector::class, $instance1);
    }

    /**
     * @test
     * @group route-collection
     */
    public function it_can_collect_api_controller_routes(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $this->assertIsArray($routes);
        $this->assertNotEmpty($routes);
        
        // 验证至少包含我们的测试路由
        $testControllerRoutes = array_filter($routes, function ($route) {
            return $route['controller'] === TestApiController::class;
        });
        
        $this->assertNotEmpty($testControllerRoutes, 'TestApiController routes not found');
    }

    /**
     * @test
     * @group route-format
     */
    public function it_generates_correct_route_format(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        foreach ($routes as $route) {
            $this->assertValidRouteFormat($route);
            $this->assertValidHttpMethods($route['methods']);
            
            // 验证路径格式
            $this->assertStringStartsWith('/', $route['path']);
            
            // 验证控制器类存在
            $this->assertTrue(class_exists($route['controller']));
            
            // 验证方法存在
            $this->assertTrue(method_exists($route['controller'], $route['action']));
        }
    }

    /**
     * @test
     * @group route-path
     */
    public function it_handles_explicit_path_correctly(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        // 查找具体的路由
        $testRoutes = array_filter($routes, function ($route) {
            return $route['controller'] === TestApiController::class;
        });
        
        // 验证带参数的路由
        $showRoute = array_filter($testRoutes, function ($route) {
            return $route['action'] === 'show';
        });
        
        $this->assertNotEmpty($showRoute);
        $showRoute = array_values($showRoute)[0];
        
        $this->assertStringContainsString('{id}', $showRoute['path']);
        $this->assertEquals(['GET'], $showRoute['methods']);
    }

    /**
     * @test
     * @group restful-mapping
     */
    public function it_generates_restful_routes_correctly(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $restfulRoutes = array_filter($routes, function ($route) {
            return $route['controller'] === RestfulController::class;
        });
        
        $this->assertNotEmpty($restfulRoutes);
        
        // 验证标准RESTful方法
        $indexRoute = $this->findRouteByAction($restfulRoutes, 'index');
        $this->assertNotNull($indexRoute);
        $this->assertEquals(['GET'], $indexRoute['methods']);
        
        $showRoute = $this->findRouteByAction($restfulRoutes, 'show');
        $this->assertNotNull($showRoute);
        $this->assertEquals(['GET'], $showRoute['methods']);
        $this->assertStringContainsString('{id}', $showRoute['path']);
        
        $createRoute = $this->findRouteByAction($restfulRoutes, 'create');
        $this->assertNotNull($createRoute);
        $this->assertEquals(['POST'], $createRoute['methods']);
        
        $updateRoute = $this->findRouteByAction($restfulRoutes, 'update');
        $this->assertNotNull($updateRoute);
        $this->assertEquals(['PUT'], $updateRoute['methods']);
        $this->assertStringContainsString('{id}', $updateRoute['path']);
        
        $deleteRoute = $this->findRouteByAction($restfulRoutes, 'delete');
        $this->assertNotNull($deleteRoute);
        $this->assertEquals(['DELETE'], $deleteRoute['methods']);
        $this->assertStringContainsString('{id}', $deleteRoute['path']);
    }

    /**
     * @test
     * @group route-prefix
     */
    public function it_applies_controller_prefix_correctly(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $testRoutes = array_filter($routes, function ($route) {
            return $route['controller'] === TestApiController::class;
        });
        
        foreach ($testRoutes as $route) {
            $this->assertStringStartsWith('/test', $route['path']);
        }
    }

    /**
     * @test
     * @group route-search
     */
    public function it_can_find_routes_by_path(): void
    {
        $this->routeCollector->collectRoutes();
        
        $route = $this->routeCollector->findRouteByPath('/test');
        $this->assertNotNull($route);
        $this->assertEquals(TestApiController::class, $route['controller']);
        
        $route = $this->routeCollector->findRouteByPath('/test/{id}');
        $this->assertNotNull($route);
        $this->assertEquals('show', $route['action']);
    }

    /**
     * @test
     * @group route-search
     */
    public function it_can_find_routes_by_controller(): void
    {
        $this->routeCollector->collectRoutes();
        
        $routes = $this->routeCollector->findRoutesByController(TestApiController::class);
        $this->assertNotEmpty($routes);
        
        foreach ($routes as $route) {
            $this->assertEquals(TestApiController::class, $route['controller']);
        }
    }

    /**
     * @test
     * @group route-search
     */
    public function it_can_find_routes_by_method(): void
    {
        $this->routeCollector->collectRoutes();
        
        $getRoutes = $this->routeCollector->findRoutesByMethod('GET');
        $this->assertNotEmpty($getRoutes);
        
        foreach ($getRoutes as $route) {
            $this->assertContains('GET', $route['methods']);
        }
        
        $postRoutes = $this->routeCollector->findRoutesByMethod('POST');
        $this->assertNotEmpty($postRoutes);
        
        foreach ($postRoutes as $route) {
            $this->assertContains('POST', $route['methods']);
        }
    }

    /**
     * @test
     * @group cache
     */
    public function it_caches_routes_correctly(): void
    {
        // 第一次收集路由
        $routes1 = $this->routeCollector->collectRoutes();
        
        // 第二次收集路由（应该从缓存获取）
        $routes2 = $this->routeCollector->collectRoutes();
        
        $this->assertEquals($routes1, $routes2);
        
        // 验证缓存统计
        $stats = $this->routeCollector->getCacheStats();
        $this->assertArrayHasKey('routes_cached', $stats);
        $this->assertArrayHasKey('controllers_cached', $stats);
    }

    /**
     * @test
     * @group cache
     */
    public function it_can_clear_cache(): void
    {
        // 收集路由以填充缓存
        $this->routeCollector->collectRoutes();
        
        // 清理缓存
        $result = $this->routeCollector->clearCache();
        
        $this->assertSame($this->routeCollector, $result);
        
        // 验证缓存已清空
        $stats = $this->routeCollector->getCacheStats();
        $this->assertEquals(0, $stats['routes_cached']);
        $this->assertEquals(0, $stats['controllers_cached']);
    }

    /**
     * @test
     * @group performance
     */
    public function it_has_acceptable_performance(): void
    {
        $start = microtime(true);
        
        // 执行多次路由收集
        for ($i = 0; $i < 100; $i++) {
            $this->routeCollector->clearCache();
            $this->routeCollector->collectRoutes();
        }
        
        $end = microtime(true);
        $totalTime = $end - $start;
        
        // 平均每次收集应该在合理时间内完成（这里设为100ms）
        $averageTime = $totalTime / 100;
        $this->assertLessThan(0.1, $averageTime, 'Route collection is too slow');
    }

    /**
     * @test
     * @group edge-cases
     */
    public function it_handles_controllers_without_routes(): void
    {
        // 创建没有路由注解的控制器
        $routes = $this->routeCollector->collectRoutes();
        
        // 不应该抛出异常
        $this->assertIsArray($routes);
    }

    /**
     * @test
     * @group statistics
     */
    public function it_provides_route_statistics(): void
    {
        $this->routeCollector->collectRoutes();
        
        $stats = $this->routeCollector->getRouteStats();
        
        $this->assertArrayHasKey('total_routes', $stats);
        $this->assertArrayHasKey('total_controllers', $stats);
        $this->assertArrayHasKey('methods_distribution', $stats);
        $this->assertArrayHasKey('path_patterns', $stats);
        
        $this->assertIsInt($stats['total_routes']);
        $this->assertIsInt($stats['total_controllers']);
        $this->assertIsArray($stats['methods_distribution']);
        $this->assertGreaterThan(0, $stats['total_routes']);
    }

    // ========== 辅助方法 ==========

    /**
     * 清理注解收集器
     */
    private function clearAnnotationCollector(): void
    {
        $reflection = new \ReflectionClass(AnnotationCollector::class);
        $container = $reflection->getProperty('container');
        $container->setAccessible(true);
        $container->setValue([]);
    }

    /**
     * 注册测试控制器注解
     */
    private function registerTestControllerAnnotations(): void
    {
        // 注册 TestApiController
        AnnotationCollector::collectClass(
            TestApiController::class,
            ApiController::class,
            new ApiController(prefix: '/test', tag: 'Test', description: '测试API控制器')
        );

        // 注册方法注解
        $this->registerMethodAnnotations(TestApiController::class);
        
        // 注册 RestfulController
        AnnotationCollector::collectClass(
            RestfulController::class,
            ApiController::class,
            new ApiController(description: 'RESTful测试控制器')
        );
        
        $this->registerMethodAnnotations(RestfulController::class);
    }

    /**
     * 注册方法注解
     */
    private function registerMethodAnnotations(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        
        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes();
            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                AnnotationCollector::collectMethod(
                    $className,
                    $method->getName(),
                    get_class($instance),
                    $instance
                );
            }
        }
    }

    /**
     * 根据动作名查找路由
     */
    private function findRouteByAction(array $routes, string $action): ?array
    {
        foreach ($routes as $route) {
            if ($route['action'] === $action) {
                return $route;
            }
        }
        
        return null;
    }
} 