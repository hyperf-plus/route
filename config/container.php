<?php

declare(strict_types=1);

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceInterface;
use Hyperf\Utils\ApplicationContext as Utils;

$container = new Container((function () {
    return require __DIR__ . '/config.php';
})());

if (!$container instanceof \Psr\Container\ContainerInterface) {
    throw new RuntimeException('The dependency injection container is invalid.');
}
return ApplicationContext::setContainer($container);