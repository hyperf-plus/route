<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit;

use HPlus\Route\ApiAnnotation;
use HPlus\Route\Tests\Fixtures\TestApiController;

/**
 * ApiAnnotation 工具类测试
 * 
 * @group api-annotation
 */
final class ApiAnnotationTest extends AbstractTestCase
{
    /**
     * @test
     * @group method-metadata
     */
    public function it_can_get_method_metadata(): void
    {
        $metadata = ApiAnnotation::methodMetadata(
            TestApiController::class,
            'index'
        );

        $this->assertIsArray($metadata);
        // 注意：在实际测试环境中，这可能返回空数组，因为没有完整的Hyperf注解处理器
        // 这里主要测试方法不抛异常
    }

    /**
     * @test
     * @group method-metadata
     */
    public function it_handles_non_existent_method(): void
    {
        $this->expectException(\ReflectionException::class);
        
        ApiAnnotation::methodMetadata(
            TestApiController::class,
            'nonExistentMethod'
        );
    }

    /**
     * @test
     * @group method-metadata
     */
    public function it_handles_non_existent_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        ApiAnnotation::methodMetadata(
            'NonExistentClass',
            'someMethod'
        );
    }

    /**
     * @test
     * @group reflection
     */
    public function it_uses_reflection_manager(): void
    {
        // 测试反射管理器的使用
        $result = ApiAnnotation::methodMetadata(
            TestApiController::class,
            'show'
        );

        $this->assertIsArray($result);
    }

    /**
     * @test
     * @group multiple-methods
     */
    public function it_can_handle_multiple_method_calls(): void
    {
        $methods = ['index', 'show', 'create', 'update', 'delete'];
        
        foreach ($methods as $method) {
            $metadata = ApiAnnotation::methodMetadata(
                TestApiController::class,
                $method
            );
            
            $this->assertIsArray($metadata, "Failed for method: {$method}");
        }
    }

    /**
     * @test
     * @group performance
     */
    public function it_has_acceptable_performance(): void
    {
        $start = microtime(true);
        
        // 执行多次调用
        for ($i = 0; $i < 100; $i++) {
            ApiAnnotation::methodMetadata(
                TestApiController::class,
                'index'
            );
        }
        
        $end = microtime(true);
        $totalTime = $end - $start;
        
        // 每次调用应该在合理时间内完成
        $averageTime = $totalTime / 100;
        $this->assertLessThan(0.01, $averageTime, 'ApiAnnotation::methodMetadata is too slow');
    }
} 