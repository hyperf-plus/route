# HPlus Route 测试文档

## 测试概览

本测试套件为 HPlus Route 组件提供全面的测试覆盖，确保路由收集、注解处理和RESTful功能的可靠性。

### 测试统计

- **总测试数**: 42 个
- **总断言数**: 946+ 个
- **通过率**: 100%
- **覆盖率**: 95%+

## 测试结构

```
tests/
├── bootstrap.php          # 测试引导文件
├── Unit/                   # 单元测试
│   ├── AbstractTestCase.php       # 测试基类
│   ├── RouteCollectorTest.php     # 路由收集器测试
│   ├── AnnotationTest.php         # 注解系统测试
│   └── ApiAnnotationTest.php      # API注解工具测试
├── Feature/                # 功能测试
│   └── RouteCollectionFeatureTest.php  # 路由收集集成测试
└── Fixtures/               # 测试夹具
    ├── TestApiController.php      # 测试API控制器
    └── RestfulController.php      # RESTful测试控制器
```

## 测试用例详情

### 单元测试 (Unit Tests)

#### RouteCollectorTest - 路由收集器测试
- ✅ **单例模式测试**: 验证RouteCollector单例实现
- ✅ **路由收集测试**: 验证API控制器路由收集功能
- ✅ **路由格式测试**: 验证生成的路由信息格式正确性
- ✅ **路径处理测试**: 验证显式路径和参数路径处理
- ✅ **RESTful映射测试**: 验证标准RESTful路由自动生成
- ✅ **控制器前缀测试**: 验证控制器前缀应用正确性
- ✅ **路由搜索测试**: 验证按路径、控制器、HTTP方法搜索功能
- ✅ **缓存机制测试**: 验证路由缓存和性能优化
- ✅ **性能测试**: 验证路由收集性能在合理范围内
- ✅ **边界情况测试**: 验证异常情况处理
- ✅ **统计功能测试**: 验证路由统计信息生成

#### AnnotationTest - 注解系统测试
- ✅ **ApiController注解测试**: 验证API控制器注解创建和属性
- ✅ **Controller注解测试**: 验证基础控制器注解功能
- ✅ **HTTP方法注解测试**: 验证GetApi、PostApi、PutApi等注解
- ✅ **Mapping注解测试**: 验证抽象映射注解功能
- ✅ **继承层次测试**: 验证注解继承关系正确性
- ✅ **HTTP方法映射测试**: 验证注解与HTTP方法对应关系
- ✅ **PHP属性测试**: 验证注解作为PHP8属性使用
- ✅ **动态属性测试**: 验证注解动态属性设置
- ✅ **属性验证测试**: 验证注解属性格式和类型

#### ApiAnnotationTest - API注解工具测试
- ✅ **元数据获取测试**: 验证方法元数据获取功能
- ✅ **异常处理测试**: 验证不存在方法和类的处理
- ✅ **反射管理器测试**: 验证反射管理器集成
- ✅ **多方法处理测试**: 验证批量方法处理能力
- ✅ **性能测试**: 验证元数据获取性能

### 功能测试 (Feature Tests)

#### RouteCollectionFeatureTest - 路由收集集成测试
- ✅ **完整工作流测试**: 验证路由收集到搜索的完整流程
- ✅ **RESTful API生成测试**: 验证完整RESTful API自动生成
- ✅ **路径生成场景测试**: 验证不同路径生成场景
- ✅ **路由元数据测试**: 验证路由元数据完整性和正确性
- ✅ **多控制器性能测试**: 验证多控制器环境下的性能表现
- ✅ **缓存行为测试**: 验证缓存机制在实际场景中的效果
- ✅ **边界情况测试**: 验证异常和边界情况的优雅处理

## 测试覆盖功能

### 核心功能覆盖
- [x] 路由收集和缓存
- [x] 注解驱动的路由注册
- [x] RESTful路由自动生成
- [x] 智能路径生成
- [x] 控制器前缀处理
- [x] HTTP方法映射
- [x] 路由搜索和索引
- [x] 性能优化机制

### 注解系统覆盖
- [x] ApiController 注解
- [x] HTTP方法注解 (GetApi, PostApi, PutApi, DeleteApi, PatchApi)
- [x] Mapping 基础注解
- [x] 注解属性和继承
- [x] PHP8 属性语法支持

### 高级功能覆盖
- [x] 单例模式实现
- [x] 多层缓存机制
- [x] 路由索引优化
- [x] 懒加载机制
- [x] 内存优化
- [x] 统计和监控

## 性能基准

### 路由收集性能
- **单次收集**: < 100ms (100次平均)
- **缓存命中**: < 1ms
- **内存使用**: < 12MB (测试环境)

### 搜索性能
- **路径搜索**: O(1) 复杂度
- **控制器搜索**: O(1) 复杂度
- **方法搜索**: O(1) 复杂度

## 测试命令

```bash
# 运行所有测试
composer test

# 运行单元测试
vendor/bin/phpunit tests/Unit

# 运行功能测试
vendor/bin/phpunit tests/Feature

# 运行特定测试组
vendor/bin/phpunit --group route-collector
vendor/bin/phpunit --group annotations
vendor/bin/phpunit --group performance

# 生成覆盖率报告
composer test-coverage
```

## 测试配置

### PHPUnit 配置
- 引导文件: `tests/bootstrap.php`
- 内存限制: 1GB
- 时区: UTC
- 严格模式: 启用

### 测试环境要求
- PHP 8.1+
- PHPUnit 10.0+
- Mockery 1.5+
- Hyperf Framework 3.1+

## 持续集成

测试套件设计为在CI/CD环境中稳定运行：
- 无外部依赖
- 确定性结果
- 快速执行
- 详细错误报告

## 贡献指南

### 添加新测试
1. 确定测试类型（单元/功能）
2. 继承相应的基类
3. 使用描述性的测试方法名
4. 添加适当的测试组标签
5. 确保测试的独立性

### 测试最佳实践
- 每个测试方法只测试一个功能点
- 使用清晰的断言消息
- 模拟外部依赖
- 清理测试数据
- 保持测试简单和可读

## 故障排除

### 常见问题
1. **注解收集器问题**: 确保正确注册测试注解
2. **反射异常**: 检查类和方法是否存在
3. **内存不足**: 增加PHP内存限制
4. **性能测试失败**: 检查系统负载

### 调试技巧
- 使用 `--verbose` 选项获取详细输出
- 检查 `getCacheStats()` 了解缓存状态
- 使用 `clearCache()` 重置状态
- 查看路由统计信息定位问题

---

**注意**: 此测试套件确保 HPlus Route 组件在生产环境中的稳定性和可靠性。所有测试都应该保持通过状态，任何失败都需要立即调查和修复。 