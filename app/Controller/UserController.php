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
    
    #[GetApi(summary: '获取用户列表')]
    public function index(): array
    {
        return [
            'code' => 200,
            'message' => 'RESTful路由测试成功',
            'data' => [
                'controller' => self::class,
                'action' => 'index',
                'expected_route' => '/api/users',
                'method' => 'GET',
                'description' => '获取用户列表',
                'test_data' => [
                    ['id' => 1, 'name' => '张三', 'email' => 'zhangsan@example.com'],
                    ['id' => 2, 'name' => '李四', 'email' => 'lisi@example.com'],
                    ['id' => 3, 'name' => '王五', 'email' => 'wangwu@example.com']
                ]
            ]
        ];
    }

    #[GetApi(summary: '获取用户详情')]
    public function show(int $id): array
    {
        return [
            'code' => 200,
            'message' => '获取用户详情成功',
            'data' => [
                'controller' => self::class,
                'action' => 'show',
                'expected_route' => '/api/users/' . $id,
                'method' => 'GET',
                'description' => '获取用户详情',
                'user_id' => $id,
                'test_data' => [
                    'id' => $id,
                    'name' => '测试用户' . $id,
                    'email' => 'user' . $id . '@example.com',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];
    }

    #[PostApi(summary: '创建用户')]
    public function store(): array
    {
        return [
            'code' => 201,
            'message' => '创建用户成功',
            'data' => [
                'controller' => self::class,
                'action' => 'store',
                'expected_route' => '/api/users',
                'method' => 'POST',
                'description' => '创建用户',
                'test_data' => [
                    'id' => rand(1000, 9999),
                    'name' => '新用户',
                    'email' => 'newuser@example.com',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];
    }

    #[PutApi(summary: '更新用户')]
    public function update(int $id): array
    {
        return [
            'code' => 200,
            'message' => '更新用户成功',
            'data' => [
                'controller' => self::class,
                'action' => 'update',
                'expected_route' => '/api/users/' . $id,
                'method' => 'PUT',
                'description' => '更新用户',
                'user_id' => $id,
                'test_data' => [
                    'id' => $id,
                    'name' => '更新后的用户' . $id,
                    'email' => 'updated' . $id . '@example.com',
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];
    }

    #[DeleteApi(summary: '删除用户')]
    public function destroy(int $id): array
    {
        return [
            'code' => 200,
            'message' => '删除用户成功',
            'data' => [
                'controller' => self::class,
                'action' => 'destroy',
                'expected_route' => '/api/users/' . $id,
                'method' => 'DELETE',
                'description' => '删除用户',
                'user_id' => $id,
                'deleted_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    // 不使用注解的方法，不应该被注册为路由
    public function privateMethod(): array
    {
        return [
            'code' => 404,
            'message' => '这个方法不应该被注册为路由'
        ];
    }
}