<?php

declare(strict_types=1);

namespace App\Controller;

use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;

#[ApiController(prefix: '/api/test', description: '测试API控制器')]
class TestController
{
    #[GetApi(path: '', summary: '获取测试列表')]
    public function index(): array
    {
        return [
            'code' => 200,
            'message' => 'success',
            'data' => [
                'action' => 'index',
                'route' => '/api/test',
                'method' => 'GET',
                'annotation' => 'GetApi',
            ]
        ];
    }

    #[GetApi(path: '/{id}', summary: '获取测试详情')]
    public function show(int $id): array
    {
        return [
            'code' => 200,
            'message' => 'success',
            'data' => [
                'action' => 'show',
                'route' => '/api/test/' . $id,
                'method' => 'GET',
                'annotation' => 'GetApi',
                'id' => $id,
            ]
        ];
    }

    #[PostApi(path: '', summary: '创建测试')]
    public function create(): array
    {
        return [
            'code' => 200,
            'message' => 'success',
            'data' => [
                'action' => 'create',
                'route' => '/api/test',
                'method' => 'POST',
                'annotation' => 'PostApi',
            ]
        ];
    }

    #[PutApi(path: '/{id}', summary: '更新测试')]
    public function update(int $id): array
    {
        return [
            'code' => 200,
            'message' => 'success',
            'data' => [
                'action' => 'update',
                'route' => '/api/test/' . $id,
                'method' => 'PUT',
                'annotation' => 'PutApi',
                'id' => $id,
            ]
        ];
    }

    #[DeleteApi(path: '/{id}', summary: '删除测试')]
    public function delete(int $id): array
    {
        return [
            'code' => 200,
            'message' => 'success',
            'data' => [
                'action' => 'delete',
                'route' => '/api/test/' . $id,
                'method' => 'DELETE',
                'annotation' => 'DeleteApi',
                'id' => $id,
            ]
        ];
    }

    // 无注解方法，不应该被路由
    public function notRouted(): array
    {
        return [
            'code' => 404,
            'message' => 'This method should not be routed'
        ];
    }
}