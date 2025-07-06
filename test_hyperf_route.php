<?php

declare(strict_types=1);

use HPlus\Route\RouteCollector;
use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;

require_once 'vendor/autoload.php';

echo "=== HPlus Route Docker 测试 ===\n\n";

echo "1. 环境信息:\n";
echo "   PHP版本: " . PHP_VERSION . "\n";
echo "   Swoole版本: " . (extension_loaded('swoole') ? phpversion('swoole') : '未安装') . "\n";
echo "   属性注解支持: " . (PHP_VERSION_ID >= 80000 ? '支持' : '不支持') . "\n\n";

echo "2. 类加载测试:\n";
$classes = [
    'HPlus\\Route\\RouteCollector',
    'HPlus\\Route\\Annotation\\ApiController',
    'HPlus\\Route\\Annotation\\GetApi',
    'App\\Controller\\ApiTestController',
    'App\\Controller\\UserController',
    'App\\Controller\\RouteDebugController'
];

foreach ($classes as $class) {
    echo "   {$class}: " . (class_exists($class) ? '✓ 已加载' : '✗ 未加载') . "\n";
}

echo "\n3. 注解扫描测试:\n";

try {
    $collector = new RouteCollector();
    $routes = $collector->collectRoutes();
    
    echo "   路由收集: ✓ 成功\n";
    echo "   收集到的路由数量: " . count($routes) . "\n\n";
    
    if (count($routes) > 0) {
        echo "4. 路由详情:\n";
        foreach ($routes as $route) {
            echo "   - 路径: {$route['path']}\n";
            echo "     方法: " . implode(', ', $route['methods']) . "\n";
            echo "     控制器: {$route['controller']}\n";
            echo "     动作: {$route['action']}\n";
            echo "     摘要: {$route['summary']}\n";
            echo "\n";
        }
    } else {
        echo "4. 警告: 没有收集到任何路由\n";
    }
    
} catch (Exception $e) {
    echo "   路由收集: ✗ 失败\n";
    echo "   错误信息: " . $e->getMessage() . "\n";
    echo "   错误堆栈: " . $e->getTraceAsString() . "\n";
}

echo "\n5. 反射API测试:\n";
try {
    $reflectionClass = new ReflectionClass('App\\Controller\\ApiTestController');
    $classAttributes = $reflectionClass->getAttributes();
    
    echo "   类注解数量: " . count($classAttributes) . "\n";
    
    foreach ($classAttributes as $attribute) {
        echo "   - 类注解: {$attribute->getName()}\n";
        echo "     参数: " . json_encode($attribute->getArguments(), JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
        $methodAttributes = $method->getAttributes();
        if (count($methodAttributes) > 0) {
            echo "   - 方法: {$method->getName()}\n";
            foreach ($methodAttributes as $attribute) {
                echo "     注解: {$attribute->getName()}\n";
                echo "     参数: " . json_encode($attribute->getArguments(), JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "   反射API测试: ✗ 失败\n";
    echo "   错误信息: " . $e->getMessage() . "\n";
}

echo "\n=== 测试完成 ===\n";