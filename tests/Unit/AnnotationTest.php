<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit;

use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;
use HPlus\Route\Annotation\PatchApi;
use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\Controller;
use HPlus\Route\Annotation\Mapping;

/**
 * 注解系统测试类
 * 
 * @group annotations
 */
final class AnnotationTest extends AbstractTestCase
{
    /**
     * @test
     * @group api-controller
     */
    public function it_can_create_api_controller_annotation(): void
    {
        $annotation = new ApiController(
            prefix: '/api/v1',
            tag: 'Users',
            description: '用户管理API',
            userOpen: true,
            security: false
        );

        $this->assertEquals('/api/v1', $annotation->prefix);
        $this->assertEquals('Users', $annotation->tag);
        $this->assertEquals('用户管理API', $annotation->description);
        $this->assertTrue($annotation->userOpen);
        $this->assertFalse($annotation->security);
    }

    /**
     * @test
     * @group api-controller
     */
    public function it_has_default_values_for_api_controller(): void
    {
        $annotation = new ApiController();

        $this->assertEquals('', $annotation->prefix);
        $this->assertEquals('http', $annotation->server);
        $this->assertEquals([], $annotation->options);
        $this->assertEquals([], $annotation->ignore);
        $this->assertEquals([], $annotation->generate);
        $this->assertNull($annotation->service);
        $this->assertNull($annotation->tag);
        $this->assertNull($annotation->description);
        $this->assertFalse($annotation->userOpen);
        $this->assertTrue($annotation->security);
    }

    /**
     * @test
     * @group controller
     */
    public function it_can_create_controller_annotation(): void
    {
        $annotation = new Controller(
            prefix: '/admin',
            server: 'http',
            options: ['middleware' => ['auth']],
            ignore: ['hidden'],
            generate: ['docs'],
            service: 'user-service',
            tag: 'Admin',
            description: '管理后台',
            userOpen: false,
            security: true
        );

        $this->assertEquals('/admin', $annotation->prefix);
        $this->assertEquals('http', $annotation->server);
        $this->assertEquals(['middleware' => ['auth']], $annotation->options);
        $this->assertEquals(['hidden'], $annotation->ignore);
        $this->assertEquals(['docs'], $annotation->generate);
        $this->assertEquals('user-service', $annotation->service);
        $this->assertEquals('Admin', $annotation->tag);
        $this->assertEquals('管理后台', $annotation->description);
        $this->assertFalse($annotation->userOpen);
        $this->assertTrue($annotation->security);
    }

    /**
     * @test
     * @group get-api
     */
    public function it_can_create_get_api_annotation(): void
    {
        $annotation = new GetApi(
            path: '/users/{id}',
            summary: '获取用户详情',
            description: '根据用户ID获取用户详细信息',
            deprecated: null,
            security: true,
            userOpen: false
        );

        $this->assertEquals('/users/{id}', $annotation->path);
        $this->assertEquals('获取用户详情', $annotation->summary);
        $this->assertEquals('根据用户ID获取用户详细信息', $annotation->description);
        $this->assertNull($annotation->deprecated);
        $this->assertTrue($annotation->security);
        $this->assertFalse($annotation->userOpen);
        $this->assertEquals(['GET'], $annotation->methods);
    }

    /**
     * @test
     * @group get-api
     */
    public function it_has_default_values_for_get_api(): void
    {
        $annotation = new GetApi();

        $this->assertNull($annotation->path);
        $this->assertNull($annotation->summary);
        $this->assertNull($annotation->description);
        $this->assertNull($annotation->deprecated);
        $this->assertTrue($annotation->security);
        $this->assertTrue($annotation->userOpen);
        $this->assertEquals(['GET'], $annotation->methods);
        $this->assertEquals([], $annotation->options);
    }

    /**
     * @test
     * @group post-api
     */
    public function it_can_create_post_api_annotation(): void
    {
        $annotation = new PostApi(
            path: '/users',
            summary: '创建用户',
            description: '创建新用户账户',
            security: false
        );

        $this->assertEquals('/users', $annotation->path);
        $this->assertEquals('创建用户', $annotation->summary);
        $this->assertEquals('创建新用户账户', $annotation->description);
        $this->assertFalse($annotation->security);
        $this->assertEquals(['POST'], $annotation->methods);
    }

    /**
     * @test
     * @group put-api
     */
    public function it_can_create_put_api_annotation(): void
    {
        $annotation = new PutApi(
            path: '/users/{id}',
            summary: '更新用户',
            description: '更新用户完整信息'
        );

        $this->assertEquals('/users/{id}', $annotation->path);
        $this->assertEquals('更新用户', $annotation->summary);
        $this->assertEquals('更新用户完整信息', $annotation->description);
        $this->assertEquals(['PUT'], $annotation->methods);
    }

