<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Feature;

use HPlus\Route\Tests\Unit\AbstractTestCase;
use HPlus\Route\RouteCollector;
use HPlus\Route\Tests\Fixtures\TestApiController;
use HPlus\Route\Tests\Fixtures\RestfulController;
use Hyperf\Di\Annotation\AnnotationCollector;
use HPlus\Route\Annotation\ApiController;

/**
 * 路由收集功能的集成测试
 * 
 * @group feature
 * @group integration
 */
final class RouteCollectionFeatureTest extends AbstractTestCase
{
    private RouteCollector $routeCollector;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->setupAnnotationCollector();
        $this->routeCollector = RouteCollector::getInstance();
        $this->routeCollector->clearCache();
    }

    /**
     * @testdox 测试完整路由收集工作流 / Test complete route collection workflow
     * @test
     * @group complete-workflow
     */
    public function it_can_perform_complete_route_collection_workflow(): void
    {
        // 1. 收集路由
        $routes = $this->routeCollector->collectRoutes();
        
        $this->assertIsArray($routes);
        $this->assertNotEmpty($routes);
        
        // 2. 验证路由结构
        foreach ($routes as $route) {
            $this->assertArrayHasKey('path', $route);
            $this->assertArrayHasKey('methods', $route);
            $this->assertArrayHasKey('controller', $route);
            $this->assertArrayHasKey('action', $route);
            $this->assertArrayHasKey('summary', $route);
        }
        
        // 3. 验证缓存工作
        $cachedRoutes = $this->routeCollector->collectRoutes();
        $this->assertEquals($routes, $cachedRoutes);
        
        // 4. 验证搜索功能
        $testRoutes = $this->routeCollector->findRoutesByController(TestApiController::class);
        $this->assertNotEmpty($testRoutes);
        
        // 5. 验证统计功能
        $stats = $this->routeCollector->getRouteStats();
        $this->assertArrayHasKey('total_routes', $stats);
        $this->assertGreaterThan(0, $stats['total_routes']);
    }

    /**
     * @testdox 测试RESTful API完整生成 / Test complete RESTful API generation
     * @test
     * @group restful-api
     */
    public function it_generates_complete_restful_api(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $restfulRoutes = array_filter($routes, function ($route) {
            return $route['controller'] === RestfulController::class;
        });
        
        $this->assertNotEmpty($restfulRoutes);
        
        // 验证标准的CRUD操作都存在
        $actions = array_column($restfulRoutes, 'action');
        
        $expectedActions = ['index', 'show', 'create', 'store', 'update', 'delete', 'destroy'];
        foreach ($expectedActions as $action) {
            $this->assertContains($action, $actions, "Missing RESTful action: {$action}");
        }
        
        // 验证HTTP方法正确映射
        $methodMappings = [
            'index' => ['GET'],
            'show' => ['GET'],
            'create' => ['POST'],
            'store' => ['POST'],
            'update' => ['PUT'],
            'delete' => ['DELETE'],
            'destroy' => ['DELETE'],
        ];
        
        foreach ($restfulRoutes as $route) {
            if (isset($methodMappings[$route['action']])) {
                $this->assertEquals(
                    $methodMappings[$route['action']], 
                    $route['methods'],
                    "Incorrect HTTP method for action: {$route['action']}"
                );
            }
        }
    }

    /**
     * @testdox 测试不同场景下路径生成 / Test path generation for different scenarios
     * @test
     * @group path-generation
     */
    public function it_generates_correct_paths_for_different_scenarios(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        $testRoutes = array_filter($routes, function ($route) {
            return $route['controller'] === TestApiController::class;
        });
        
        $pathScenarios = [];
        foreach ($testRoutes as $route) {
            $pathScenarios[$route['action']] = $route['path'];
        }
        
        // 验证不同路径场景
        $this->assertStringStartsWith('/test', $pathScenarios['index']);
        $this->assertStringContainsString('{id}', $pathScenarios['show']);
        $this->assertStringEndsWith('/search', $pathScenarios['search']);
        $this->assertStringEndsWith('/batch', $pathScenarios['batch']);
    }

    /**
     * @testdox 测试路由元数据完整性 / Test completeness of route metadata
     * @test
     * @group route-metadata
     */
    public function it_includes_complete_route_metadata(): void
    {
        $routes = $this->routeCollector->collectRoutes();
        
        foreach ($routes as $route) {
            // 验证基础元数据
            $this->assertNotEmpty($route['controller']);
            $this->assertNotEmpty($route['action']);
            $this->assertNotEmpty($route['methods']);
            $this->assertNotEmpty($route['path']);
            
            // 验证可选元数据存在
            $this->assertArrayHasKey('summary', $route);
            $this->assertArrayHasKey('description', $route);
            $this->assertArrayHasKey('tags', $route);
            $this->assertArrayHasKey('security', $route);
            $this->assertArrayHasKey('userOpen', $route);
            
            // 验证数据类型
            $this->assertIsString($route['controller']);
            $this->assertIsString($route['action']);
            $this->assertIsArray($route['methods']);
            $this->assertIsString($route['path']);
            $this->assertIsArray($route['tags']);
            $this->assertIsBool($route['security']);
            $this->assertIsBool($route['userOpen']);
        }
    }

    /**
     * @testdox 测试多控制器环境下的性能 / Test performance with multiple controllers
     * @test
     * @group performance-integration
     */
    public function it_performs_well_with_multiple_controllers(): void
    {
        $start = microtime(true);
        
        // 模拟多次收集（实际项目中的情况）
        for ($i = 0; $i < 10; $i++) {
            $routes = $this->routeCollector->collectRoutes();
            $this->assertNotEmpty($routes);
            
            // 执行一些查询操作
            $this->routeCollector->findRoutesByMethod('GET');
            $this->routeCollector->findRoutesByController(TestApiController::class);
            $this->routeCollector->getRouteStats();
        }
        
        $end = microtime(true);
        $totalTime = $end - $start;
        
        // 整个流程应该在合理时间内完成
        $this->assertLessThan(1.0, $totalTime, 'Route collection workflow is too slow');
    }

    /**
     * @testdox 测试缓存行为 / Test caching behavior
     * @test
     * @group caching-behavior
     */
    public function it_demonstrates_effective_caching(): void
    {
        // 第一次收集（冷启动）
        $start1 = microtime(true);
        $routes1 = $this->routeCollector->collectRoutes();
        $time1 = microtime(true) - $start1;
        
        // 第二次收集（从缓存）
        $start2 = microtime(true);
        $routes2 = $this->routeCollector->collectRoutes();
        $time2 = microtime(true) - $start2;
        
        // 验证结果相同
        $this->assertEquals($routes1, $routes2);
        
        // 验证缓存效果（第二次应该更快）
        $this->assertLessThan($time1, $time2, 'Cache should make subsequent calls faster');
        
        // 验证缓存统计
        $stats = $this->routeCollector->getCacheStats();
        $this->assertGreaterThan(0, $stats['routes_cached']);
    }

    /**
     * @testdox 测试边界情况处理 / Test edge case handling
     * @test
     * @group edge-cases
     */
    public function it_handles_edge_cases_gracefully(): void
    {
        // 清空注解收集器
        $this->clearAnnotationCollector();
        
        // 即使没有注解，也应该正常工作
        $routes = $this->routeCollector->collectRoutes();
        $this->assertIsArray($routes);
        
        // 搜索不存在的路由
        $result = $this->routeCollector->findRouteByPath('/nonexistent');
        $this->assertNull($result);
        
        // 搜索不存在的控制器
        $result = $this->routeCollector->findRoutesByController('NonExistentController');
        $this->assertEmpty($result);
        
        // 搜索不存在的HTTP方法
        $result = $this->routeCollector->findRoutesByMethod('INVALID');
        $this->assertEmpty($result);
    }

    // ========== 辅助方法 ==========

    private function setupAnnotationCollector(): void
    {
        $this->clearAnnotationCollector();
        $this->registerTestAnnotations();
    }

    private function clearAnnotationCollector(): void
    {
        $reflection = new \ReflectionClass(AnnotationCollector::class);
        $container = $reflection->getProperty('container');
        $container->setAccessible(true);
        $container->setValue([]);
    }

    private function registerTestAnnotations(): void
    {
        // 注册 TestApiController
        AnnotationCollector::collectClass(
            TestApiController::class,
            ApiController::class,
            new ApiController(prefix: '/test', tag: 'Test', description: '测试API控制器')
        );

        // 注册 RestfulController
        AnnotationCollector::collectClass(
            RestfulController::class,
            ApiController::class,
            new ApiController(description: 'RESTful测试控制器')
        );

        // 注册方法注解
        $this->registerMethodAnnotations(TestApiController::class);
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
} 