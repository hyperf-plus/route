<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Fixtures;

use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;

#[ApiController(
    description: 'RESTful测试控制器 - 测试自动路由生成'
)]
class RestfulController
{
    // 标准 RESTful 方法名，应该自动生成路由
    
    #[GetApi]
    public function index(): array
    {
        return ['action' => 'index'];
    }

    #[GetApi]
    public function show(int $id): array
    {
        return ['action' => 'show', 'id' => $id];
    }

    #[PostApi]
    public function create(): array
    {
        return ['action' => 'create'];
    }

    #[PostApi]
    public function store(): array
    {
        return ['action' => 'store'];
    }

    #[PutApi]
    public function update(int $id): array
    {
        return ['action' => 'update', 'id' => $id];
    }

    #[DeleteApi]
    public function delete(int $id): array
    {
        return ['action' => 'delete', 'id' => $id];
    }

    #[DeleteApi]
    public function destroy(int $id): array
    {
        return ['action' => 'destroy', 'id' => $id];
    }

    // 资源子操作
    #[GetApi]
    public function status(int $id): array
    {
        return ['action' => 'status', 'id' => $id];
    }

    #[PostApi]
    public function enable(int $id): array
    {
        return ['action' => 'enable', 'id' => $id];
    }

    #[PostApi]
    public function disable(int $id): array
    {
        return ['action' => 'disable', 'id' => $id];
    }

    // 批量操作
    #[PostApi]
    public function batch(): array
    {
        return ['action' => 'batch'];
    }

    #[GetApi]
    public function search(): array
    {
        return ['action' => 'search'];
    }

    #[GetApi]
    public function export(): array
    {
        return ['action' => 'export'];
    }

    #[PostApi]
    public function import(): array
    {
        return ['action' => 'import'];
    }
} 