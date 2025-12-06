<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit;

use HPlus\Route\DispatcherFactory;

/**
 * DispatcherFactory 单元测试
 * 
 * 测试路径生成逻辑
 * 注：字符串转换方法（camelToKebab, pluralize）已移至 Helper\StringHelper，
 * 相关测试请参见 Helper\StringHelperTest
 */
final class DispatcherFactoryTest extends AbstractTestCase
{
    private DispatcherFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 使用反射创建实例（绕过构造函数依赖）
        $reflection = new \ReflectionClass(DispatcherFactory::class);
        $this->factory = $reflection->newInstanceWithoutConstructor();
    }

    // ========== getRestfulPath 测试 ==========

    /**
     * @test
     */
    public function it_generates_list_path(): void
    {
        $method = $this->getPrivateMethod($this->factory, 'getRestfulPath');
        
        // index -> GET /prefix
        $this->assertEquals('/users', $method->invoke($this->factory, 'index', 'GET', '/users'));
        $this->assertEquals('/posts', $method->invoke($this->factory, 'list', 'GET', '/posts'));
    }

    /**
     * @test
     */
    public function it_generates_show_path(): void
    {
        $method = $this->getPrivateMethod($this->factory, 'getRestfulPath');
        
        // show -> GET /prefix/{id}
        $this->assertEquals('/users/{id}', $method->invoke($this->factory, 'show', 'GET', '/users'));
        $this->assertEquals('/posts/{id}', $method->invoke($this->factory, 'detail', 'GET', '/posts'));
        $this->assertEquals('/comments/{id}', $method->invoke($this->factory, 'get', 'GET', '/comments'));
    }

    /**
     * @test
     */
    public function it_generates_create_path(): void
    {
        $method = $this->getPrivateMethod($this->factory, 'getRestfulPath');
        
        // create -> POST /prefix
        $this->assertEquals('/users', $method->invoke($this->factory, 'create', 'POST', '/users'));
        $this->assertEquals('/posts', $method->invoke($this->factory, 'store', 'POST', '/posts'));
        $this->assertEquals('/comments', $method->invoke($this->factory, 'add', 'POST', '/comments'));
    }

    /**
     * @test
     */
    public function it_generates_update_path(): void
    {
        $method = $this->getPrivateMethod($this->factory, 'getRestfulPath');
        
        // update -> PUT /prefix/{id}
        $this->assertEquals('/users/{id}', $method->invoke($this->factory, 'update', 'PUT', '/users'));
        $this->assertEquals('/posts/{id}', $method->invoke($this->factory, 'edit', 'PUT', '/posts'));
        $this->assertEquals('/comments/{id}', $method->invoke($this->factory, 'modify', 'PUT', '/comments'));
        $this->assertEquals('/articles/{id}', $method->invoke($this->factory, 'patch', 'PATCH', '/articles'));
    }

    /**
     * @test
     */
    public function it_generates_delete_path(): void
    {
        $method = $this->getPrivateMethod($this->factory, 'getRestfulPath');
        
        // delete -> DELETE /prefix/{id}
        $this->assertEquals('/users/{id}', $method->invoke($this->factory, 'delete', 'DELETE', '/users'));
        $this->assertEquals('/posts/{id}', $method->invoke($this->factory, 'destroy', 'DELETE', '/posts'));
        $this->assertEquals('/comments/{id}', $method->invoke($this->factory, 'remove', 'DELETE', '/comments'));
    }

    /**
     * @test
     */
    public function it_generates_kebab_case_path_for_non_restful_methods(): void
    {
        $method = $this->getPrivateMethod($this->factory, 'getRestfulPath');
        
        // 非 RESTful 方法 -> /prefix/method-name
        $this->assertEquals('/users/get-user-profile', $method->invoke($this->factory, 'getUserProfile', 'GET', '/users'));
        $this->assertEquals('/posts/batch-update', $method->invoke($this->factory, 'batchUpdate', 'POST', '/posts'));
        $this->assertEquals('/users/export-data', $method->invoke($this->factory, 'exportData', 'GET', '/users'));
        $this->assertEquals('/users/import-users', $method->invoke($this->factory, 'importUsers', 'POST', '/users'));
    }

    /**
     * @test
     */
    public function it_falls_back_for_wrong_http_method(): void
    {
        $method = $this->getPrivateMethod($this->factory, 'getRestfulPath');
        
        // index 应该是 GET，但如果用了 POST，则回退到方法名
        $this->assertEquals('/users/index', $method->invoke($this->factory, 'index', 'POST', '/users'));
        
        // show 应该是 GET，但如果用了 DELETE，则回退到方法名
        $this->assertEquals('/users/show', $method->invoke($this->factory, 'show', 'DELETE', '/users'));
    }
}
