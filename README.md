# HPlus Route 4.0

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://php.net)
[![Hyperf Version](https://img.shields.io/badge/hyperf-%3E%3D3.1-brightgreen.svg)](https://hyperf.io)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

为 Hyperf 框架打造的智能 RESTful 路由组件，支持自动路径生成、智能参数识别、kebab-case URL 规范。

## ✨ 4.0 新特性

- 🚀 **Hyperf 3.1+ 支持** - 完整支持 `PriorityMiddleware`
- 🎯 **kebab-case URL** - 符合现代 RESTful 规范 (`user-profile` 而非 `user_profile`)
- ⚡ **精简设计** - 移除冗余映射规则，核心方法覆盖 95% 场景
- 🔧 **插件独立** - 不依赖 validate 插件，纯路由功能

> ⚠️ **破坏性变更**: URL 命名从 `snake_case` 改为 `kebab-case`，老用户请勿直接升级。

## 📦 安装

```bash
composer require hyperf-plus/route:^4.0
```

## 🚀 快速开始

### 基础 CRUD

```php
<?php
use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;
use HPlus\Route\Annotation\PutApi;
use HPlus\Route\Annotation\DeleteApi;

#[ApiController]  // 自动生成 /api/users
class UserController
{
    #[GetApi]
    public function index() {}      // GET /api/users
    
    #[GetApi] 
    public function show($id) {}    // GET /api/users/{id}
    
    #[PostApi]
    public function create() {}     // POST /api/users
    
    #[PutApi]
    public function update($id) {}  // PUT /api/users/{id}
    
    #[DeleteApi]
    public function delete($id) {}  // DELETE /api/users/{id}
}
```

### 资源子操作

```php
#[ApiController]
class UserController
{
    #[GetApi]
    public function state($id) {}       // GET /api/users/{id}/state
    
    #[PostApi]
    public function enable($id) {}      // POST /api/users/{id}/enable
    
    #[GetApi]
    public function permissions($id) {} // GET /api/users/{id}/permissions
}
```

### 自定义路径

```php
#[ApiController(prefix: '/v2/members')]
class UserController
{
    #[GetApi(path: '/all')]
    public function index() {}  // GET /v2/members/all
    
    #[GetApi]
    public function show($id) {} // GET /v2/members/{id}
}
```

## 📋 RESTful 映射规则

### 标准方法

| 方法名 | HTTP | 路径 | 说明 |
|--------|------|------|------|
| `index` / `list` | GET | `/` | 获取列表 |
| `show` / `detail` | GET | `/{id}` | 获取详情 |
| `create` / `store` | POST | `/` | 创建资源 |
| `update` / `edit` | PUT | `/{id}` | 更新资源 |
| `patch` | PATCH | `/{id}` | 部分更新 |
| `delete` / `destroy` | DELETE | `/{id}` | 删除资源 |
| `search` | GET | `/search` | 搜索 |
| `export` | GET | `/export` | 导出 |
| `import` | POST | `/import` | 导入 |
| `batch` | POST | `/batch` | 批量操作 |

### 驼峰转 kebab-case

```php
#[GetApi]
public function getUserProfile($id) {}  
// GET /api/users/{id}/get-user-profile

#[GetApi]
public function apiV2Users() {}  
// GET /api/users/api-v2-users
```

## 🎯 注解说明

### @ApiController

```php
#[ApiController(
    prefix: '/api/v1/users',  // 路由前缀（可选，默认自动生成）
    tag: 'User Management',   // API 标签（Swagger 用）
    server: 'http',           // 服务名
)]
```

### @GetApi / @PostApi / @PutApi / @DeleteApi / @PatchApi

```php
#[GetApi(
    path: '/{id}/detail',      // 自定义路径（可选）
    summary: '获取用户详情',    // 接口摘要
    description: '详细描述',    // 接口描述
    name: 'user.detail',       // 路由名称
    middleware: ['auth'],      // 中间件
    security: true,            // 需要认证
    deprecated: false,         // 是否废弃
)]
```

### 参数注解

```php
use HPlus\Route\Annotation\Query;
use HPlus\Route\Annotation\Path;
use HPlus\Route\Annotation\Body;
use HPlus\Route\Annotation\Header;

#[GetApi]
public function search(
    #[Query] string $keyword,
    #[Query] int $page = 1,
    #[Path] int $id,
    #[Header('X-Token')] string $token,
) {}
```

## 🛠️ 高级用法

### 路由收集器

```php
use HPlus\Route\RouteCollector;

$collector = RouteCollector::getInstance();

// 获取所有路由
$routes = $collector->collectRoutes();

// 按路径查找
$route = $collector->findRouteByPath('/api/users');

// 按控制器查找
$routes = $collector->findRoutesByController(UserController::class);

// 按标签查找
$routes = $collector->findRoutesByTag('User Management');
```

### 版本控制

```php
namespace App\Controller\Api\V2;

#[ApiController]  // 自动生成 /api/v2/users
class UserController {}
```

## 🤝 与其他组件集成

### 与 HPlus Validate 集成

```php
use HPlus\Validate\Annotations\RequestValidation;

#[PostApi]
#[RequestValidation(rules: [
    'name' => 'required|string|max:50',
    'email' => 'required|email',
])]
public function create() {}
```

### 与 HPlus Swagger 集成

路由信息自动被 Swagger 组件识别，生成 API 文档。

## 🧪 测试覆盖

```
tests/
├── Unit/
│   ├── AnnotationTest.php           # 注解测试
│   ├── DispatcherFactoryTest.php    # 调度器测试
│   └── RouteCollectorTest.php       # 路由收集器测试
├── Feature/
│   └── RouteCollectionFeatureTest.php  # 功能测试
└── Fixtures/
    ├── RestfulController.php        # 测试控制器
    └── TestApiController.php
```

运行测试：

```bash
composer test
```

## 📊 与 3.x 版本对比

| 特性 | 3.x | 4.0 |
|------|-----|-----|
| URL 风格 | `snake_case` | `kebab-case` |
| 依赖 validate | 软依赖 | 完全独立 |
| 智能映射规则 | 30+ 条 | 13 条核心 |
| Hyperf 3.1 | 部分支持 | 完整支持 |

### 迁移注意

```
# URL 变化示例
/api/user_profile  →  /api/user-profile
/api/get_user_info →  /api/get-user-info
```

## 📄 License

MIT
