# HPlus 3.x → 4.0 迁移指南（超详细）

> 面向 AI/团队的一站式迁移说明，涵盖 **route / validate / swagger / app 示例** 的所有破坏性变更、兼容策略和落地步骤。
>  
> **版本要求**：PHP ≥ 8.1；Hyperf ≥ 3.1。

---

## 1. 总览：4.0 做了哪些事？

### 路由（hyperf-plus/route）
- **URL 规范**：`snake_case` → `kebab-case`（`getUserInfo` → `/get-user-info`）
- **RESTful 映射精简**：保留 13 条核心映射，移除冗余映射（见下）
- **路由优先级**：静态路由优先于动态路由，避免 `/export` 被 `/{id}` 抢占
- **默认前缀变更**：不再强制 `/api`；按目录结构自动生成前缀（可在 `ApiController` 上显式写 `prefix`）
- **支持 Hyperf 3.1 `PriorityMiddleware`**

### 验证（hyperf-plus/validate）
- **完全基于 hyperf/validation**（兼容 Laravel 规则）
- **双模式**：内联规则 + FormRequest 验证器（支持 scene）
- **Query/Body 分离**：`queryRules` 专属 Query，`rules` 专属 Body，`mode` 控制 Body 解析（json/form/xml）
- **内置中文兜底**：无语言包也能输出中文错误；用户自定义 messages 优先
- **移除旧基类**：不再支持 ThinkPHP 风格 `Validate` 基类

### Swagger（hyperf-plus/swagger）
- **OpenAPI 3.1.1**，懒加载 + 构建结果缓存
- **FormRequest 支持**：自动解析 Hyperf FormRequest 规则与属性描述
- **软依赖**：无 validate 插件也能工作（跳过参数解析）
- **路径/参数感知**：GET/DELETE 不生成 requestBody，路径参数自动提取

### 示例项目（app）
- 演示 4.0 全套用法（kebab-case 路由、验证、Swagger）
- README、接口示例更新，便于直接试跑

---

## 2. 路由迁移（route）

### 2.1 破坏性变更
1) **URL 命名**：`snake_case` → `kebab-case`
   ```
   /api/user_profile  →  /api/user-profile
   /api/get_user_info →  /api/get-user-info
   ```
2) **默认前缀**：不再强制 `/api`，按目录生成
   - `App/Controller/TestController` → `/tests`
   - `App/Controller/Api/UserController` → `/api/users`
   - 如需自定义，显式写 `#[ApiController(prefix: '/xxx')]`
3) **RESTful 映射精简**
   - 保留：index/list，show/detail，create/store，update/edit，patch，delete/destroy
   - 移除自动映射：all/find/first/save/put 等（需显式 `path`）
4) **PHP/Hyperf 要求**：PHP 8.1+，Hyperf 3.1+

### 2.2 迁移步骤
```bash
composer require hyperf-plus/route:^4.0
```
1) **前端 URL**：替换为 kebab-case；或在注解写旧路径保持兼容：
   ```php
   #[ApiController(prefix: '/api/user_profile')]
   #[GetApi(path: '/get_user_info')]
   ```
2) **自定义方法**：如 `all()` 需显式 `path: '/'`；`find()` 需显式 `path: '/{id}'`
3) **检查冲突**：若移除 `/api` 造成路径重叠，请在注解写明前缀

### 2.3 代码要点
- `RouteCollector::generateRestfulPrefix`：按目录生成，kebab + 复数；无默认 `/api`
- 路由优先级：静态优先（`/export` > `/{id}`）
- 显式前缀始终优先生效

---

## 3. 验证迁移（validate）

### 3.1 破坏性变更
1) **移除** ThinkPHP 风格 `Validate` 基类  
   - 需改用 Hyperf 原生 `FormRequest` 或注解内联规则
2) **参数改名**：`dateType` → `mode`（json/form/xml）
3) **版本要求**：PHP 8.1+，Hyperf 3.1+

