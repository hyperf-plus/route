<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Mockery;

abstract class AbstractTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 创建 Mockery Mock 对象
     */
    protected function createMockery(string $class): Mockery\MockInterface
    {
        return Mockery::mock($class);
    }

    /**
     * 断言数组包含指定键值对
     */
    protected function assertArrayContainsKeyValue(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $actual, "Key '{$key}' not found in array");
            $this->assertEquals($value, $actual[$key], "Value for key '{$key}' does not match");
        }
    }

    /**
     * 断言路由信息格式正确
     */
    protected function assertValidRouteFormat(array $route): void
    {
        $this->assertArrayHasKey('path', $route);
        $this->assertArrayHasKey('methods', $route);
        $this->assertArrayHasKey('controller', $route);
        $this->assertArrayHasKey('action', $route);
        $this->assertIsString($route['path']);
        $this->assertIsArray($route['methods']);
        $this->assertIsString($route['controller']);
        $this->assertIsString($route['action']);
    }

    /**
     * 断言HTTP方法正确
     */
    protected function assertValidHttpMethods(array $methods): void
    {
        $validMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'];
        
        foreach ($methods as $method) {
            $this->assertContains($method, $validMethods, "Invalid HTTP method: {$method}");
        }
    }

    /**
     * 创建反射类实例用于测试
     */
    protected function getReflectionClass(string $className): \ReflectionClass
    {
        return new \ReflectionClass($className);
    }

    /**
     * 获取私有/保护方法用于测试
     */
    protected function getPrivateMethod(object $object, string $methodName): \ReflectionMethod
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method;
    }

    /**
     * 获取私有/保护属性用于测试
     */
    protected function getPrivateProperty(object $object, string $propertyName): \ReflectionProperty
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        
        return $property;
    }
} 