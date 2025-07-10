<?php
declare(strict_types=1);

namespace HPlus\Route;

use Hyperf\HttpServer\Router\DispatcherFactory as HyperfDispatcherFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // 注册路由收集器
                RouteCollector::class => RouteCollector::class,
                // 替换 Hyperf 的默认 DispatcherFactory
                HyperfDispatcherFactory::class => DispatcherFactory::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'listeners' => [],
            'publish' => [],
        ];
    }
}
