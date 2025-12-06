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
| 路由优先级 | 无 | 静态路由优先 |
| PHP 版本 | 8.0+ | 8.1+ |

---

## 🔄 从 3.x 迁移到 4.0

> ⚠️ **重要警告**：4.0 有破坏性变更，不建议老项目直接升级。如确需升级，请仔细阅读以下内容。

### 破坏性变更清单

#### 1. URL 命名风格变更（影响最大）

**3.x**: `snake_case`
**4.0**: `kebab-case`

```php
// 控制器类名
UserProfileController

// 3.x 生成的 URL
/api/user_profile
/api/user_profiles

// 4.0 生成的 URL
/api/user-profile
/api/user-profiles
```

**方法名转换**：

```php
// 3.x
public function getUserInfo() {}  // → /api/users/get_user_info
public function batchUpdate() {}  // → /api/users/batch_update

// 4.0
public function getUserInfo() {}  // → /api/users/get-user-info
public function batchUpdate() {}  // → /api/users/batch-update
```

#### 2. RESTful 映射精简

**移除的映射**（4.0 将回退为 kebab-case 路径）：

| 3.x 方法 | 3.x 路径 | 4.0 路径 |
|---------|---------|---------|
| `all` | `/` | `/all` |
| `find` | `/{id}` | `/find` |
| `first` | `/{id}` | `/first` |
| `save` | `/` | `/save` |
| `put` | `/{id}` | `/put` |

**保留的核心映射**：

```
index, list     → GET /
show, detail    → GET /{id}
create, store   → POST /
update, edit    → PUT /{id}
patch           → PATCH /{id}
delete, destroy → DELETE /{id}
```

#### 3. PHP 版本要求

```diff
- PHP >= 8.0
+ PHP >= 8.1
```

#### 4. Hyperf 版本要求

```diff
- Hyperf >= 3.0
+ Hyperf >= 3.1
```

#### 5. 注解参数变更

**`deprecated` 类型变更**：

```php
// 3.x
#[GetApi(deprecated: true)]  // bool 类型

// 4.0
#[GetApi(deprecated: true)]  // 仍支持 bool，向后兼容
```

#### 6. 路由优先级

4.0 新增路由优先级排序，静态路由优先于动态路由注册：

```
/api/users/export   (优先级 1000，先匹配)
/api/users/{id}     (优先级 900，后匹配)
```

### 迁移步骤

#### 步骤 1：检查 PHP 和 Hyperf 版本

```bash
php -v  # 需要 8.1+
composer show hyperf/framework  # 需要 3.1+
```

#### 步骤 2：备份现有路由

```bash
# 导出当前所有路由
php bin/hyperf.php route:list > routes_backup.txt
```

#### 步骤 3：升级依赖

```bash
composer require hyperf-plus/route:^4.0
```

#### 步骤 4：修改前端 API 调用

**方案 A：修改前端**（推荐）

```javascript
// 修改前
fetch('/api/user_profile')
fetch('/api/get_user_info')

// 修改后
fetch('/api/user-profile')
fetch('/api/get-user-info')
```

**方案 B：使用显式路径保持兼容**

```php
// 在注解中显式指定旧路径
#[ApiController(prefix: '/api/user_profile')]
class UserProfileController {}

#[GetApi(path: '/get_user_info')]
public function getUserInfo() {}
```

#### 步骤 5：检查自定义方法

```php
// 如果使用了被移除的映射方法名，需要手动指定路径
// 3.x 自动映射
#[GetApi]
public function all() {}  // → GET /api/users

// 4.0 需要显式指定
#[GetApi(path: '/')]
public function all() {}  // → GET /api/users
```

#### 步骤 6：运行测试验证

```bash
# 启动服务
php bin/hyperf.php start

# 检查路由是否正确
curl http://localhost:9501/api/users
```

### 不迁移建议

如果你的项目满足以下条件，建议**保持 3.x 版本**：

- ✅ 已稳定运行的生产环境
- ✅ 前端有大量 API 调用
- ✅ 没有使用 Hyperf 3.1 新特性的需求
- ✅ 没有 `kebab-case` URL 的强制要求

### 快速兼容方案

如果必须升级但不想改 URL，在 `config/autoload/route.php` 添加：

```php
// 暂无自动兼容方案，需手动在注解中指定旧路径
// 或使用 Nginx 重写规则做兼容：

# nginx.conf
location /api/ {
    # 将下划线转为中划线
    if ($request_uri ~* "_") {
        rewrite ^(.*)_(.*)$ $1-$2 permanent;
    }
}
```

---

## 📄 License

MIT
