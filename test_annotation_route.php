<?php

use HPlus\Route\RouteCollector;
use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\PostApi;

require_once 'vendor/autoload.php';

echo "=== HPlus Route 注解路由修复测试 ===\n\n";

echo "1. PHP版本信息:\n";
echo "   PHP版本: " . PHP_VERSION . "\n";
echo "   是否支持属性注解: " . (PHP_VERSION_ID >= 80000 ? '是' : '否') . "\n\n";

echo "2. 加载类信息:\n";
echo "   HPlus\\Route\\RouteCollector: " . (class_exists('HPlus\\Route\\RouteCollector') ? '已加载' : '未加载') . "\n";
echo "   HPlus\\Route\\Annotation\\ApiController: " . (class_exists('HPlus\\Route\\Annotation\\ApiController') ? '已加载' : '未加载') . "\n";
echo "   HPlus\\Route\\Annotation\\GetApi: " . (class_exists('HPlus\\Route\\Annotation\\GetApi') ? '已加载' : '未加载') . "\n\n";

// 测试我们创建的控制器是否被正确扫描
echo "3. 测试控制器扫描:\n";
try {
    $collector = new RouteCollector();
    $routes = $collector->collectRoutes();
    
    echo "   找到的路由总数: " . count($routes) . "\n";
    
    // 统计不同控制器的路由
    $controllerStats = [];
    foreach ($routes as $route) {
        $controllerName = $route['controller'] ?? 'Unknown';
        if (!isset($controllerStats[$controllerName])) {
            $controllerStats[$controllerName] = 0;
        }
        $controllerStats[$controllerName]++;
    }
    
    echo "   按控制器分类:\n";
    foreach ($controllerStats as $controller => $count) {
        echo "     - {$controller}: {$count} 个路由\n";
    }
    
    echo "\n4. 详细路由信息:\n";
    foreach ($routes as $route) {
        echo "   路由: {$route['path']}\n";
        echo "     方法: " . implode(', ', $route['methods']) . "\n";
        echo "     控制器: {$route['controller']}\n";
        echo "     方法名: {$route['action']}\n";
        echo "     描述: {$route['summary']}\n";
        echo "   ---\n";
    }
    
    echo "\n5. 测试结果:\n";
    $testController = 'App\\Controller\\TestController';
    $userController = 'App\\Controller\\UserController';
    $debugController = 'App\\Controller\\RouteDebugController';
    
    $testRoutes = $controllerStats[$testController] ?? 0;
    $userRoutes = $controllerStats[$userController] ?? 0;
    $debugRoutes = $controllerStats[$debugController] ?? 0;
    
    echo "   TestController路由: {$testRoutes}\n";
    echo "   UserController路由: {$userRoutes}\n";
    echo "   RouteDebugController路由: {$debugRoutes}\n";
    
    if ($testRoutes >= 5 && $userRoutes >= 7 && $debugRoutes >= 3) {
        echo "   ✅ 注解路由扫描修复成功！所有控制器的路由都被正确识别。\n";
    } else {
        echo "   ❌ 注解路由扫描仍有问题，部分路由未被识别。\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ 测试失败: " . $e->getMessage() . "\n";
    echo "   错误详情: " . $e->getTraceAsString() . "\n";
}

echo "\n=== 测试完成 ===\n";