<?php
declare(strict_types=1);

namespace HPlus\Route;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // 注册路由收集器
                \Hyperf\HttpServer\Router\DispatcherFactory::class => DispatcherFactory::class,
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
