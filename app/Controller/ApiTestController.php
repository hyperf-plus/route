<?php

declare(strict_types=1);

namespace App\Controller;

use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;

#[ApiController(prefix: '/api/test', description: 'API测试控制器 - 验证HPlus Route注解功能')]
class ApiTestController
{
    #[GetApi(path: '', summary: '获取测试列表')]
    public function index(): array
    {
        return [
            'code' => 200,
            'message' => 'HPlus Route 注解路由测试成功！',
            'data' => [
                'controller' => self::class,
                'action' => 'index',
                'route' => '/api/test',
                'method' => 'GET',
                'annotation' => 'GetApi',
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'attributes_supported' => PHP_VERSION_ID >= 80000
            ]
        ];
    }

    #[GetApi(path: '/{id}', summary: '获取指定ID的测试数据')]
    public function show(int $id): array
    {
        return [
            'code' => 200,
            'message' => '获取测试数据成功',
            'data' => [
                'controller' => self::class,
                'action' => 'show',
                'route' => '/api/test/' . $id,
                'method' => 'GET',
                'annotation' => 'GetApi',
                'id' => $id,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    #[PostApi(path: '', summary: '创建测试数据')]
    public function create(): array
    {
        return [
            'code' => 200,
            'message' => '创建测试数据成功',
            'data' => [
                'controller' => self::class,
                'action' => 'create',
                'route' => '/api/test',
                'method' => 'POST',
                'annotation' => 'PostApi',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    #[PutApi(path: '/{id}', summary: '更新指定ID的测试数据')]
    public function update(int $id): array
    {
        return [
            'code' => 200,
            'message' => '更新测试数据成功',
            'data' => [
                'controller' => self::class,
                'action' => 'update',
                'route' => '/api/test/' . $id,
                'method' => 'PUT',
                'annotation' => 'PutApi',
                'id' => $id,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    #[DeleteApi(path: '/{id}', summary: '删除指定ID的测试数据')]
    public function delete(int $id): array
    {
        return [
            'code' => 200,
            'message' => '删除测试数据成功',
            'data' => [
                'controller' => self::class,
                'action' => 'delete',
                'route' => '/api/test/' . $id,
                'method' => 'DELETE',
                'annotation' => 'DeleteApi',
                'id' => $id,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    // 不使用注解的方法，不应该被注册为路由
    public function notRouted(): array
    {
        return [
            'code' => 404,
            'message' => '这个方法不应该被注册为路由'
        ];
    }
}