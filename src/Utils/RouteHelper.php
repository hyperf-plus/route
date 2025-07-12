<?php

declare(strict_types=1);

namespace HPlus\Route\Utils;

/**
 * 路由工具类
 * 提供驼峰转换、复数化、RESTful路由映射等功能
 */
class RouteHelper
{
    /**
     * RESTful方法映射规则
     * [方法名 => [HTTP方法期望, 路径模板]]
     */
    public static array $restfulMapping = [
        // 列表操作
        'index' => ['GET', ''],
        'list' => ['GET', ''],
        'getList' => ['GET', ''],
        
        // 详情操作
        'show' => ['GET', '/{id}'],
        'detail' => ['GET', '/{id}'],
        'getDetail' => ['GET', '/{id}'],
        'get' => ['GET', '/{id}'],
        
        // 创建操作
        'create' => ['POST', ''],
        'store' => ['POST', ''],
        'add' => ['POST', ''],
        'post' => ['POST', ''],
        
        // 更新操作
        'update' => ['PUT', '/{id}'],
        'edit' => ['PUT', '/{id}'],
        'modify' => ['PUT', '/{id}'],
        'put' => ['PUT', '/{id}'],
        'patch' => ['PATCH', '/{id}'],
        
        // 删除操作
        'delete' => ['DELETE', '/{id}'],
        'destroy' => ['DELETE', '/{id}'],
        'remove' => ['DELETE', '/{id}'],
        
        // 批量操作
        'batch' => ['POST', '/batch'],
        'batchUpdate' => ['PUT', '/batch'],
        'batchDelete' => ['DELETE', '/batch'],
        
        // 搜索操作
        'search' => ['GET', '/search'],
        'query' => ['GET', '/query'],
        'filter' => ['GET', '/filter'],
        
        // 导入导出
        'export' => ['GET', '/export'],
        'import' => ['POST', '/import'],
        'upload' => ['POST', '/upload'],
        'download' => ['GET', '/download/{id}'],
        
        // 当前用户相关操作
        'currentUser' => ['GET', '/current'],
        'getCurrentUser' => ['GET', '/current'],
        'current' => ['GET', '/current'],
        'me' => ['GET', '/me'],
        'profile' => ['GET', '/profile'],
        'self' => ['GET', '/me'],
    ];

    /**
     * 资源子操作映射规则
     * 这些方法会生成 /{id}/action 格式的路径
     */
    public static array $resourceActionMapping = [
        // 状态操作
        'state' => ['GET', '/{id}/state'],
        'status' => ['GET', '/{id}/status'],
        'enable' => ['POST', '/{id}/enable'],
        'disable' => ['POST', '/{id}/disable'],
        'activate' => ['POST', '/{id}/activate'],
        'deactivate' => ['POST', '/{id}/deactivate'],
        
        // 关系操作
        'relationships' => ['GET', '/{id}/relationships'],
        'relations' => ['GET', '/{id}/relations'],
        'children' => ['GET', '/{id}/children'],
        'parent' => ['GET', '/{id}/parent'],
        
        // 动作操作
        'lock' => ['POST', '/{id}/lock'],
        'unlock' => ['POST', '/{id}/unlock'],
        'publish' => ['POST', '/{id}/publish'],
        'unpublish' => ['POST', '/{id}/unpublish'],
        'archive' => ['POST', '/{id}/archive'],
        'restore' => ['POST', '/{id}/restore'],
        'clone' => ['POST', '/{id}/clone'],
        'duplicate' => ['POST', '/{id}/duplicate'],
        
        // 审核操作
        'approve' => ['POST', '/{id}/approve'],
        'reject' => ['POST', '/{id}/reject'],
        'review' => ['GET', '/{id}/review'],
        'audit' => ['GET', '/{id}/audit'],
        
        // 统计操作
        'stats' => ['GET', '/{id}/stats'],
        'statistics' => ['GET', '/{id}/statistics'],
        'metrics' => ['GET', '/{id}/metrics'],
        'analytics' => ['GET', '/{id}/analytics'],
        
        // 历史操作
        'history' => ['GET', '/{id}/history'],
        'logs' => ['GET', '/{id}/logs'],
        'versions' => ['GET', '/{id}/versions'],
        'revisions' => ['GET', '/{id}/revisions'],
        
        // 权限操作
        'permissions' => ['GET', '/{id}/permissions'],
        'roles' => ['GET', '/{id}/roles'],
        'share' => ['POST', '/{id}/share'],
        'unshare' => ['POST', '/{id}/unshare'],
    ];

