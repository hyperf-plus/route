<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Fixtures;

use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;
use HPlus\Route\Annotation\PatchApi;

#[ApiController(
    prefix: '/test',
    tag: 'Test',
    description: '测试API控制器'
)]
class TestApiController
{
    #[GetApi(path: '', summary: '获取用户列表')]
    public function index(): array
    {
        return ['message' => 'index'];
    }

    #[GetApi(path: '/{id}', summary: '获取用户详情')]
    public function show(int $id): array
    {
        return ['id' => $id, 'message' => 'show'];
    }

    #[PostApi(path: '', summary: '创建用户')]
    public function create(): array
    {
        return ['message' => 'create'];
    }

    #[PutApi(path: '/{id}', summary: '更新用户')]
    public function update(int $id): array
    {
        return ['id' => $id, 'message' => 'update'];
    }

    #[PatchApi(path: '/{id}', summary: '部分更新用户')]
    public function patch(int $id): array
    {
        return ['id' => $id, 'message' => 'patch'];
    }

    #[DeleteApi(path: '/{id}', summary: '删除用户')]
    public function delete(int $id): array
    {
        return ['id' => $id, 'message' => 'delete'];
    }

    #[GetApi(path: '/search', summary: '搜索用户')]
    public function search(string $keyword = ''): array
    {
        return ['keyword' => $keyword, 'message' => 'search'];
    }

    #[PostApi(path: '/batch', summary: '批量操作')]
    public function batch(): array
    {
        return ['message' => 'batch'];
    }

    // 无注解的方法，不应该被路由收集
    public function notRouted(): array
    {
        return ['message' => 'not routed'];
    }
} 