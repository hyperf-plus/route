<?php

declare(strict_types=1);

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
            BASE_PATH . '/packages',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
    ],
    'dependencies' => [
        HPlus\Route\RouteCollector::class => HPlus\Route\RouteCollector::class,
        Hyperf\HttpServer\Router\DispatcherFactory::class => HPlus\Route\DispatcherFactory::class,
    ],
];