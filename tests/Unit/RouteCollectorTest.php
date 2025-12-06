<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit;

use HPlus\Route\RouteCollector;
use HPlus\Route\Tests\Fixtures\TestApiController;
use HPlus\Route\Tests\Fixtures\RestfulController;
use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;
use HPlus\Route\Annotation\PatchApi;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * RouteCollector 单元测试
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
        
        // 获取路由收集器实例并清理缓存
        $this->routeCollector = RouteCollector::getInstance();
        $this->routeCollector->clearCache();
    }

    protected function tearDown(): void
    {
        $this->routeCollector->clearCache();
        parent::tearDown();
    }

    // ========== 单例模式测试 ==========

    /**
     * @test
     */
    public function it_should_be_singleton(): void
    {
        $instance1 = RouteCollector::getInstance();
        $instance2 = RouteCollector::getInstance();
        
        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(RouteCollector::class, $instance1);
    }

    // ========== 路由收集测试 ==========

    /**
     * @test
     */
    public function it_can_collect_api_controller_routes(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $this->assertIsArray($routes);
        $this->assertNotEmpty($routes);
        
        // 验证包含测试控制器路由
        $testControllerRoutes = array_filter($routes, fn($route) => 
            $route['controller'] === TestApiController::class
        );
        
        $this->assertNotEmpty($testControllerRoutes, 'TestApiController routes not found');
    }

    /**
     * @test
     */
    public function it_generates_correct_route_format(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        foreach ($routes as $route) {
            // 验证必需字段
            $this->assertArrayHasKey('path', $route);
            $this->assertArrayHasKey('methods', $route);
            $this->assertArrayHasKey('controller', $route);
            $this->assertArrayHasKey('action', $route);
            $this->assertArrayHasKey('name', $route);
            $this->assertArrayHasKey('summary', $route);
            $this->assertArrayHasKey('tags', $route);
            $this->assertArrayHasKey('security', $route);
            
            // 验证类型
            $this->assertIsString($route['path']);
            $this->assertIsArray($route['methods']);
            $this->assertIsString($route['controller']);
            $this->assertIsString($route['action']);
            
            // 验证路径格式
            $this->assertStringStartsWith('/', $route['path']);
            
            // 验证控制器和方法存在
            $this->assertTrue(class_exists($route['controller']));
            $this->assertTrue(method_exists($route['controller'], $route['action']));
        }
    }

    // ========== 路径生成测试 ==========

    /**
     * @test
     */
    public function it_handles_explicit_path_correctly(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $testRoutes = array_filter($routes, fn($route) => 
            $route['controller'] === TestApiController::class
        );
        
        // 验证带参数的路由
        $showRoute = $this->findRouteByAction($testRoutes, 'show');
        $this->assertNotNull($showRoute);
        $this->assertStringContainsString('{id}', $showRoute['path']);
        $this->assertEquals(['GET'], $showRoute['methods']);
        
        // 验证搜索路由
        $searchRoute = $this->findRouteByAction($testRoutes, 'search');
        $this->assertNotNull($searchRoute);
        $this->assertStringContainsString('/search', $searchRoute['path']);
    }

    /**
     * @test
     */
    public function it_applies_controller_prefix_correctly(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $testRoutes = array_filter($routes, fn($route) => 
            $route['controller'] === TestApiController::class
        );
        
        foreach ($testRoutes as $route) {
            $this->assertStringStartsWith('/test', $route['path']);
        }
    }

    /**
     * @test
     */
    public function it_generates_kebab_case_paths(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        // 非 RESTful 方法应该转为 kebab-case
        $restfulRoutes = array_filter($routes, fn($route) => 
            $route['controller'] === RestfulController::class
        );
        
        $searchRoute = $this->findRouteByAction($restfulRoutes, 'search');
        $exportRoute = $this->findRouteByAction($restfulRoutes, 'export');
        
        // search 和 export 不在标准 RESTful 映射中，应该转为方法名路径
        $this->assertNotNull($searchRoute);
        $this->assertNotNull($exportRoute);
        $this->assertStringContainsString('/search', $searchRoute['path']);
        $this->assertStringContainsString('/export', $exportRoute['path']);
    }

    // ========== RESTful 映射测试 ==========

    /**
     * @test
     */
    public function it_generates_restful_routes_correctly(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $restfulRoutes = array_filter($routes, fn($route) => 
            $route['controller'] === RestfulController::class
        );
        
        $this->assertNotEmpty($restfulRoutes);
        
        // index -> GET /prefix
        $indexRoute = $this->findRouteByAction($restfulRoutes, 'index');
        $this->assertNotNull($indexRoute);
        $this->assertEquals(['GET'], $indexRoute['methods']);
        $this->assertStringNotContainsString('{id}', $indexRoute['path']);
        
        // show -> GET /prefix/{id}
        $showRoute = $this->findRouteByAction($restfulRoutes, 'show');
        $this->assertNotNull($showRoute);
        $this->assertEquals(['GET'], $showRoute['methods']);
        $this->assertStringContainsString('{id}', $showRoute['path']);
        
        // create -> POST /prefix
        $createRoute = $this->findRouteByAction($restfulRoutes, 'create');
        $this->assertNotNull($createRoute);
        $this->assertEquals(['POST'], $createRoute['methods']);
        
        // store -> POST /prefix
        $storeRoute = $this->findRouteByAction($restfulRoutes, 'store');
        $this->assertNotNull($storeRoute);
        $this->assertEquals(['POST'], $storeRoute['methods']);
        
        // update -> PUT /prefix/{id}
        $updateRoute = $this->findRouteByAction($restfulRoutes, 'update');
        $this->assertNotNull($updateRoute);
        $this->assertEquals(['PUT'], $updateRoute['methods']);
        $this->assertStringContainsString('{id}', $updateRoute['path']);
        
        // delete -> DELETE /prefix/{id}
        $deleteRoute = $this->findRouteByAction($restfulRoutes, 'delete');
        $this->assertNotNull($deleteRoute);
        $this->assertEquals(['DELETE'], $deleteRoute['methods']);
        $this->assertStringContainsString('{id}', $deleteRoute['path']);
    }

    /**
     * @test
     */
    public function it_identifies_restful_routes(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $restfulRoutes = $this->routeCollector->getRestfulRoutes();
        
        $this->assertIsArray($restfulRoutes);
        
        // 验证标记为 restful 的路由
        foreach ($restfulRoutes as $route) {
            $this->assertTrue($route['restful'] ?? false);
        }
    }

    // ========== 查询方法测试 ==========

    /**
     * @test
     */
    public function it_can_find_routes_by_path(): void
    {
        $this->routeCollector->collectRoutes();
        
        $route = $this->routeCollector->findRouteByPath('/test');
        $this->assertNotNull($route);
        $this->assertEquals(TestApiController::class, $route['controller']);
        
        $route = $this->routeCollector->findRouteByPath('/test/{id}');
        $this->assertNotNull($route);
    }

    /**
     * @test
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
     */
    public function it_can_find_routes_by_tag(): void
    {
        $this->routeCollector->collectRoutes();
        
        $routes = $this->routeCollector->findRoutesByTag('Test');
        $this->assertNotEmpty($routes);
        
        foreach ($routes as $route) {
            $this->assertContains('Test', $route['tags']);
        }
    }

    /**
     * @test
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
        
        $putRoutes = $this->routeCollector->findRoutesByMethod('PUT');
        $this->assertNotEmpty($putRoutes);
        
        $deleteRoutes = $this->routeCollector->findRoutesByMethod('DELETE');
        $this->assertNotEmpty($deleteRoutes);
    }

    /**
     * @test
     */
    public function it_can_get_all_paths(): void
    {
        $this->routeCollector->collectRoutes();
        
        $paths = $this->routeCollector->getAllPaths();
        
        $this->assertIsArray($paths);
        $this->assertNotEmpty($paths);
        
        foreach ($paths as $path) {
            $this->assertIsString($path);
            $this->assertStringStartsWith('/', $path);
        }
    }

    // ========== 缓存测试 ==========

    /**
     * @test
     */
    public function it_caches_routes_correctly(): void
    {
        // 第一次收集
        $routes1 = $this->routeCollector->collectRoutes();
        
        // 第二次收集（应该从缓存获取）
        $routes2 = $this->routeCollector->collectRoutes();
        
        $this->assertEquals($routes1, $routes2);
    }

    /**
     * @test
     */
    public function it_can_clear_cache(): void
    {
        // 收集路由
        $routes1 = $this->routeCollector->collectRoutes();
        
        // 清理缓存
        $result = $this->routeCollector->clearCache();
        
        $this->assertSame($this->routeCollector, $result);
        
        // 再次收集（应该重新收集）
        $routes2 = $this->routeCollector->collectRoutes();
        
        $this->assertEquals($routes1, $routes2);
    }

    // ========== 边界情况测试 ==========

    /**
     * @test
     */
    public function it_returns_null_for_nonexistent_path(): void
    {
        $this->routeCollector->collectRoutes();
        
        $route = $this->routeCollector->findRouteByPath('/nonexistent/path');
        $this->assertNull($route);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_for_nonexistent_controller(): void
    {
        $this->routeCollector->collectRoutes();
        
        $routes = $this->routeCollector->findRoutesByController('NonExistent\\Controller');
        $this->assertIsArray($routes);
        $this->assertEmpty($routes);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_for_nonexistent_tag(): void
    {
        $this->routeCollector->collectRoutes();
        
        $routes = $this->routeCollector->findRoutesByTag('NonExistentTag');
        $this->assertIsArray($routes);
        $this->assertEmpty($routes);
    }

    // ========== 性能测试 ==========

    /**
     * @test
     */
    public function it_has_acceptable_performance(): void
    {
        $start = microtime(true);
        
        for ($i = 0; $i < 100; $i++) {
            $this->routeCollector->clearCache();
            $this->routeCollector->collectRoutes();
        }
        
        $end = microtime(true);
        $averageTime = ($end - $start) / 100;
        
        // 平均每次应该小于 50ms
        $this->assertLessThan(0.05, $averageTime, 'Route collection is too slow');
    }

    // ========== 辅助方法 ==========

    private function clearAnnotationCollector(): void
    {
        $reflection = new \ReflectionClass(AnnotationCollector::class);
        $container = $reflection->getProperty('container');
        $container->setAccessible(true);
        $container->setValue([]);
    }

    private function registerTestControllerAnnotations(): void
    {
        // 注册 TestApiController
        AnnotationCollector::collectClass(
            TestApiController::class,
            ApiController::class,
            new ApiController(prefix: '/test', tag: 'Test', description: '测试API控制器')
        );
        $this->registerMethodAnnotations(TestApiController::class);
        
        // 注册 RestfulController
        AnnotationCollector::collectClass(
            RestfulController::class,
            ApiController::class,
            new ApiController(description: 'RESTful测试控制器')
        );
        $this->registerMethodAnnotations(RestfulController::class);
    }

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
