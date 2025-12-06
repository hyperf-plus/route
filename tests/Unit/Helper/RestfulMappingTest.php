<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit\Helper;

use HPlus\Route\Helper\RestfulMapping;
use PHPUnit\Framework\TestCase;

/**
 * RestfulMapping 单元测试
 */
final class RestfulMappingTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_standard_restful_mappings(): void
    {
        // 列表操作
        $this->assertEquals(['GET', ''], RestfulMapping::MAPPING['index']);
        $this->assertEquals(['GET', ''], RestfulMapping::MAPPING['list']);
        
        // 详情操作
        $this->assertEquals(['GET', '/{id}'], RestfulMapping::MAPPING['show']);
        $this->assertEquals(['GET', '/{id}'], RestfulMapping::MAPPING['detail']);
        $this->assertEquals(['GET', '/{id}'], RestfulMapping::MAPPING['get']);
        
        // 创建操作
        $this->assertEquals(['POST', ''], RestfulMapping::MAPPING['create']);
        $this->assertEquals(['POST', ''], RestfulMapping::MAPPING['store']);
        $this->assertEquals(['POST', ''], RestfulMapping::MAPPING['add']);
        
        // 更新操作
        $this->assertEquals(['PUT', '/{id}'], RestfulMapping::MAPPING['update']);
        $this->assertEquals(['PUT', '/{id}'], RestfulMapping::MAPPING['edit']);
        $this->assertEquals(['PATCH', '/{id}'], RestfulMapping::MAPPING['patch']);
        
        // 删除操作
        $this->assertEquals(['DELETE', '/{id}'], RestfulMapping::MAPPING['delete']);
        $this->assertEquals(['DELETE', '/{id}'], RestfulMapping::MAPPING['destroy']);
        $this->assertEquals(['DELETE', '/{id}'], RestfulMapping::MAPPING['remove']);
    }

    /**
     * @test
     */
    public function it_checks_restful_methods(): void
    {
        $this->assertTrue(RestfulMapping::isRestfulMethod('index'));
        $this->assertTrue(RestfulMapping::isRestfulMethod('show'));
        $this->assertTrue(RestfulMapping::isRestfulMethod('create'));
        $this->assertTrue(RestfulMapping::isRestfulMethod('update'));
        $this->assertTrue(RestfulMapping::isRestfulMethod('delete'));
        
        $this->assertFalse(RestfulMapping::isRestfulMethod('custom'));
        $this->assertFalse(RestfulMapping::isRestfulMethod('getUsers'));
        $this->assertFalse(RestfulMapping::isRestfulMethod(''));
    }

    /**
     * @test
     */
    public function it_gets_mapping_for_method(): void
    {
        $this->assertEquals(['GET', ''], RestfulMapping::getMapping('index'));
        $this->assertEquals(['GET', '/{id}'], RestfulMapping::getMapping('show'));
        $this->assertEquals(['POST', ''], RestfulMapping::getMapping('create'));
        
        $this->assertNull(RestfulMapping::getMapping('custom'));
        $this->assertNull(RestfulMapping::getMapping('nonExistent'));
    }
}