    /**
     * 驼峰转中划线
     * currentUser -> current-user
     * getUserInfo -> get-user-info
     */
    public static function camelToKebab(string $str): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $str));
    }

    /**
     * 单词复数化（简单规则）
     * user -> users
     * category -> categories
     */
    public static function pluralize(string $word): string
    {
        // 特殊情况
        $irregular = [
            'child' => 'children',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'tooth' => 'teeth',
            'foot' => 'feet',
            'mouse' => 'mice',
            'goose' => 'geese',
        ];
        
        $lowerWord = strtolower($word);
        
        if (isset($irregular[$lowerWord])) {
            return $irregular[$lowerWord];
        }
        
        // 已经是复数形式
        if (str_ends_with($word, 's') || str_ends_with($word, 'es')) {
            return $word;
        }
        
        // 以y结尾（前面是辅音）
        if (str_ends_with($word, 'y') && !in_array(substr($word, -2, 1), ['a', 'e', 'i', 'o', 'u'])) {
            return substr($word, 0, -1) . 'ies';
        }
        
        // 以s, x, z, ch, sh结尾
        if (str_ends_with($word, 's') || str_ends_with($word, 'x') || 
            str_ends_with($word, 'z') || str_ends_with($word, 'ch') || 
            str_ends_with($word, 'sh')) {
            return $word . 'es';
        }
        
        // 默认加s
        return $word . 's';
    }

    /**
     * 检查是否为RESTful标准方法
     */
    public static function isRestfulMethod(string $methodName): bool
    {
        return isset(self::$restfulMapping[$methodName]) || isset(self::$resourceActionMapping[$methodName]);
    }

    /**
     * 获取RESTful路径模板
     */
    public static function getRestfulPath(string $methodName, string $httpMethod): ?string
    {
        // 检查标准RESTful映射
        if (isset(self::$restfulMapping[$methodName])) {
            [$expectedMethod, $pathTemplate] = self::$restfulMapping[$methodName];
            
            if (strtoupper($httpMethod) === $expectedMethod) {
                return $pathTemplate;
            }
        }
        
        // 检查资源子操作映射
        if (isset(self::$resourceActionMapping[$methodName])) {
            [$expectedMethod, $pathTemplate] = self::$resourceActionMapping[$methodName];
            
            if (strtoupper($httpMethod) === $expectedMethod) {
                return $pathTemplate;
            }
        }
        
        return null;
    }

    /**
     * 生成RESTful风格的控制器前缀
     * UserController -> /api/users
     * UserDetailController -> /api/user-details
     */
    public static function generateRestfulPrefix(string $className): string
    {
        $classPath = str_replace('\\', '/', $className);
        
        // 提取控制器部分
        if (str_contains($classPath, '/Controller/')) {
            $controllerPart = substr($classPath, strpos($classPath, '/Controller/') + 12);
        } else {
            $controllerPart = substr($className, strrpos($className, '\\') + 1);
        }
        
        // 移除Controller后缀
        if (str_ends_with($controllerPart, 'Controller')) {
            $controllerPart = substr($controllerPart, 0, -10);
        }
        
        // 处理路径部分（保留版本号等）
        $parts = explode('/', $controllerPart);
        $resourceName = array_pop($parts); // 最后一部分是资源名
        
        // 转换资源名为RESTful风格（驼峰转中划线+复数）
        $resourceName = self::camelToKebab($resourceName);
        $resourceName = self::pluralize($resourceName);
        
        // 组装完整路径
        $prefix = '/api';
        if (!empty($parts)) {
            $prefix .= '/' . implode('/', array_map('strtolower', $parts));
        }
        $prefix .= '/' . $resourceName;
        
        return self::normalizePath($prefix);
    }

    /**
     * 规范化路径
     */
    public static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
} 