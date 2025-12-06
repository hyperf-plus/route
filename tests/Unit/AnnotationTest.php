<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit;

use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\AdminController;
use HPlus\Route\Annotation\Controller;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;
use HPlus\Route\Annotation\PatchApi;
use HPlus\Route\Annotation\Mapping;

/**
 * 注解类单元测试
 */
final class AnnotationTest extends AbstractTestCase
{
    // ========== Controller 注解测试 ==========

    /**
     * @test
     */
    public function controller_annotation_has_correct_defaults(): void
    {
        $annotation = new Controller();
        
        $this->assertEquals('', $annotation->prefix);
        $this->assertEquals('http', $annotation->server);
        $this->assertEquals([], $annotation->options);
        $this->assertNull($annotation->service);
        $this->assertNull($annotation->tag);
        $this->assertNull($annotation->description);
        $this->assertFalse($annotation->userOpen);
        $this->assertTrue($annotation->security);
    }

    /**
     * @test
     */
    public function controller_annotation_accepts_custom_values(): void
    {
        $annotation = new Controller(
            prefix: '/api/v1',
            server: 'custom',
            options: ['key' => 'value'],
            service: 'user-service',
            tag: 'Users',
            description: 'User API',
            userOpen: true,
            security: false
        );
        
        $this->assertEquals('/api/v1', $annotation->prefix);
        $this->assertEquals('custom', $annotation->server);
        $this->assertEquals(['key' => 'value'], $annotation->options);
        $this->assertEquals('user-service', $annotation->service);
        $this->assertEquals('Users', $annotation->tag);
        $this->assertEquals('User API', $annotation->description);
        $this->assertTrue($annotation->userOpen);
        $this->assertFalse($annotation->security);
    }

    // ========== ApiController 注解测试 ==========

    /**
     * @test
     */
    public function api_controller_extends_controller(): void
    {
        $annotation = new ApiController();
        
        $this->assertInstanceOf(Controller::class, $annotation);
    }

    /**
     * @test
     */
    public function api_controller_inherits_defaults(): void
    {
        $annotation = new ApiController();
        
        $this->assertEquals('', $annotation->prefix);
        $this->assertEquals('http', $annotation->server);
        $this->assertTrue($annotation->security);
    }

    // ========== AdminController 注解测试 ==========

    /**
     * @test
     */
    public function admin_controller_extends_controller(): void
    {
        $annotation = new AdminController();
        
        $this->assertInstanceOf(Controller::class, $annotation);
    }

    // ========== HTTP 方法注解测试 ==========

    /**
     * @test
     */
    public function get_api_has_correct_method(): void
    {
        $annotation = new GetApi();
        
        $this->assertEquals(['GET'], $annotation->methods);
    }

    /**
     * @test
     */
    public function post_api_has_correct_method(): void
    {
        $annotation = new PostApi();
        
        $this->assertEquals(['POST'], $annotation->methods);
    }

    /**
     * @test
     */
    public function put_api_has_correct_method(): void
    {
        $annotation = new PutApi();
        
        $this->assertEquals(['PUT'], $annotation->methods);
    }

    /**
     * @test
     */
    public function delete_api_has_correct_method(): void
    {
        $annotation = new DeleteApi();
        
        $this->assertEquals(['DELETE'], $annotation->methods);
    }

    /**
     * @test
     */
    public function patch_api_has_correct_method(): void
    {
        $annotation = new PatchApi();
        
        $this->assertEquals(['PATCH'], $annotation->methods);
    }

    // ========== Mapping 注解属性测试 ==========

    /**
     * @test
     */
    public function mapping_annotation_has_correct_defaults(): void
    {
        $annotation = new GetApi();
        
        $this->assertNull($annotation->path);
        $this->assertNull($annotation->summary);
        $this->assertNull($annotation->description);
        $this->assertFalse($annotation->deprecated);
        $this->assertTrue($annotation->security);
        $this->assertTrue($annotation->userOpen);
        $this->assertEquals([], $annotation->options);
        $this->assertNull($annotation->name);
        $this->assertEquals([], $annotation->middleware);
    }

    /**
     * @test
     */
    public function mapping_annotation_accepts_custom_values(): void
    {
        $annotation = new GetApi(
            path: '/users/{id}',
            summary: '获取用户详情',
            description: '根据ID获取用户信息',
            deprecated: true,
            security: false,
            userOpen: false,
            options: ['timeout' => 30],
            name: 'user.show',
            middleware: ['auth']
        );
        
        $this->assertEquals('/users/{id}', $annotation->path);
        $this->assertEquals('获取用户详情', $annotation->summary);
        $this->assertEquals('根据ID获取用户信息', $annotation->description);
        $this->assertTrue($annotation->deprecated);
        $this->assertFalse($annotation->security);
        $this->assertFalse($annotation->userOpen);
        $this->assertEquals(['timeout' => 30], $annotation->options);
        $this->assertEquals('user.show', $annotation->name);
        $this->assertEquals(['auth'], $annotation->middleware);
    }

    // ========== PHP Attribute 测试 ==========

    /**
     * @test
     */
    public function annotations_are_valid_attributes(): void
    {
        // 验证类注解
        $reflection = new \ReflectionClass(ApiController::class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        $this->assertNotEmpty($attributes);
        
        // 验证方法注解
        $reflection = new \ReflectionClass(GetApi::class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        $this->assertNotEmpty($attributes);
    }

    /**
     * @test
     */
    public function controller_attribute_targets_class(): void
    {
        $reflection = new \ReflectionClass(ApiController::class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        
        // Attribute::TARGET_CLASS = 1
        $this->assertEquals(\Attribute::TARGET_CLASS, $attr->flags & \Attribute::TARGET_CLASS);
    }

    /**
     * @test
     */
    public function mapping_attribute_targets_method(): void
    {
        $reflection = new \ReflectionClass(GetApi::class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        
        // Attribute::TARGET_METHOD = 2
        $this->assertEquals(\Attribute::TARGET_METHOD, $attr->flags & \Attribute::TARGET_METHOD);
    }
}
