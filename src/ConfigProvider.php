<?php
declare(strict_types=1);

namespace HPlus\Route;

use Mzh\Admin\Contracts\AuthInterface;
use Mzh\Admin\Library\Auth;

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