### 3.2 写法对照
**旧（3.x）**  
```php
use HPlus\Validate\Validate;
class UserValidate extends Validate {
    protected $rule = ['email' => 'require|email'];
}
```
**新（4.0，FormRequest）**  
```php
use Hyperf\Validation\Request\FormRequest;
class UserRequest extends FormRequest {
    public function rules(): array { return ['email' => 'required|email']; }
    public function attributes(): array { return ['email' => '邮箱']; }
}
#[PostApi]
#[RequestValidation(validate: UserRequest::class, mode: 'json')]
public function create() {}
```
**新（4.0，内联）**  
```php
#[RequestValidation(
  rules: ['email|邮箱' => 'required|email'],
  queryRules: ['page|页码' => 'integer|min:1'],
  mode: 'json', filter: true, security: false
)]
```

### 3.3 特性与行为
- **Query/Body 分离**：`queryRules` 只验 Query；`rules` 只验 Body；`mode` 决定 Body 解析
- **字段别名**：支持 `field|标题`，自动写入 attributes，并用于错误消息
- **内置中文兜底**：即使无语言包、无自定义 messages，也返回中文（可被用户 messages 覆盖）
- **安全模式**：`security=true` 拒绝未定义字段；`filter=true` 仅保留规则字段
- **常用内置规则消息**：required/required_if/required_unless/email/phone/mobile/boolean/url/date/ip/ipv4/ipv6/json/alpha/alpha_num/alpha_dash/min/max/between/size/same/different/confirmed/after/before/unique/exists 等

### 3.4 迁移步骤
1) 升级依赖：`composer require hyperf-plus/validate:^4.0`
2) 将旧 `Validate` 基类改为 FormRequest 或注解内联
3) `dateType` 改为 `mode`；若表单提交用 `mode: 'form'`
4) 如需中文，可不再放语言包（内置兜底）；有自定义 messages 继续生效

---

## 4. Swagger 迁移（swagger）

### 4.1 破坏性/关键变更
- OpenAPI 升级到 3.1.1
- 懒加载 + 构建结果缓存（首次访问 `/swagger/json` 构建，后续直接缓存）
- 支持 FormRequest 规则与属性描述
- GET/DELETE 不生成 requestBody，路径参数自动提取

### 4.2 使用与迁移
```bash
composer require hyperf-plus/swagger:^4.0
```
配置：`config/autoload/swagger.php` 通常保持默认即可。  
与 3.x 差异：
- `dateType` → `mode`（与 validate 保持一致）
- 路径前缀变更导致的展示变化：取决于 route 生成的真实路径

---

## 5. 示例项目（app）对照

- 路径示例（按目录自动前缀）：
  - `App/Controller/TestController` → `/tests`
  - `App/Controller/Api/UserController` → `/api/users`
- 接口示例：`/test/get-test`、`/test/post-test`、`/test/form-test`
- Swagger 访问：`/swagger`

---

## 6. 常见问题与方案

1) **前端 URL 不想改**  
   - 在 `ApiController`/`GetApi` 等注解里显式写旧路径（snake_case），保持兼容。
2) **参数必填但已传仍报错**  
   - 确认 Content-Type 与 `mode` 匹配（json/form）；规则支持 `field|标题` 已自动规范；表单请用 `mode: 'form'`。
3) **无语言包提示英文**  
   - 4.0 内置中文兜底，除非自定义 messages 显式覆盖为英文。
4) **路由冲突/前缀重叠**  
   - 显式写 `prefix`；或为静态路由设置更明确的 path，静态路由已优先。

---

## 7. 升级清单（Checklist）

- [ ] PHP ≥ 8.1，Hyperf ≥ 3.1
- [ ] route 升级：kebab-case，检查前端 URL；必要时注解写旧路径
- [ ] route 前缀：默认按目录，无 `/api`；需要固定前缀就写 `prefix`
- [ ] RESTful 映射：all/find/first/save/put 等需显式 path
- [ ] validate 升级：去掉旧 Validate 基类；`dateType` → `mode`
- [ ] validate 中文：无语言包也有中文；messages 可覆盖
- [ ] swagger 升级：OpenAPI 3.1.1，懒加载缓存；FormRequest 支持
- [ ] CI：确保 GitHub Actions 覆盖多 PHP 版本（已内置）

---

## 8. 参考与链接
- route 文档：`README.md`
- validate 文档：`README.md`
- swagger 文档：`README.md`
- OpenAPI 3.1.1 规范：https://spec.openapis.org/oas/v3.1.0
- Laravel/Hyperf 验证规则：https://laravel.com/docs/validation#available-validation-rules

--- 

> 如果需要 AI 自动重写项目，请向它提供本文件，并逐项核对 Checklist。