    /**
     * @test
     * @group patch-api
     */
    public function it_can_create_patch_api_annotation(): void
    {
        $annotation = new PatchApi(
            path: '/users/{id}',
            summary: '部分更新用户',
            description: '部分更新用户信息'
        );

        $this->assertEquals('/users/{id}', $annotation->path);
        $this->assertEquals('部分更新用户', $annotation->summary);
        $this->assertEquals('部分更新用户信息', $annotation->description);
        $this->assertEquals(['PATCH'], $annotation->methods);
    }

    /**
     * @test
     * @group delete-api
     */
    public function it_can_create_delete_api_annotation(): void
    {
        $annotation = new DeleteApi(
            path: '/users/{id}',
            summary: '删除用户',
            description: '删除指定用户'
        );

        $this->assertEquals('/users/{id}', $annotation->path);
        $this->assertEquals('删除用户', $annotation->summary);
        $this->assertEquals('删除指定用户', $annotation->description);
        $this->assertEquals(['DELETE'], $annotation->methods);
    }

    /**
     * @test
     * @group mapping
     */
    public function it_can_create_mapping_annotation(): void
    {
        $mapping = new class extends Mapping {
            public array $methods = ['CUSTOM'];
        };

        $annotation = new $mapping(
            path: '/custom',
            summary: '自定义接口',
            description: '自定义HTTP方法接口',
            deprecated: '1.0.0',
            security: false,
            userOpen: true,
            options: ['custom' => 'value']
        );

        $this->assertEquals('/custom', $annotation->path);
        $this->assertEquals('自定义接口', $annotation->summary);
        $this->assertEquals('自定义HTTP方法接口', $annotation->description);
        $this->assertEquals('1.0.0', $annotation->deprecated);
        $this->assertFalse($annotation->security);
        $this->assertTrue($annotation->userOpen);
        $this->assertEquals(['custom' => 'value'], $annotation->options);
        $this->assertEquals(['CUSTOM'], $annotation->methods);
    }

    /**
     * @test
     * @group inheritance
     */
    public function it_respects_inheritance_hierarchy(): void
    {
        // ApiController 继承自 Controller
        $apiController = new ApiController();
        $this->assertInstanceOf(Controller::class, $apiController);

        // HTTP方法注解都继承自 Mapping
        $getApi = new GetApi();
        $postApi = new PostApi();
        $putApi = new PutApi();
        $patchApi = new PatchApi();
        $deleteApi = new DeleteApi();

        $this->assertInstanceOf(Mapping::class, $getApi);
        $this->assertInstanceOf(Mapping::class, $postApi);
        $this->assertInstanceOf(Mapping::class, $putApi);
        $this->assertInstanceOf(Mapping::class, $patchApi);
        $this->assertInstanceOf(Mapping::class, $deleteApi);
    }

    /**
     * @test
     * @group http-methods
     */
    public function it_has_correct_http_methods_for_each_annotation(): void
    {
        $annotationsData = [
            [new GetApi(), ['GET']],
            [new PostApi(), ['POST']],
            [new PutApi(), ['PUT']],
            [new PatchApi(), ['PATCH']],
            [new DeleteApi(), ['DELETE']],
        ];

        foreach ($annotationsData as [$annotation, $expectedMethods]) {
            $this->assertEquals($expectedMethods, $annotation->methods);
        }
    }

    /**
     * @test
     * @group attributes
     */
    public function it_can_be_used_as_php_attributes(): void
    {
        // 验证注解类都有正确的Attribute声明
        $reflectionClasses = [
            ApiController::class,
            Controller::class,
            GetApi::class,
            PostApi::class,
            PutApi::class,
            PatchApi::class,
            DeleteApi::class,
        ];

        foreach ($reflectionClasses as $className) {
            $reflection = new \ReflectionClass($className);
            $attributes = $reflection->getAttributes(\Attribute::class);
            
            $this->assertNotEmpty($attributes, "{$className} should have Attribute declaration");
        }
    }

    /**
     * @test
     * @group dynamic-properties
     */
    public function it_supports_dynamic_property_assignment(): void
    {
        $annotation = new GetApi();
        
        // 测试动态属性设置（通过构造函数参数）
        $annotation2 = new GetApi(
            path: '/dynamic',
            summary: 'Dynamic API',
            options: ['timeout' => 30]
        );

        $this->assertEquals('/dynamic', $annotation2->path);
        $this->assertEquals('Dynamic API', $annotation2->summary);
        $this->assertEquals(['timeout' => 30], $annotation2->options);
    }

    /**
     * @test
     * @group validation
     */
    public function it_validates_annotation_properties(): void
    {
        // 测试路径格式（以 / 开头）
        $annotation = new GetApi(path: 'invalid-path');
        $this->assertEquals('invalid-path', $annotation->path); // 注解本身不做验证，由路由收集器验证

        // 测试布尔值
        $annotation = new GetApi(security: false, userOpen: true);
        $this->assertFalse($annotation->security);
        $this->assertTrue($annotation->userOpen);

        // 测试数组属性
        $annotation = new GetApi(options: ['key' => 'value']);
        $this->assertEquals(['key' => 'value'], $annotation->options);
    }
} 