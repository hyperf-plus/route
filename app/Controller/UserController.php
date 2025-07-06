<?php

declare(strict_types=1);

namespace App\Controller;

use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;

#[ApiController(description: '用户管理控制器 - 测试RESTful路由自动生成')]
class UserController
{
    // 标准 RESTful 方法名，应该自动生成路由
    
    #[GetApi]
    public function index(): array
    {
        return [
            'code' => 200,
            'message' => 'RESTful路由测试成功',
            'data' => [
                'action' => 'index',
                'expected_route' => '/api/users',
                'method' => 'GET',
                'description' => '获取用户列表',
            ]
        ];
    }

    #[GetApi]
    public function show(int $id): array
    {
        return [
            'code' => 200,
            'message' => 'RESTful路由测试成功',
            'data' => [
                'action' => 'show',
                'expected_route' => '/api/users/' . $id,
                'method' => 'GET',
                'description' => '获取用户详情',
                'id' => $id,
            ]
        ];
    }

    #[PostApi]
    public function create(): array
    {
        return [
            'code' => 200,
            'message' => 'RESTful路由测试成功',
            'data' => [
                'action' => 'create',
                'expected_route' => '/api/users',
                'method' => 'POST',
                'description' => '创建用户',
            ]
        ];
    }

    #[PutApi]
    public function update(int $id): array
    {
        return [
            'code' => 200,
            'message' => 'RESTful路由测试成功',
            'data' => [
                'action' => 'update',
                'expected_route' => '/api/users/' . $id,
                'method' => 'PUT',
                'description' => '更新用户',
                'id' => $id,
            ]
        ];
    }

    #[DeleteApi]
    public function delete(int $id): array
    {
        return [
            'code' => 200,
            'message' => 'RESTful路由测试成功',
            'data' => [
                'action' => 'delete',
                'expected_route' => '/api/users/' . $id,
                'method' => 'DELETE',
                'description' => '删除用户',
                'id' => $id,
            ]
        ];
    }

    // 资源子操作
    #[GetApi]
    public function status(int $id): array
    {
        return [
            'code' => 200,
            'message' => 'RESTful资源子操作测试成功',
            'data' => [
                'action' => 'status',
                'expected_route' => '/api/users/' . $id . '/status',
                'method' => 'GET',
                'description' => '获取用户状态',
                'id' => $id,
            ]
        ];
    }

    #[PostApi]
    public function enable(int $id): array
    {
        return [
            'code' => 200,
            'message' => 'RESTful资源子操作测试成功',
            'data' => [
                'action' => 'enable',
                'expected_route' => '/api/users/' . $id . '/enable',
                'method' => 'POST',
                'description' => '启用用户',
                'id' => $id,
            ]
        ];
    }
}