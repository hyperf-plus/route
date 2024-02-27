<?php
declare(strict_types=1);

namespace HPlus\Route;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__
                    ],
                ],
            ],
            'dependencies' => [
                \Hyperf\HttpServer\Router\DispatcherFactory::class => DispatcherFactory::class
            ]
        ];
    }
}
